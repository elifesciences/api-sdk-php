<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;

interface HasUpdatedDate
{
    /**
     * @return DateTimeImmutable|null
     */
    public function getPublishedDate();
}
