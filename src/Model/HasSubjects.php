<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

interface HasSubjects
{
    /**
     * @return Sequence|Subject[]
     */
    public function getSubjects() : Sequence;
}
