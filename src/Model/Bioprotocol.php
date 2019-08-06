<?php

namespace eLife\ApiSdk\Model;

final class Bioprotocol
{
    private $sectionId;
    private $status;
    private $uri;

    /**
     * @internal
     */
    public function __construct(string $sectionId, bool $status, string $uri)
    {
        $this->sectionId = $sectionId;
        $this->status = $status;
        $this->uri = $uri;
    }

    public function getSectionId() : string
    {
        return $this->sectionId;
    }

    public function getStatus() : bool
    {
        return $this->status;
    }

    public function getUri() : string
    {
        return $this->uri;
    }
}
