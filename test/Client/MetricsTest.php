<?php

namespace test\eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\MetricsClient;
use eLife\ApiSdk\Client\Metrics;
use eLife\ApiSdk\Model\CitationsMetric;
use eLife\ApiSdk\Model\CitationsMetricSource;
use eLife\ApiSdk\Model\Identifier;
use test\eLife\ApiSdk\ApiTestCase;

final class MetricsTest extends ApiTestCase
{
    /** @var Metrics */
    private $metrics;

    /**
     * @before
     */
    protected function setUpMetrics()
    {
        $this->metrics = new Metrics(new MetricsClient($this->getHttpClient()));
    }

    /**
     * @test
     */
    public function it_gets_citations()
    {
        $this->mockMetricCitationsCall('article', '09560');

        $expected = new CitationsMetric(new CitationsMetricSource('Service', 'http://www.example.com/', 9560));

        $this->assertEquals($expected, $this->metrics->citations(Identifier::article('09560'))->wait());
    }

    /**
     * @test
     */
    public function it_gets_total_page_views()
    {
        $this->mockMetricPageViewsCall('article', '09560');

        $this->assertSame(9560, $this->metrics->totalPageViews(Identifier::article('09560'))->wait());
    }

    /**
     * @test
     */
    public function it_gets_total_downloads()
    {
        $this->mockMetricDownloadsCall('article', '09560');

        $this->assertSame(9560, $this->metrics->totalDownloads(Identifier::article('09560'))->wait());
    }
}
