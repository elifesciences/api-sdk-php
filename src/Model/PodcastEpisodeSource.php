<?php

namespace eLife\ApiSdk\Model;

final class PodcastEpisodeSource
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
