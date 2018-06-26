<?php

namespace Terminal42\LeadsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Terminal42\LeadsBundle\Service\LeadsPurger;

class PurgeCommand extends Command
{
    /**
     * @var LeadsPurger
     */
    private $purger;

    /**
     * PurgeCommand constructor.
     * @param LeadsPurger $purger
     */
    public function __construct(
        LeadsPurger $purger
    ) {
        $this->purger = $purger;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('leads:purge')
            ->setDescription('Purge the leads that are older than the configured storage period.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>'.$this->purger->execute().'</info>');
    }
}
