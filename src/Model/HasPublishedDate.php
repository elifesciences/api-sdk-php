<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;

interface HasPublishedDate
{
    /**
     * @return DateTimeImmutable|null
     */
    public function getPublishedDate();
}
