<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Reference\ReferenceDate;

interface Reference
{
    public function getDate() : ReferenceDate;
}
