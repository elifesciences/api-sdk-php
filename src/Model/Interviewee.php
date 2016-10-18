<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;

final class Interviewee
{
    private $person;
    private $cvLines;

    public function __construct(Person $person, Sequence $cvLines = null)
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
     * @return Sequence|IntervieweeCvLine[]
     */
    public function getCvLines() : Sequence
    {
        if (empty($this->cvLines)) {
            return new ArraySequence([]);
        }

        return $this->cvLines;
    }
}
