<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\CitationsMetricSource;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CitationsMetricSourceTest extends TestCase
{
    #[Test]
    public function it_has_a_service()
    {
        $source = new CitationsMetricSource('service', 'uri', 123);

        $this->assertSame('service', $source->getService());
    }

    #[Test]
    public function it_has_a_uri()
    {
        $source = new CitationsMetricSource('service', 'uri', 123);

        $this->assertSame('uri', $source->getUri());
    }

    #[Test]
    public function it_has_citations()
    {
        $source = new CitationsMetricSource('service', 'uri', 123);

        $this->assertSame(123, $source->getCitations());
    }
}
