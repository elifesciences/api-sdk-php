<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class Interviewee
{
    private $person;
    private $cvLines;

    public function __construct(Person $person, Sequence $cvLines)
    {
        $this->person = $person;
        $this->cvLines = $cvLines;
    }

    public function getPerson() : Person
    {
        return $this->person;
    }

    /**
     * @return Sequence|IntervieweeCvLine[]
     */
    public function getCvLines() : Sequence
    {
        return $this->cvLines;
    }
}
