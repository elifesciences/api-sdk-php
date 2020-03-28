<?php

namespace eLife\ApiSdk\Client;

trait ForSubject
{
    private $count;
    private $subjectsQuery = [];

    public function forSubject(string ...$subjectId) : self
    {
        $clone = clone $this;

        $clone->subjectsQuery = array_unique(array_merge($this->subjectsQuery, $subjectId));

        if ($clone->subjectsQuery !== $this->subjectsQuery) {
            $clone->count = null;
        }

        return $clone;
    }
}
