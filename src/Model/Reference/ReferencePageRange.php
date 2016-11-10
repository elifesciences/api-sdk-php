<?php

namespace eLife\ApiSdk\Model\Reference;

final class ReferencePageRange implements ReferencePages
{
    private $first;
    private $last;
    private $range;

    /**
     * @internal
     */
    public function __construct(string $first, string $last, string $range)
    {
        $this->first = $first;
        $this->last = $last;
        $this->range = $range;
    }

    public function getFirst() : string
    {
        return $this->first;
    }

    public function getLast() : string
    {
        return $this->last;
    }

    public function getRange() : string
    {
        return $this->range;
    }

    public function toString() : string
    {
        if ($this->range === $this->first) {
            return 'p. '.$this->range;
        }

        return 'pp. '.$this->range;
    }
}
