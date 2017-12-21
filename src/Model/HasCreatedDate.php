<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;

interface HasCreatedDate
{
    /**
     * @return DateTimeImmutable|null
     */
    public function getCreatedDate();
}
