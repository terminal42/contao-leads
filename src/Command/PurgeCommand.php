<?php

namespace Terminal42\LeadsBundle\Command;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\System;
use Doctrine\DBAL\Connection;
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
     * @var Filesystem
     */
    private $fs;

    /**
     * Constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param Connection $db
     * @param Filesystem $fs
     */
    public function __construct(ContaoFrameworkInterface $framework, Connection $db, Filesystem $fs = null)
    {
        if (null === $fs) {
            $fs = new Filesystem();
        }

        $this->framework = $framework;
        $this->db = $db;
        $this->fs = $fs;

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
        $logger = System::getContainer()->get('monolog.logger.contao');
        $logger->log($logLevel, $logMessage, array('contao' => new ContaoContext(__METHOD__, $logLevel)));
        $output->writeln('<info>'.$logMessage.'</info>');
    }

    /**
     * @return bool
     * @throws \Doctrine\DBAL\DBALException
     */
    private function executeBatchPurge()
    {
        $purged = false;
        $forms = $this->db->fetchAll("SELECT id, title, leadPeriod FROM tl_form WHERE leadPeriod > 0");

        foreach ($forms as $masterForm) {

            if (!empty($leads = $this->getAllLeads($masterForm))) {
                $leadsIds = implode(',', array_keys($leads));

                if (!empty($leadsData = $this->getAllLeadsData($masterForm, $leadsIds))) {
                    $leadsDataIds = implode(',', array_keys($leadsData));
                    $deletedData = $this->db->executeUpdate(
                        "DELETE FROM tl_lead_data WHERE id IN(".$leadsDataIds.")"
                    );
                }

                $deletedLeads = $this->db->executeUpdate(
                    "DELETE FROM tl_lead WHERE id IN(".$leadsIds.")"
                );

                $logLevel = LogLevel::INFO;
                $logMessage = 'Purged leads for master form "'.$masterForm['title'].'": '.(int)$deletedLeads.' leads | '.(int)$deletedData.' data';
                $logger = System::getContainer()->get('monolog.logger.contao');
                $logger->log($logLevel, $logMessage, array('contao' => new ContaoContext(__METHOD__, $logLevel)));

                // Add custom logic
                if (isset($GLOBALS['TL_HOOKS']['postLeadsPurge']) && is_array($GLOBALS['TL_HOOKS']['postLeadsPurge'])) {
                    foreach ($GLOBALS['TL_HOOKS']['postLeadsPurge'] as $callback) {
                        if (is_array($callback)) {
                            System::importStatic($callback[0])->{$callback[1]}($masterForm, $leads, $leadsData);
                        } elseif (is_callable($callback)) {
                            $callback($masterForm, $leads, $leadsData);
                        }
                    }
                }

                $purged = true;
            }
        }

        return $purged;
    }

    /**
     * @param $masterForm
     * @return array
     */
    private function getAllLeads($masterForm)
    {
        $leads = [];

        $rows = $this->db->fetchAll(
            "SELECT * FROM tl_lead WHERE master_id=? AND created<?",
            [$masterForm['id'], (time() - (int)$masterForm['leadPeriod'])]
        );

        foreach ($rows as $row) {
            $leads[$row['id']] = $row;
        }

        return $leads;
    }

    /**
     * @param $masterForm
     * @param $leadsIds
     * @return array
     */
    private function getAllLeadsData($masterForm, $leadsIds)
    {
        $leadsData = [];

        if (!empty($leadsIds)) {
            $rows = $this->db->fetchAll(
                "SELECT * FROM tl_lead_data WHERE pid IN(".$leadsIds.") AND tstamp<?",
                [(time() - (int)$masterForm['leadPeriod'])]
            );

            foreach ($rows as $row) {
                $leadsData[$row['id']] = $row;
            }
        }

        return $leadsData;
    }
}
