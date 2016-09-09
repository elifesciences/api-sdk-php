<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;

final class File
{
    private $doi;
    private $id;
    private $label;
    private $title;
    private $caption;
    private $mediaType;
    private $uri;

    /**
     * @internal
     */
    public function __construct(
        string $doi = null,
        string $id = null,
        string $label = null,
        string $title = null,
        array $caption,
        string $mediaType,
        string $uri
    ) {
        $this->doi = $doi;
        $this->id = $id;
        $this->label = $label;
        $this->title = $title;
        $this->caption = $caption;
        $this->mediaType = $mediaType;
        $this->uri = $uri;
    }

    /**
     * @return string|null
     */
    public function getDoi()
    {
        return $this->doi;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return Block[]
     */
    public function getCaption() : array
    {
        return $this->caption;
    }

    public function getMediaType(): string
    {
        return $this->mediaType;
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}
