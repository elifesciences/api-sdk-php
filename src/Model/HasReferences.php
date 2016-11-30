<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

interface HasReferences
{
    /**
     * @return Sequence|Reference[]
     */
    public function getReferences() : Sequence;
}
