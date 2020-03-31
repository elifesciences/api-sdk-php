<?php

namespace eLife\ApiSdk\Client;

trait ForSubject
{
    use InvalidateDataIfDifferent;

    private $subjectsQuery = [];

    final public function forSubject(string ...$subjectId) : self
    {
        $clone = clone $this;

        $clone->subjectsQuery = array_unique(array_merge($this->subjectsQuery, $subjectId));

        $clone->invalidateDataIfDifferent('subjectsQuery', $this);

        return $clone;
    }
}
