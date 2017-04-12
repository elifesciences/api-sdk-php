<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Asset;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image as ImageModel;

final class ImageFile implements Asset
{
    private $doi;
    private $id;
    private $label;
    private $title;
    private $caption;
    private $image;
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
        ImageModel $image,
        array $attribution = [],
        array $sourceData = []
    ) {
        $this->doi = $doi;
        $this->id = $id;
        $this->label = $label;
        $this->title = $title;
        $this->caption = $caption;
        $this->image = $image;
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

    public function getImage() : ImageModel
    {
        return $this->image;
    }

    /**
     * @return string[]
     */
    public function getAttribution() : array
    {
        return $this->attribution;
    }

    /**
     * @return AssetFile[]
     */
    public function getSourceData() : array
    {
        return $this->sourceData;
    }
}
