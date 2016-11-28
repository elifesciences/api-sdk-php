<?php

namespace test\eLife\ApiSdk;

trait TimezoneAwareTestCase
{
    private $originalTimezone;

    /**
     * @before
     */
    final public function setUpTimezone()
    {
        $this->originalTimezone = date_default_timezone_get();
    }

    /**
     * @after
     */
    final public function resetTimezone()
    {
        date_default_timezone_set($this->originalTimezone);
    }
}
