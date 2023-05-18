<?php

namespace Mautic\CoreBundle\Form;

use Symfony\Component\Validator\Constraints\Callback;

class ToBcBccFieldsCustomConstraintDTO
{
    private ?Callback $toConstraint   = null;
    private ?Callback $ccConstraint   = null;
    private ?Callback $bccConstraint  = null;

    public function getBccConstraint(): ?Callback
    {
        return $this->bccConstraint;
    }

    public function getCcConstraint(): ?Callback
    {
        return $this->ccConstraint;
    }

    public function getToConstraint(): ?Callback
    {
        return $this->toConstraint;
    }

    public function setToConstraint(?Callback $toConstraint): void
    {
        $this->toConstraint = $toConstraint;
    }

    public function setCcConstraint(?Callback $ccConstraint): void
    {
        $this->ccConstraint = $ccConstraint;
    }

    public function setBccConstraint(?Callback $bccConstraint): void
    {
        $this->bccConstraint = $bccConstraint;
    }
}
