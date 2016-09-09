<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;

final class Interviewee
{
    private $person;
    private $cvLines;

    public function __construct(Person $person, Collection $cvLines = null)
    {
        $this->person = $person;
        $this->cvLines = $cvLines;
    }

    public function getPerson() : Person
    {
        return $this->person;
    }

    public function hasCvLines() : bool
    {
        return !empty($this->cvLines);
    }

    /**
     * @return Collection|IntervieweeCvLine[]
     */
    public function getCvLines() : Collection
    {
        if (empty($this->cvLines)) {
            return new ArrayCollection([]);
        }

        return $this->cvLines;
    }
}
