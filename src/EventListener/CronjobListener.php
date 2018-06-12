<?php

namespace Terminal42\LeadsBundle\EventListener;

use Contao\Backend;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Environment;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Psr\Log\LogLevel;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\Kernel;

class CronjobListener
{
    function onDaily()
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
            $logger = System::getContainer()->get('monolog.logger.contao');
            $logger->log($logLevel, $logMessage, array('contao' => new ContaoContext(__METHOD__, $logLevel)));
        }

    }
}
