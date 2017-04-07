<?php

namespace eLife\ApiSdk\Model;

final class File
{
    private $mediaType;
    private $uri;
    private $filename;

    /**
     * @internal
     */
    public function __construct(
        string $mediaType,
        string $uri,
        string $filename
    ) {
        $this->mediaType = $mediaType;
        $this->uri = $uri;
        $this->filename = $filename;
    }

    public function getMediaType() : string
    {
        return $this->mediaType;
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    public function getFilename() : string
    {
        return $this->filename;
    }
}
