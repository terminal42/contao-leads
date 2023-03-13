<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Validator;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

#[AsCronJob('daily')]
class PurgeCron
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Filesystem $filesystem,
        private readonly LoggerInterface|null $contaoCronLogger = null,
    ) {}

    public function __invoke(): void
    {
        $forms = $this->connection->fetchAllAssociative("SELECT * FROM tl_form WHERE leadEnabled='1' AND leadMain=0 AND leadPeriod!=''");
        $allIds = [];
        $allUploads = [];

        foreach ($forms as $form) {
            if (null === $time = $this->getExpirationTimestamp($form['leadPeriod'])) {
                continue;
            }

            $leadIds = $this->connection->fetchFirstColumn(
                'SELECT id FROM tl_lead WHERE main_id=? AND created<?',
                [$form['id'], $time]
            );

            if (empty($leadIds)) {
                continue;
            }

            $allIds[] = $leadIds;
            $allUploads[] = $form['leadPurgeUploads'] ? $this->getUploads($leadIds) : [];
        }

        if (empty($allIds)) {
            return;
        }

        $allIds = array_merge(...$allIds);
        $allUploads = array_merge(...$allUploads);

        $deleted = $this->connection->delete('tl_lead', ['id' => $allIds], [ArrayParameterType::INTEGER]);
        $this->connection->delete('tl_lead_data', ['pid' => $allIds], [ArrayParameterType::INTEGER]);

        /** @var FilesModel $filesModel */
        foreach ($allUploads as $filesModel) {
            try {
                $this->filesystem->remove($filesModel->path);
                $filesModel->delete();
            } catch (IOException) {
                continue;
            }
        }

        if (null !== $this->contaoCronLogger) {
            $this->contaoCronLogger->info(sprintf('Purged %s leads from %s forms.', $deleted, \count($forms)));
        }
    }

    private function getExpirationTimestamp(string $timePeriod): int|null
    {
        $range = StringUtil::deserialize($timePeriod);

        if (isset($range['unit'], $range['value']) && is_array($range) && !empty($range['value']) && false !== ($timestamp = strtotime('- ' . $range['value'] . ' ' . $range['unit']))) {
            return $timestamp;
        }

        return null;
    }

    private function getUploads(array $leadIds): array
    {
        $uploads = [];
        $rows = $this->connection->fetchAllAssociative("
            SELECT *
            FROM tl_lead_data
            WHERE pid IN (?)
              AND value REGEXP '[a-f0-9]{8}-[a-f0-9]{4}-1[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}'
        ", [$leadIds], [ArrayParameterType::INTEGER]);

        foreach ($rows as $row) {
            foreach (StringUtil::deserialize($row['value'], true) as $uuid) {
                if (Validator::isUuid($uuid) && null !== ($filesModel = FilesModel::findByUuid($uuid))) {
                    $uploads[] = $filesModel;
                }
            }
        }

        return $uploads;
    }
}
