<?php

namespace eLife\ApiSdk\Client;

trait ForSubject
{
    use InvalidateData;

    private $subjectsQuery = [];

    final public function forSubject(string ...$subjectId) : self
    {
        $clone = clone $this;

        $clone->subjectsQuery = array_unique(array_merge($this->subjectsQuery, $subjectId));

        if ($clone->subjectsQuery !== $this->subjectsQuery) {
            $clone->invalidateData();
        }

        return $clone;
    }
}
