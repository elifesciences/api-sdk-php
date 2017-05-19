<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\AssetBlock;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\HasDoi;

final class FigureAsset implements HasDoi
{
    private $doi;
    private $label;
    private $sourceData;
    private $asset;

    /**
     * @internal
     */
    public function __construct(
        string $doi = null,
        string $label,
        Sequence $sourceData,
        AssetBlock $asset
    ) {
        $this->doi = $doi;
        $this->label = $label;
        $this->sourceData = $sourceData;
        $this->asset = $asset;
    }

    public function getDoi()
    {
        return $this->doi;
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @return Sequence|AssetFile[]
     */
    public function getSourceData() : Sequence
    {
        return $this->sourceData;
    }

    public function getAsset() : AssetBlock
    {
        return $this->asset;
    }
}
