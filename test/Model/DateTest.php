<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Date;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\TimezoneAwareTestCase;

final class DateTest extends PHPUnit_Framework_TestCase
{
    use TimezoneAwareTestCase;

    /**
     * @test
     */
    public function it_has_a_year()
    {
        $date = new Date(2000);

        $this->assertSame(2000, $date->getYear());
    }

    /**
     * @test
     */
    public function it_may_have_a_month()
    {
        $with = new Date(2000, 1);
        $withOut = new Date(2000);

        $this->assertSame(1, $with->getMonth());
        $this->assertNull($withOut->getMonth());
    }

    /**
     * @test
     */
    public function it_may_have_a_day()
    {
        $with = new Date(2000, 1, 1);
        $withOut = new Date(2000);

        $this->assertSame(1, $with->getDay());
        $this->assertNull($withOut->getDay());
    }

    /**
     * @test
     * @dataProvider createStringProvider
     */
    public function it_can_be_created_from_a_string(string $string, int $year, int $month = null, int $day = null)
    {
        $date = Date::fromString($string);

        $this->assertSame($year, $date->getYear());
        $this->assertSame($month, $date->getMonth());
        $this->assertSame($day, $date->getDay());
    }

    public function createStringProvider() : array
    {
        return [
            'day' => ['2000-01-01', 2000, 1, 1],
            'month' => ['2000-01', 2000, 1, null],
            'year' => ['2000', 2000, null, null],
        ];
    }

    /**
     * @test
     * @dataProvider formatStringProvider
     */
    public function it_can_be_cast_to_a_string(string $date)
    {
        $this->assertSame($date, Date::fromString($date)->toString());
    }

    public function formatStringProvider() : array
    {
        return [
            'day' => ['2000-01-01'],
            'month' => ['2000-01'],
            'year' => ['2000'],
        ];
    }

    /**
     * @test
     * @dataProvider formatProvider
     */
    public function it_can_be_formated(string $date, string $expected)
    {
        $date = Date::fromString($date);

        $this->assertSame($expected, $date->format());
    }

    public function formatProvider() : array
    {
        return [
            'day' => ['2000-01-01', 'January 1, 2000'],
            'month' => ['2000-01', 'January 2000'],
            'year' => ['2000', '2000'],
        ];
    }

    /**
     * @test
     * @dataProvider timezoneProvider
     */
    public function it_works_in_different_timezones(string $timezone = null, string $string, string $format)
    {
        if ($timezone) {
            date_default_timezone_set($timezone);
        }

        $date = Date::fromString($string);
        $this->assertEquals($string, $date->toString());
        $this->assertEquals($format, $date->format());
    }

    public function timezoneProvider() : array
    {
        return [
            'UTC' => [null, '2011-12-30', 'December 30, 2011'],
            'http://www.bbc.co.uk/news/world-asia-16351377' => ['Pacific/Apia', '2011-12-30', 'December 30, 2011'],
        ];
    }
}
