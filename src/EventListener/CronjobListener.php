<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\EventListener;

use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\Log\LoggerInterface;
use Terminal42\LeadsBundle\Service\LeadsPurger;

class CronjobListener
{
    /**
     * @var LeadsPurger
     */
    private $purger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * CronjobListener constructor.
     */
    public function __construct(LeadsPurger $purger, LoggerInterface $logger)
    {
        $this->purger = $purger;
        $this->logger = $logger;
    }

    public function onDaily(): void
    {
        try {
            $this->purger->execute();
        } catch (\Exception $exception) {
            $this->logger->error(
                $exception->getMessage(),
                ['contao' => new ContaoContext(__METHOD__, ContaoContext::ERROR)]
            );
        }
    }
}
