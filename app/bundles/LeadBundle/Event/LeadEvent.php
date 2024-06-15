<?php

namespace Mautic\LeadBundle\Event;

use Mautic\CoreBundle\Event\CommonEvent;
use Mautic\LeadBundle\Entity\Lead;

class LeadEvent extends CommonEvent
{
    public function __construct(Lead &$lead, bool $isNew = false)
    {
        $this->entity = &$lead;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Lead entity.
     */
    public function getLead(): Lead
    {
        return $this->entity;
    }

    /**
     * Sets the Lead entity.
     */
    public function setLead(Lead $lead): void
    {
        $this->entity = $lead;
    }
}
