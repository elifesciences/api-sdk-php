<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

interface Asset extends HasDoi, HasId
{
    /**
     * @return string|null
     */
    public function getLabel();

    /**
     * @return string|null
     */
    public function getTitle();

    /**
     * @return Sequence|Block[]
     */
    public function getCaption() : Sequence;
}
