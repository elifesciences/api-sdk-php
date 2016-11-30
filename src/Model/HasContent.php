<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

interface HasContent
{
    /**
     * @return Sequence|Block[]
     */
    public function getContent() : Sequence;
}
