<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\CitationsMetricSource;
use PHPUnit_Framework_TestCase;

final class CitationsMetricSourceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_service()
    {
        $source = new CitationsMetricSource('service', 'uri', 123);

        $this->assertSame('service', $source->getService());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $source = new CitationsMetricSource('service', 'uri', 123);

        $this->assertSame('uri', $source->getUri());
    }

    /**
     * @test
     */
    public function it_has_citations()
    {
        $source = new CitationsMetricSource('service', 'uri', 123);

        $this->assertSame(123, $source->getCitations());
    }
}
