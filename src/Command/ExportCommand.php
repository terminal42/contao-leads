<?php

namespace Terminal42\LeadsBundle\Command;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Terminal42\LeadsBundle\ExportTarget\LocalTarget;
use Terminal42\LeadsBundle\Leads;

class ExportCommand extends Command
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @var LocalTarget
     */
    private $localTarget;

    /**
     * ExportCommand constructor.
     *
     * @param Connection               $db
     * @param ContaoFrameworkInterface $framework
     * @param LocalTarget              $localTarget
     */
    public function __construct(Connection $db, ContaoFrameworkInterface $framework, LocalTarget $localTarget)
    {
        $this->db          = $db;
        $this->framework   = $framework;
        $this->localTarget = $localTarget;

        parent::__construct();
    }

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('leads:export')
            ->setDescription('Exports the leads with the chosen configuration.')
            ->addArgument(
                'config_id',
                InputArgument::OPTIONAL,
                'The export configuration ID.'
            )
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Export all configurations of all forms.'
            )
            ->addOption(
                'start',
                null,
                InputOption::VALUE_REQUIRED,
                'Records before this date will not be exported. You can use PHP strtotime() function.'
            )
            ->addOption(
                'stop',
                null,
                InputOption::VALUE_REQUIRED,
                'Records after this date will not be exported. You can use PHP strtotime() function.'
            );
    }

    /**
     * Get the config ID
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('all')) {
            return;
        }

        $configId = $input->getArgument('config_id');

        // Ask for the config ID if it has been not provided by default
        if (!$configId) {
            $helper = $this->getHelper('question');

            $question = new ChoiceQuestion(
                'Please enter the ID of the configuration you would like to export',
                $this->getAllConfigs()
            );

            $question->setValidator(
                function ($answer) {
                    return $answer;
                }
            );

            if (!($configId = $helper->ask($input, $output, $question))) {
                return;
            }
        }

        $input->setArgument('config_id', $configId);
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->framework->initialize();

            if ($input->getOption('all')) {
                $this->executeBatchExport($this->getOptions($input));
            } else {
                $this->executeSingleExport((int)$input->getArgument('config_id'), $this->getOptions($input));
            }
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return;
        }

        $output->writeln('<info>The leads have been exported successfully.</info>');
    }

    /**
     * Execute the single export
     *
     * @param int   $configId
     * @param array $options
     *
     * @throws \Exception
     */
    private function executeSingleExport($configId, array $options)
    {
        if (!$this->validateConfigId($configId)) {
            throw new \Exception(sprintf('Invalid lead export configuration ID: %s', $configId));
        }

        if (!Leads::export($configId, null, $this->localTarget)) {
            throw new \Exception('There was an error exporting leads. Please check the system logs.');
        }
    }

    /**
     * Execute the batch export
     *
     * @param array $options
     *
     * @throws \Exception
     */
    private function executeBatchExport(array $options)
    {
        foreach ($this->db->fetchAll('SELECT id FROM tl_lead_export WHERE cliExport=1') as $id) {
            if (!Leads::export((int)$id, null, $this->localTarget)) {
                throw new \Exception('There was an error exporting leads. Please check the system logs.');
            }
        }
    }

    /**
     * Get the options
     *
     * @param InputInterface $input
     *
     * @return array
     * @throws \Exception
     */
    private function getOptions(InputInterface $input)
    {
        $start = ($input->getOption('start') !== null) ? strtotime($input->getOption('start')) : null;
        $stop  = ($input->getOption('stop') !== null) ? strtotime($input->getOption('stop')) : null;

        // Validate the start option
        if ($start === false) {
            throw new \Exception(sprintf('The "start" option is invalid: %s', $input->getOption('start')));
        }

        // Validate the stop option
        if ($stop === false) {
            throw new \Exception(sprintf('The "stop" option is invalid: %s', $input->getOption('stop')));
        }

        return ['start' => $start, 'stop' => $stop];
    }

    /**
     * Validate the lead export config ID
     *
     * @param int $id
     *
     * @return bool
     */
    private function validateConfigId($id)
    {
        $exists = $this->db->fetchColumn('SELECT cliExport FROM tl_lead_export WHERE id=?', [$id]);

        // Return false if the row has been not found or the CLI export is not enabled
        if (!$exists) {
            return false;
        }

        return true;
    }

    /**
     * Get all lead configs
     *
     * @return array
     */
    private function getAllConfigs()
    {
        $configs = [];
        $rows    = $this->db->fetchAll(
            'SELECT id, name, (SELECT title FROM tl_form WHERE tl_form.id=tl_lead_export.pid) AS form FROM tl_lead_export WHERE cliExport=1 ORDER BY name'
        );

        foreach ($rows as $row) {
            $configs[$row['id']] = sprintf('%s: %s', $row['form'], $row['name']);
        }

        return $configs;
    }
}
