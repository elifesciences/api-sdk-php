<?php

namespace eLife\ApiSdk\Model;

final class AnnotationDocument
{
    private $title;
    private $uri;

    /**
     * @internal
     */
    public function __construct(string $title, string $uri)
    {
        $this->title = $title;
        $this->uri = $uri;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getUri() : string
    {
        return $this->uri;
    }
}
