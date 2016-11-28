<?php

namespace test\eLife\ApiSdk;

trait TimezoneAwareTestCase
{
    private static $originalTimezone;

    /**
     * @beforeClass
     */
    final public static function recordTimezone()
    {
        self::$originalTimezone = date_default_timezone_get();
    }

    /**
     * @after
     */
    final public function resetTimezone()
    {
        date_default_timezone_set(self::$originalTimezone);
    }
}
