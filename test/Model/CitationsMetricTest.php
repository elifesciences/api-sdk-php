<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Model\CitationsMetric;
use eLife\ApiSdk\Model\CitationsMetricSource;
use PHPUnit\Framework\TestCase;

final class CitationsMetricTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_collection()
    {
        $metric = new CitationsMetric(new CitationsMetricSource('service', 'uri', 123));

        $this->assertInstanceOf(Collection::class, $metric);
    }

    /**
     * @test
     */
    public function it_can_be_traversed()
    {
        $array = [new CitationsMetricSource('service 1', 'uri', 123), new CitationsMetricSource('service 2', 'uri', 456)];
        $metric = new CitationsMetric(...$array);

        foreach ($metric as $i => $element) {
            $this->assertSame('service '.($i + 1), $element->getService());
        }
    }

    /**
     * @test
     */
    public function it_can_be_counted()
    {
        $array = [new CitationsMetricSource('service 1', 'uri', 123), new CitationsMetricSource('service 2', 'uri', 456)];
        $metric = new CitationsMetric(...$array);

        $this->assertFalse($metric->isEmpty());
        $this->assertEquals(2, $metric->count());
    }

    /**
     * @test
     */
    public function it_casts_to_an_array()
    {
        $array = [new CitationsMetricSource('service 1', 'uri', 123), new CitationsMetricSource('service 2', 'uri', 456)];
        $metric = new CitationsMetric(...$array);

        $this->assertEquals($array, $metric->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered()
    {
        $array = [new CitationsMetricSource('service 1', 'uri', 123), new CitationsMetricSource('service 2', 'uri', 456)];
        $metric = new CitationsMetric(...$array);

        $this->assertEquals($array, $metric->filter()->toArray());
    }

    /**
     * @test
     */
    public function it_can_be_filtered_with_a_callback()
    {
        $array = [new CitationsMetricSource('service 1', 'uri', 123), $service2 = new CitationsMetricSource('service 2', 'uri', 456)];
        $metric = new CitationsMetric(...$array);

        $filter = function (CitationsMetricSource $source) {
            return $source->getCitations() >= 200;
        };

        $this->assertEquals([$service2], $metric->filter($filter)->toArray());
    }

    /**
     * @test
     */
    public function it_has_a_highest_source()
    {
        $array = [new CitationsMetricSource('service 1', 'uri', 123), $service2 = new CitationsMetricSource('service 2', 'uri', 456)];
        $metric = new CitationsMetric(...$array);

        $this->assertEquals($service2, $metric->getHighest());
    }
}
