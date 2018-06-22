<?php

namespace Terminal42\LeadsBundle\Command;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Terminal42\LeadsBundle\Leads;

class ExportCommand extends Command
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
     * @param Connection               $db
     * @param Filesystem               $fs
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
            ->setName('leads:export')
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
            )
        ;
    }

    /**
     * Get the config ID
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('all') || null !== $input->getArgument('config_id')) {
            return;
        }

        $configs = $this->getAllConfigs();

        if (0 === count($configs)) {
            throw new \RuntimeException('No export configurations available.');
        }

        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Please enter the ID of the configuration you would like to export',
            $configs
        );

        $question->setValidator(
            function ($answer) {
                return $answer;
            }
        );

        if (!($configId = $helper->ask($input, $output, $question))) {
            return;
        }

        $input->setArgument('config_id', $configId);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ((!$input->getOption('all') && !$input->getArgument('config_id'))
            || ($input->getOption('all') && $input->getArgument('config_id'))
        ) {
            throw new InvalidArgumentException('Must either have an export config ID or the --all flag to export.');
        }

        $this->framework->initialize();

        list($start, $stop) = $this->getStartStop($input);

        if ($input->getOption('all')) {
            $success = $this->executeBatchExport($start, $stop);
        } else {
            $config = $this->db->fetchAssoc(
                'SELECT id, targetPath, cliExport FROM tl_lead_export WHERE id=?',
                [(int) $input->getArgument('config_id')]
            );

            if (empty($config) || !$config['cliExport']) {
                throw new InvalidArgumentException(
                    sprintf('Leads export ID %s is invalid or not enabled for CLI output.', $config['id'])
                );
            }

            $success = $this->export($config['id'], $config['targetPath'], $start, $stop);
        }

        if ($success) {
            $output->writeln('<info>The leads have been exported successfully.</info>');
        } else {
            $output->writeln('<error>Nothing to export.</error>');
        }
    }

    /**
     * Execute the batch export
     *
     * @param int|null $start
     * @param int|null $stop
     */
    private function executeBatchExport($start, $stop)
    {
        $success = false;
        $configs = $this->db->fetchAll("SELECT id, targetPath FROM tl_lead_export WHERE cliExport='1'");

        foreach ($configs as $config) {
            if ($this->export($config['id'], $config['targetPath'], $start, $stop)) {
                $success = true;
            }
        }

        return $success;
    }

    /**
     * Export a leads configuration with start and stop date if not empty.
     *
     * @param int      $configId
     * @param string   $targetPath
     * @param int|null $start
     * @param int|null $stop
     *
     * @return true
     */
    private function export($configId, $targetPath, $start, $stop)
    {
        $ids = null;

        if ($start || $stop) {
            $query = $this->db->createQueryBuilder();
            $query
                ->select('l.id')
                ->from('tl_lead', 'l')
                ->join(
                    'l',
                    'tl_lead_export',
                    'e',
                    $query->expr()->orX(
                        $query->expr()->eq('l.master_id', 'e.pid'),
                        $query->expr()->eq('l.form_id', 'e.pid')
                    )
                )
                ->where('e.id = :export_id')
                ->setParameter('export_id', $configId)
                ->groupBy('l.id')
            ;

            if (null !== $start) {
                $query
                    ->andWhere('l.created >= :start')
                    ->setParameter('start', $start)
                ;
            }

            if (null !== $stop) {
                $query
                    ->andWhere('l.created <= :stop')
                    ->setParameter('stop', $stop)
                ;
            }

            $ids = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);

            if (0 === count($ids)) {
                return false;
            }
        }

        $file = Leads::export($configId, $ids);

        if ('/' !== substr($targetPath, 0, 1)) {
            $targetPath = TL_ROOT.'/'.$targetPath;
        }

        $this->fs->mkdir($targetPath);
        $this->fs->copy(TL_ROOT.'/'.$file->path, $targetPath.'/'.$file->name, true);

        return true;
    }

    /**
     * Get the start and stop options.
     *
     * @param InputInterface $input
     *
     * @return array
     *
     * @throws InvalidArgumentException
     */
    private function getStartStop(InputInterface $input)
    {
        $start = $input->getOption('start') ? strtotime($input->getOption('start')) : null;
        $stop  = $input->getOption('stop') ? strtotime($input->getOption('stop')) : null;

        // Validate the start option
        if ($start === false) {
            throw new InvalidArgumentException(
                sprintf('The "start" option is invalid: %s', $input->getOption('start'))
            );
        }

        // Validate the stop option
        if ($stop === false) {
            throw new InvalidArgumentException(
                sprintf('The "stop" option is invalid: %s', $input->getOption('stop'))
            );
        }

        return [$start, $stop];
    }

    /**
     * Get all lead configs
     *
     * @return array
     */
    private function getAllConfigs()
    {
        $configs = [];
        $rows    = $this->db->fetchAll("
            SELECT id, name, (SELECT title FROM tl_form WHERE tl_form.id=tl_lead_export.pid) AS form 
            FROM tl_lead_export 
            WHERE cliExport='1' 
            ORDER BY name
        ");

        foreach ($rows as $row) {
            $configs[$row['id']] = sprintf('%s: %s', $row['form'], $row['name']);
        }

        return $configs;
    }
}
