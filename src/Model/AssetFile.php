<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class AssetFile implements HasAttribution, HasCaption
{
    private $doi;
    private $id;
    private $label;
    private $title;
    private $caption;
    private $attribution;
    private $file;

    /**
     * @internal
     */
    public function __construct(
        string $doi = null,
        string $id = null,
        string $label = null,
        string $title = null,
        Sequence $caption,
        Sequence $attribution,
        File $file
    ) {
        $this->doi = $doi;
        $this->id = $id;
        $this->label = $label;
        $this->title = $title;
        $this->caption = $caption;
        $this->attribution = $attribution;
        $this->file = $file;
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

    /**
     * @return Sequence|string[]
     */
    public function getAttribution() : Sequence
    {
        return $this->attribution;
    }

    public function getFile() : File
    {
        return $this->file;
    }
}
