<?php

namespace Terminal42\LeadsBundle\Command;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Validator;
use Doctrine\DBAL\Connection;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Terminal42\LeadsBundle\Event\LeadsPurgeEvent;

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
     * PurgeCommand constructor.
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
        $forms = $this->db->fetchAll(
            "SELECT id, title, leadPeriod, leadPurgeUploads FROM tl_form WHERE leadPeriod != ''"
        );

        foreach ($forms as $masterForm) {

            $leadPeriodTime = $this->convertTimePeriodToTime($masterForm['leadPeriod']);
            if (!empty($leads = $this->getAllLeads($masterForm['id'], $leadPeriodTime))) {

                $leadsData = $this->getAllLeadsData($leads);
                $uploads = $this->getUploads($leadsData, $masterForm['leadPurgeUploads']);

                // Add custom logic or modify data before purge
                $purgeEvent = new LeadsPurgeEvent($masterForm, $leads, $leadsData, $uploads);
                $this->eventDispatcher->dispatch(LeadsPurgeEvent::EVENT_NAME, $purgeEvent);

                $this->purgeLeadsData($purgeEvent->getLeadsData(), $purgeEvent->getMasterForm());
                $this->purgeUploads($purgeEvent->getUploads(), $purgeEvent->getMasterForm());
                $this->purgeLeads($purgeEvent->getLeads(), $purgeEvent->getMasterForm());

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
        $deleted = 0;
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

        $filesModel = FilesModel::findByUUid($value);

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

        $logLevel = LogLevel::INFO;
        $logMessage = 'Purged '.$deleted.' of '.$count.' leads uploads for master form "'.$masterForm['title'].'"';
        $this->logger->log($logLevel, $logMessage, array('contao' => new ContaoContext(__METHOD__, $logLevel)));

        return $deleted;
    }

    /**
     * @param int $dataId
     * @param FilesModel|null $filesModel
     * @return int
     */
    private function purgeUpload(int $dataId, ?FilesModel $filesModel)
    {
        $count = 0;
        $logLevel = LogLevel::ERROR;
        $logMessage = 'Purge leads upload data id "'.$dataId.'": ';

        if (null !== $filesModel) {
            $logMessage .= ' Model "'.$filesModel->id.'" ';
            try {
                if ($this->fs->exists($this->rootDir.'/'.$filesModel->path)) {
                    $this->fs->remove($this->rootDir.'/'.$filesModel->path);
                    $logLevel = LogLevel::INFO;
                    $logMessage .= 'File deleted';
                    $count++;
                } else {
                    $logMessage .= 'File not found for deletion';
                }
                $filesModel->delete();
            } catch (Exception $exception) {
                $logMessage .= $exception->getMessage();
            }
        } else {
            $logMessage .= 'Model not found for deletion';
        }

        $this->logger->log($logLevel, $logMessage, array('contao' => new ContaoContext(__METHOD__, $logLevel)));

        return $count;
    }


}
