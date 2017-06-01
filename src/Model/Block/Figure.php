<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;

final class Figure implements Block
{
    private $assets;

    /**
     * @internal
     */
    public function __construct(FigureAsset ...$assets)
    {
        $this->assets = new ArraySequence($assets);
    }

    /**
     * @return Sequence|FigureAsset[]
     */
    public function getAssets() : Sequence
    {
        return $this->assets;
    }
}
