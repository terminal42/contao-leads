<?php

namespace Terminal42\LeadsBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * The terminal42.leads_purge event is triggered while 'leads:purge' after the purge is executed
 *
 * Class LeadsPurgeEvent
 * @package Terminal42\LeadsBundle\Event
 */
class LeadsPurgeEvent extends Event
{

    public const EVENT_NAME = 'terminal42.leads_purge';

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
     * @param array $masterForm
     * @param int $deletedLeads
     * @param int $deletedLeadsData
     * @param int $deletedUploads
     */
    public function __construct(array $masterForm, int $deletedLeads, int $deletedLeadsData, int $deletedUploads)
    {
        $this->masterForm = $masterForm;
        $this->deletedLeads = $deletedLeads;
        $this->deletedLeadsData = $deletedLeadsData;
        $this->deletedUploads = $deletedUploads;
    }

    /**
     * @return array
     */
    public function getMasterForm(): array
    {
        return $this->masterForm;
    }

    /**
     * @return int
     */
    public function getDeletedLeads(): int
    {
        return $this->deletedLeads;
    }

    /**
     * @return int
     */
    public function getDeletedLeadsData(): int
    {
        return $this->deletedLeadsData;
    }

    /**
     * @return int
     */
    public function getDeletedUploads(): int
    {
        return $this->deletedUploads;
    }

}
