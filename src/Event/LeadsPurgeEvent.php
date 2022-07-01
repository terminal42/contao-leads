<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * The terminal42_leads.purge event is triggered while 'leads:purge' after the purge is executed.
 *
 * Class LeadsPurgeEvent
 */
class LeadsPurgeEvent extends Event
{
    public const EVENT_NAME = 'terminal42_leads.purge';

    /**
     * @var array
     */
    private $masterForm;

    /**
     * @var int
     */
    private $deletedLeads;

    /**
     * @var int
     */
    private $deletedLeadsData;

    /**
     * @var int
     */
    private $deletedUploads;

    /**
     * LeadsPurgeEvent constructor.
     */
    public function __construct(array $masterForm, int $deletedLeads, int $deletedLeadsData, int $deletedUploads)
    {
        $this->masterForm = $masterForm;
        $this->deletedLeads = $deletedLeads;
        $this->deletedLeadsData = $deletedLeadsData;
        $this->deletedUploads = $deletedUploads;
    }

    public function getMasterForm(): array
    {
        return $this->masterForm;
    }

    public function getDeletedLeads(): int
    {
        return $this->deletedLeads;
    }

    public function getDeletedLeadsData(): int
    {
        return $this->deletedLeadsData;
    }

    public function getDeletedUploads(): int
    {
        return $this->deletedUploads;
    }
}
