<?php

declare(strict_types=1);

namespace Terminal42\LeadsBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

class LeadsSaveEvent extends Event
{
    public const NAME = 'contao_leads.save';

    public function __construct(private int $leadId, private array $postData, private array $formConfig, private $files)
    {
    }

    public function getLeadId()
    {
        return $this->leadId;
    }

    public function getPostData()
    {
        return $this->postData;
    }

    public function getFormConfig()
    {
        return $this->formConfig;
    }

    public function getFiles()
    {
        return $this->files;
    }
}
