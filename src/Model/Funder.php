<?php

namespace eLife\ApiSdk\Model;

final class Funder
{
    private $place;
    private $funderId;

    /**
     * @internal
     */
    public function __construct(Place $place, string $funderId = null)
    {
        $this->place = $place;
        $this->funderId = $funderId;
    }

    public function getPlace(): Place
    {
        return $this->place;
    }

    /**
     * @return string|null
     */
    public function getFunderId()
    {
        return $this->funderId;
    }
}
