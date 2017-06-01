<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\AssetBlock;
use eLife\ApiSdk\Model\Block;

/**
 * @SuppressWarnings(ForbiddenAbleSuffix)
 */
final class Table implements AssetBlock
{
    private $id;
    private $title;
    private $caption;
    private $attribution;
    private $tables;
    private $footnotes;

    /**
     * @internal
     */
    public function __construct(
        string $id = null,
        string $title = null,
        Sequence $caption,
        Sequence $attribution,
        array $tables,
        array $footnotes = []
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->caption = $caption;
        $this->attribution = $attribution;
        $this->tables = $tables;
        $this->footnotes = $footnotes;
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

    public function getTables() : array
    {
        return $this->tables;
    }

    /**
     * @return Block[]
     */
    public function getFootnotes() : array
    {
        return $this->footnotes;
    }
}
