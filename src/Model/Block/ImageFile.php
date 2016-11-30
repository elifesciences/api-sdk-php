<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Asset;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\File;

final class ImageFile implements Asset
{
    private $doi;
    private $id;
    private $label;
    private $title;
    private $caption;
    private $altText;
    private $uri;
    private $attribution;
    private $sourceData;

    /**
     * @internal
     */
    public function __construct(
        string $doi = null,
        string $id = null,
        string $label = null,
        string $title = null,
        Sequence $caption,
        string $altText,
        string $uri,
        array $attribution = [],
        array $sourceData = []
    ) {
        $this->doi = $doi;
        $this->id = $id;
        $this->label = $label;
        $this->title = $title;
        $this->caption = $caption;
        $this->altText = $altText;
        $this->uri = $uri;
        $this->attribution = $attribution;
        $this->sourceData = $sourceData;
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
     * @return Sequence|Block[]
     */
    public function getCaption() : Sequence
    {
        return $this->caption;
    }

    public function getAltText(): string
    {
        return $this->altText;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @return string[]
     */
    public function getAttribution(): array
    {
        return $this->attribution;
    }

    /**
     * @return File[]
     */
    public function getSourceData(): array
    {
        return $this->sourceData;
    }
}
