<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class MetricsClient
{
    const TYPE_METRIC_CITATIONS = 'application/vnd.elife.metric-citations+json';
    const TYPE_METRIC_TIME_PERIOD = 'application/vnd.elife.metric-time-period+json';

    use ApiClient;

    public function citations(array $headers, string $type, string $id) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "metrics/$type/$id/citations"]), $headers);
    }

    public function downloads(
        array $headers,
        string $type,
        string $id,
        string $by = 'month',
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => "metrics/$type/$id/downloads",
                'query' => [
                    'by' => $by,
                    'page' => $page,
                    'per-page' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                ],
            ]),
            $headers);
    }

    public function pageViews(
        array $headers,
        string $type,
        string $id,
        string $by = 'month',
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => "metrics/$type/$id/page-views",
                'query' => [
                    'by' => $by,
                    'page' => $page,
                    'per-page' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                ],
            ]),
            $headers);
    }
}
