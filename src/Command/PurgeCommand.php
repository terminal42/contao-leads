<?php

namespace Terminal42\LeadsBundle\Command;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\File;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Terminal42\LeadsBundle\Leads;
use \Exception;

class PurgeCommand extends Command
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * PurgeCommand constructor.
     * @param ContaoFrameworkInterface $framework
     * @param Connection $db
     * @param LoggerInterface $logger
     * @param Filesystem|null $fs
     */
    public function __construct(
        ContaoFrameworkInterface $framework,
        Connection $db,
        LoggerInterface $logger,
        Filesystem $fs = null
    ) {
        $this->framework = $framework;
        $this->db = $db;
        $this->logger = $logger;
        $this->fs = $fs ? $fs : new Filesystem();

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('leads:purge')
            ->setDescription('Purge the leads outsite the configured storage period.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->framework->initialize();

        $purged = $this->executeBatchPurge();

        if ($purged) {
            $logMessage = 'The leads have been purged successfully.';
        } else {
            $logMessage = 'No leads to purge.';
        }

        $logLevel = LogLevel::INFO;
        $this->logger->log($logLevel, $logMessage, array('contao' => new ContaoContext(__METHOD__, $logLevel)));
        $output->writeln('<info>'.$logMessage.'</info>');
    }

    /**
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    private function executeBatchPurge()
    {
        $purged = false;
        $forms = $this->db->fetchAll("SELECT id, title, leadPeriod FROM tl_form WHERE leadPeriod != ''");

        foreach ($forms as $masterForm) {

            $leadPeriodTime = $this->convertTimePeriodToTime($masterForm['leadPeriod']);
            if (!empty($leads = $this->getAllLeads($masterForm['id'], $leadPeriodTime))) {
                $deletedUploads = null;
                if (!empty($leadsData = $this->getAllLeadsData($leads))) {
                    $this->purgeLeadsData($leadsData, $masterForm);

                    if ($masterForm['leadPurgeUploads']) {
                        $deletedUploads = $this->purgeUploads($leadsData);
                    }
                }

                $this->purgeLeads($leads, $masterForm);

                // Add custom logic
                if (isset($GLOBALS['TL_HOOKS']['postLeadsPurge']) && is_array($GLOBALS['TL_HOOKS']['postLeadsPurge'])) {
                    foreach ($GLOBALS['TL_HOOKS']['postLeadsPurge'] as $callback) {
                        if (is_array($callback)) {
                            System::importStatic($callback[0])->{$callback[1]}(
                                $masterForm,
                                $leads,
                                $leadsData,
                                $deletedUploads
                            );
                        } elseif (is_callable($callback)) {
                            $callback($masterForm, $leads, $leadsData, $deletedUploads);
                        }
                    }
                }

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

        if (is_array($range) && isset($range['unit']) && isset($range['value'])) {
            if (false !== ($timestamp = strtotime('- '.$range['value'].' '.$range['unit']))) {
                return $timestamp;
            }
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
        $ids = implode(',', array_keys($leads));

        if (!empty($ids)) {
            $deleted = $this->db->executeUpdate(
                "DELETE FROM tl_lead WHERE id IN(".$ids.")"
            );

            $logLevel = LogLevel::INFO;
            $logMessage = 'Purged '.(int)$deleted.' leads for master form "'.$masterForm['title'].'"';
            $this->logger->log($logLevel, $logMessage, array('contao' => new ContaoContext(__METHOD__, $logLevel)));
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

        $ids = implode(',', array_keys($leads));

        if (!empty($ids)) {

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
        $ids = implode(',', array_keys($leadsData));

        if (!empty($ids)) {
            $deleted = $this->db->executeUpdate(
                "DELETE FROM tl_lead_data WHERE id IN(".$ids.")"
            );

            $logLevel = LogLevel::INFO;
            $logMessage = 'Purged '.(int)$deleted.' leads data for master form "'.$masterForm['title'].'"';
            $this->logger->log($logLevel, $logMessage, array('contao' => new ContaoContext(__METHOD__, $logLevel)));
        }

        return $deleted;
    }

    /**
     * @param array $leadsData
     * @return array
     */
    private function purgeUploads(array $leadsData)
    {
        $files = [];
        if (!empty($leadsData)) {
            foreach ($leadsData as $data) {
                if ('upload' === $data['field_type']) {
                    $files[$data['id']] = $this->purgeUpload($data['value']);
                }
            }
        }

        return $files;
    }

    /**
     * @param string $value
     * @return FilesModel|null
     */
    private function purgeUpload(string $value)
    {
        if (!Validator::isUuid($value)) {
            return null;
        }

        $filesModel = FilesModel::findByUUid($value);

        if (null !== $filesModel) {
            try {
                $file = new File($filesModel->path);
                if ($file->exists()) {
                    if ($file->delete()) {
                        $logLevel = LogLevel::INFO;
                        $logMessage = 'Upload "'.$filesModel->path.'"" deleted';
                    }
                }
            } catch (Exception $exception) {
                $logLevel = LogLevel::ERROR;
                $logMessage = 'Upload "'.$filesModel->path.'"" could not be deleted';
            }
        }

        $this->logger->log($logLevel, $logMessage, array('contao' => new ContaoContext(__METHOD__, $logLevel)));

        return $filesModel;
    }
}
