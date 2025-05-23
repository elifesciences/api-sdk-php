<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiClient\MetricsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Model\CitationsMetric;
use eLife\ApiSdk\Model\CitationsMetricSource;
use eLife\ApiSdk\Model\Identifier;
use GuzzleHttp\Promise\PromiseInterface;

final class Metrics
{
    const VERSION_METRIC_CITATIONS = 1;
    const VERSION_METRIC_TIME_PERIOD = 1;

    private $metricsClient;

    public function __construct(MetricsClient $metricsClient)
    {
        $this->metricsClient = $metricsClient;
    }

    public function citations(Identifier $identifier) : PromiseInterface
    {
        return $this->metricsClient
            ->citations(
                ['Accept' => (string) new MediaType(MetricsClient::TYPE_METRIC_CITATIONS, self::VERSION_METRIC_CITATIONS)],
                $identifier->getType(),
                $identifier->getId()
            )
            ->then(function (Result $result) {
                $sources = [];

                foreach ($result as $source) {
                    $sources[] = new CitationsMetricSource($source['service'], $source['uri'], $source['citations']);
                }

                return new CitationsMetric(...$sources);
            });
    }

    public function versionCitations(Identifier $identifier, int $version) : PromiseInterface
    {
        return $this->metricsClient
            ->versionCitations(
                ['Accept' => (string) new MediaType(MetricsClient::TYPE_METRIC_CITATIONS, self::VERSION_METRIC_CITATIONS)],
                $identifier->getType(),
                $identifier->getId(),
                $version
            )
            ->then(function (Result $result) {
                $sources = [];

                foreach ($result as $source) {
                    $sources[] = new CitationsMetricSource($source['service'], $source['uri'], $source['citations']);
                }

                return new CitationsMetric(...$sources);
            });
    }

    public function totalPageViews(Identifier $identifier) : PromiseInterface
    {
        return $this->metricsClient
            ->pageViews(
                ['Accept' => (string) new MediaType(MetricsClient::TYPE_METRIC_TIME_PERIOD, self::VERSION_METRIC_TIME_PERIOD)],
                $identifier->getType(),
                $identifier->getId()
            )
            ->then(function (Result $result) {
                return $result['totalValue'];
            });
    }

    public function totalDownloads(Identifier $identifier) : PromiseInterface
    {
        return $this->metricsClient
            ->downloads(
                ['Accept' => (string) new MediaType(MetricsClient::TYPE_METRIC_TIME_PERIOD, self::VERSION_METRIC_TIME_PERIOD)],
                $identifier->getType(),
                $identifier->getId()
            )
            ->then(function (Result $result) {
                return $result['totalValue'];
            });
    }
}
