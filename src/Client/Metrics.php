<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\MetricsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Model\CitationsMetric;
use eLife\ApiSdk\Model\CitationsMetricSource;
use GuzzleHttp\Promise\PromiseInterface;

final class Metrics
{
    private $metricsClient;

    public function __construct(MetricsClient $metricsClient)
    {
        $this->metricsClient = $metricsClient;
    }

    public function citations(string $type, $id) : PromiseInterface
    {
        return $this->metricsClient
            ->citations(
                ['Accept' => new MediaType(MetricsClient::TYPE_METRIC_CITATIONS, 1)],
                $type,
                $id
            )
            ->then(function (Result $result) {
                $sources = [];

                foreach ($result as $source) {
                    $sources[] = new CitationsMetricSource($source['service'], $source['uri'], $source['citations']);
                }

                return new CitationsMetric(...$sources);
            });
    }

    public function totalPageViews(string $type, $id) : PromiseInterface
    {
        return $this->metricsClient
            ->pageViews(
                ['Accept' => new MediaType(MetricsClient::TYPE_METRIC_TIME_PERIOD, 1)],
                $type,
                $id
            )
            ->then(function (Result $result) {
                return $result['totalValue'];
            });
    }

    public function totalDownloads(string $type, $id) : PromiseInterface
    {
        return $this->metricsClient
            ->downloads(
                ['Accept' => new MediaType(MetricsClient::TYPE_METRIC_TIME_PERIOD, 1)],
                $type,
                $id
            )
            ->then(function (Result $result) {
                return $result['totalValue'];
            });
    }
}
