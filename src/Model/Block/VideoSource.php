<?php

namespace eLife\ApiSdk\Model\Block;

final class VideoSource
{
    private $mediaType;
    private $uri;

    /**
     * @internal
     */
    public function __construct(string $mediaType, string $uri)
    {
        $this->mediaType = $mediaType;
        $this->uri = $uri;
    }

    public function getMediaType() : string
    {
        return $this->mediaType;
    }

    public function getUri() : string
    {
        return $this->uri;
    }
}
