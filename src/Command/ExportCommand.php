<?php

namespace Terminal42\LeadsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Terminal42\LeadsBundle\Leads;

class ExportCommand extends ContainerAwareCommand
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('leads:export')
            ->setDescription('Exports the leads with the chosen configuration.')
            ->addArgument('config_id', InputArgument::OPTIONAL, 'The export configuration ID.');
    }

    /**
     * Get the config ID
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $configId = $input->getArgument('config_id');

        // Ask for the config ID if it has been not provided by default
        if (!$configId) {
            $helper = $this->getHelper('question');

            $question = new ChoiceQuestion(
                'Please enter the ID of the configuration you would like to export',
                $this->getAllConfigs()
            );

            $question->setValidator(function ($answer) {
                return $answer[0];
            });

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
        $configId = (int)$input->getArgument('config_id');

        // Validate the entered config ID
        if (!$this->validateConfigId($configId)) {
            $output->writeln(sprintf('<error>Invalid lead export configuration ID: %s</error>', $configId));

            return;
        }

        $this->getContainer()->get('contao.framework')->initialize();

        if (Leads::export($configId)) {
            $output->writeln('<info>The leads have been exported successfully.</info>');
        } else {
            $output->writeln('<error>There was an error exporting leads. Please check the system logs.</error>');
        }
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
        $target = $this->getContainer()->get('database_connection')->fetchColumn(
            'SELECT target FROM tl_lead_export WHERE id=?',
            [$id]
        );

        // Return false if the row has been not found or the target is browser
        if (!$target || $target === 'browser') {
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
        $rows    = $this->getContainer()->get('database_connection')->fetchAll(
            'SELECT id, name, (SELECT title FROM tl_form WHERE tl_form.id=tl_lead_export.pid) AS form FROM tl_lead_export ORDER BY name'
        );

        foreach ($rows as $row) {
            $configs[$row['id']] = sprintf('%s: %s', $row['form'], $row['name']);
        }

        return $configs;
    }
}
