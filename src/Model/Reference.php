<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Reference\ReferenceDate;

interface Reference
{
    public function getId() : string;

    public function getDate() : ReferenceDate;

    /**
     * @return string|null
     */
    public function getDiscriminator();
}
