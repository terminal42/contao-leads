<?php

namespace Terminal42\LeadsBundle\Service;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Validator;
use Doctrine\DBAL\Connection;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Terminal42\LeadsBundle\Event\LeadsPurgeEvent;

class LeadsPurger
{
    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;


    /**
     * LeadsPurger constructor.
     * @param ContaoFrameworkInterface $framework
     * @param Connection $db
     * @param string $rootDir
     * @param LoggerInterface $logger
     * @param EventDispatcherInterface $eventDispatcher
     * @param Filesystem|null $fs
     */
    public function __construct(
        ContaoFrameworkInterface $framework,
        Connection $db,
        string $rootDir,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        Filesystem $fs = null
    ) {
        $this->framework = $framework;
        $this->db = $db;
        $this->rootDir = $rootDir;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->fs = $fs ? $fs : new Filesystem();
    }

    /**
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute()
    {
        $this->framework->initialize();

        $purged = $this->executeBatchPurge();

        if ($purged) {
            $logMessage = 'The leads have been purged successfully.';
        } else {
            $logMessage = 'No leads to purge.';
        }

        $this->logger->info(
            $logMessage,
            ['contao' => new ContaoContext(__METHOD__, ContaoContext::GENERAL)]
        );

        return $logMessage;
    }

    /**
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    private function executeBatchPurge()
    {
        $purged = false;
        $forms = $this->db->fetchAll(
            "SELECT id, title, leadPeriod, leadPurgeUploads FROM tl_form WHERE leadPeriod != ''"
        );

        foreach ($forms as $masterForm) {

            $leadPeriodTime = $this->convertTimePeriodToTime($masterForm['leadPeriod']);
            if (!empty($leads = $this->getAllLeads($masterForm['id'], $leadPeriodTime))) {

                $leadsData = $this->getAllLeadsData($leads);
                $uploads = $this->getUploads($leadsData, $masterForm['leadPurgeUploads']);

                $deletedLeadsData = $this->purgeLeadsData($leadsData, $masterForm);
                $deletedUploads = $this->purgeUploads($uploads, $masterForm);
                $deletedLeads = $this->purgeLeads($leads, $masterForm);

                // Add custom logging
                $purgeEvent = new LeadsPurgeEvent($masterForm, $deletedLeads, $deletedLeadsData, $deletedUploads);
                $this->eventDispatcher->dispatch(LeadsPurgeEvent::EVENT_NAME, $purgeEvent);

                $purged = true;
            }
        }

        return $purged;
    }

    /**
     * @param $timePeriod
     * @return int
     */
    private function convertTimePeriodToTime($timePeriod)
    {
        $range = StringUtil::deserialize($timePeriod);

        if (is_array($range) && isset($range['unit']) && isset($range['value'])
            && false !== ($timestamp = strtotime('- '.$range['value'].' '.$range['unit']))) {
            return $timestamp;
        }

        return 0;
    }

    /**
     * @param $masterFormId
     * @param $timestamp
     * @return array
     */
    private function getAllLeads($masterFormId, $timestamp)
    {
        $leads = [];

        $rows = $this->db->fetchAll(
            "SELECT * FROM tl_lead WHERE master_id=? AND created<?",
            [$masterFormId, $timestamp]
        );

        foreach ($rows as $row) {
            $leads[$row['id']] = $row;
        }

        return $leads;
    }

    /**
     * @param array $leads
     * @param array $masterForm
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    private function purgeLeads(array $leads, array $masterForm)
    {
        $deleted = 0;
        $ids = implode(',', array_keys($leads));

        if (!empty($ids)) {
            $deleted = (int)$this->db->executeUpdate(
                "DELETE FROM tl_lead WHERE id IN(".$ids.")"
            );

            $this->logger->info(
                sprintf('Purged %d leads for master form "%s"', $deleted, $masterForm['title']),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::GENERAL)]
            );
        }

        return $deleted;
    }

    /**
     * @param array $leads
     * @return array
     */
    private function getAllLeadsData(array $leads)
    {
        $leadsData = [];

        if (!empty($leads)) {

            $ids = implode(',', array_keys($leads));

            $rows = $this->db->fetchAll(
                "SELECT d.*, f.type AS field_type FROM tl_lead_data d
                      LEFT JOIN tl_form_field f ON d.field_id = f.id
                      WHERE d.pid IN(".$ids.")"
            );

            foreach ($rows as $row) {
                $leadsData[$row['id']] = $row;
            }
        }


        return $leadsData;
    }

    /**
     * @param array $leadsData
     * @param array $masterForm
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    private function purgeLeadsData(array $leadsData, array $masterForm)
    {
        $deleted = 0;

        if (!empty($leadsData)) {

            $ids = implode(',', array_keys($leadsData));

            $deleted = (int)$this->db->executeUpdate(
                "DELETE FROM tl_lead_data WHERE id IN(".$ids.")"
            );

            $this->logger->info(
                sprintf('Purged %d leads data for master form "%s"', $deleted, $masterForm['title']),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::GENERAL)]
            );
        }

        return $deleted;
    }

    /**
     * @param array $leadsData
     * @param string $leadPurgeUploads
     * @return array
     */
    private function getUploads(array $leadsData, string $leadPurgeUploads)
    {
        $uploads = [];
        if (!empty($leadsData) && !empty($leadPurgeUploads)) {
            foreach ($leadsData as $data) {
                if ('upload' === $data['field_type']) {
                    $uploads[$data['id']] = $this->getUploadFileModel($data['value']);
                }
            }
        }

        return $uploads;
    }

    /**
     * @param string $value
     * @return FilesModel|null
     */
    private function getUploadFileModel(string $value)
    {
        if (!Validator::isUuid($value)) {
            return null;
        }

        $filesModel = $this->framework->getAdapter(FilesModel::class)->findByUuid($value);

        return $filesModel;
    }


    /**
     * @param array $uploads
     * @param array $masterForm
     * @return int
     */
    private function purgeUploads(array $uploads, array $masterForm)
    {
        if (empty($uploads)) {
            return 0;
        }

        $count = 0;
        $deleted = 0;
        foreach ($uploads as $dataId => $filesModel) {
            $deleted += $this->purgeUpload($dataId, $filesModel);
            $count++;
        }

        $this->logger->info(
            sprintf('Purged %d of %d leads uploads for master form "%s"', $deleted, $count, $masterForm['title']),
            ['contao' => new ContaoContext(__METHOD__, ContaoContext::GENERAL)]
        );

        return $deleted;
    }

    /**
     * @param int $dataId
     * @param FilesModel|null $filesModel
     * @return int
     */
    private function purgeUpload(int $dataId, ?FilesModel $filesModel)
    {
        if (null === $filesModel) {
            $this->logger->error(
                sprintf('Purge leads upload (data ID %d): Model not found for deletion', $dataId),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ERROR)]
            );

            return 0;
        }

        try {
            if (!$this->fs->exists($this->rootDir.'/'.$filesModel->path)) {
                $this->logger->error(
                    sprintf('Purge leads upload (filesModel ID %d): File not found for deletion', $filesModel->id),
                    ['contao' => new ContaoContext(__METHOD__, ContaoContext::ERROR)]
                );

                return 0;
            }

            $this->fs->remove($this->rootDir.'/'.$filesModel->path);
            $filesModel->delete();

            $this->logger->info(
                sprintf('Purge leads upload (filesModel ID %d): File deleted', $filesModel->id),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::GENERAL)]
            );

            return 1;

        } catch (Exception $exception) {
            $this->logger->error(
                sprintf('Purge leads upload (filesModel ID %d): %s', $filesModel->id, $exception->getMessage()),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ERROR)]
            );
        }

        return 0;
    }


}
