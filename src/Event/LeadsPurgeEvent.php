<?php

namespace Terminal42\LeadsBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * The terminal42.leads_purge event is triggered while 'leads:purge' before the purge is executed
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
     * @var array
     */
    private $leads;

    /**
     * @var array
     */
    private $leadsData;

    /**
     * @var array
     */
    private $uploads;

    /**
     * LeadsPurgeEvent constructor.
     * @param array $masterForm
     * @param array $leads
     * @param array $leadsData
     * @param array $uploads
     */
    public function __construct(array $masterForm, array $leads, array $leadsData, array $uploads)
    {
        $this->masterForm = $masterForm;
        $this->leads = $leads;
        $this->leadsData = $leadsData;
        $this->uploads = $uploads;
    }

    /**
     * @return array
     */
    public function getMasterForm(): array
    {
        return $this->masterForm;
    }

    /**
     * @param array $masterForm
     * @return LeadsPurgeEvent
     */
    public function setMasterForm(array $masterForm): LeadsPurgeEvent
    {
        $this->masterForm = $masterForm;

        return $this;
    }

    /**
     * @return array
     */
    public function getLeads(): array
    {
        return $this->leads;
    }

    /**
     * @param array $leads
     * @return LeadsPurgeEvent
     */
    public function setLeads(array $leads): LeadsPurgeEvent
    {
        $this->leads = $leads;

        return $this;
    }

    /**
     * @return array
     */
    public function getLeadsData(): array
    {
        return $this->leadsData;
    }

    /**
     * @param array $leadsData
     * @return LeadsPurgeEvent
     */
    public function setLeadsData(array $leadsData): LeadsPurgeEvent
    {
        $this->leadsData = $leadsData;

        return $this;
    }

    /**
     * @return array
     */
    public function getUploads(): array
    {
        return $this->uploads;
    }

    /**
     * @param array $uploads
     * @return LeadsPurgeEvent
     */
    public function setUploads(array $uploads): LeadsPurgeEvent
    {
        $this->uploads = $uploads;

        return $this;
    }

}
