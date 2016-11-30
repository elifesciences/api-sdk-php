<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Asset;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\File;

/**
 * @SuppressWarnings(ForbiddenAbleSuffix)
 */
final class Table implements Block, Asset
{
    private $doi;
    private $id;
    private $label;
    private $title;
    private $caption;
    private $tables;
    private $footer;
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
        array $tables,
        array $footer = [],
        array $sourceData = []
    ) {
        $this->doi = $doi;
        $this->id = $id;
        $this->label = $label;
        $this->title = $title;
        $this->caption = $caption;
        $this->tables = $tables;
        $this->footer = $footer;
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

    public function getTables(): array
    {
        return $this->tables;
    }

    /**
     * @return Block[]
     */
    public function getFooter() : array
    {
        return $this->footer;
    }

    /**
     * @return File[]
     */
    public function getSourceData(): array
    {
        return $this->sourceData;
    }
}
