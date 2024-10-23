<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class AnnualReportsClient
{
    const TYPE_ANNUAL_REPORT = 'application/vnd.elife.annual-report+json';
    const TYPE_ANNUAL_REPORT_LIST = 'application/vnd.elife.annual-report-list+json';

    use ApiClient;

    public function getReport(array $headers, int $year) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "annual-reports/$year"]), $headers);
    }

    public function listReports(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'annual-reports',
                'query' => [
                    'page' => $page,
                    'per-page' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                ],
            ]),
            $headers
        );
    }
}
