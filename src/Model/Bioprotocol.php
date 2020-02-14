<?php

namespace eLife\ApiSdk\Model;

final class Bioprotocol
{
    private $sectionId;
    private $title;
    private $status;
    private $uri;

    /**
     * @internal
     */
    public function __construct(string $sectionId, string $title, bool $status, string $uri)
    {
        $this->sectionId = $sectionId;
        $this->title = $title;
        $this->status = $status;
        $this->uri = $uri;
    }

    public function getSectionId() : string
    {
        return $this->sectionId;
    }

    public function getTitle() : string
    {
        return $this->title;
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
