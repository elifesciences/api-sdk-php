<?php

namespace eLife\ApiSdk\Model;

final class CitationsMetricSource
{
    private $service;
    private $uri;
    private $citations;

    /**
     * @internal
     */
    public function __construct(string $service, string $uri, int $citations)
    {
        $this->service = $service;
        $this->uri = $uri;
        $this->citations = $citations;
    }

    public function getService() : string
    {
        return $this->service;
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    public function getCitations() : int
    {
        return $this->citations;
    }
}
