<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

interface HasAttribution
{
    /**
     * @return Sequence|string[]
     */
    public function getAttribution() : Sequence;
}
