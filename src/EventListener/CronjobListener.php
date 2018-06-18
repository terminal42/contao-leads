<?php

namespace Terminal42\LeadsBundle\EventListener;

use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class CronjobListener
{

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CronjobListener constructor.
     * @param KernelInterface $kernel
     * @param LoggerInterface $logger
     */
    public function __construct(KernelInterface $kernel, LoggerInterface $logger)
    {
        $this->kernel = $kernel;
        $this->logger = $logger;
    }

    function onDaily()
    {

        try {
            $application = new Application($this->kernel);
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
            $this->logger->log($logLevel, $logMessage, array('contao' => new ContaoContext(__METHOD__, $logLevel)));
        }

    }
}
