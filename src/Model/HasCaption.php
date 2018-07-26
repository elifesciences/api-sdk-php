<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

interface HasCaption
{
    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @return Sequence|Block[]
     */
    public function getCaption() : Sequence;
}
