<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Command;

use Doctrine\DBAL\Exception;
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
     */
    public function __construct(LeadsPurger $purger)
    {
        $this->purger = $purger;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('leads:purge')
            ->setDescription('Purge the leads that are older than the configured storage period.')
        ;
    }

    /**
     * @throws Exception
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>'.$this->purger->execute().'</info>');
    }
}
