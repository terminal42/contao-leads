<?php

namespace Terminal42\LeadsBundle\EventListener;

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\System;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\Kernel;

class CronjobListener
{
    function onDaily(LoggerInterface $logger)
    {

        try {
            /** @var Kernel $kernel */
            $kernel = System::getContainer()->get('kernel');

            $application = new Application($kernel);
            $input = new ArrayInput(
                [
                    'command' => 'leads:purge',
                ]
            );
            $output = new BufferedOutput();
            $application->run($input, $output);

        } catch (\Exception $exception) {
            $logLevel = LogLevel::ERROR;
            $logMessage = $exception->getMessage();
            $logger->log($logLevel, $logMessage, array('contao' => new ContaoContext(__METHOD__, $logLevel)));
        }

    }
}
