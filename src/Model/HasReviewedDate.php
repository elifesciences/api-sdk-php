<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;

interface HasReviewedDate
{
    /**
     * @return DateTimeImmutable|null
     */
    public function getReviewedDate();
}
