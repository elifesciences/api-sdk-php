<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class JobAdvertsClient
{
    const TYPE_JOB_ADVERT = 'application/vnd.elife.job-advert+json';
    const TYPE_JOB_ADVERT_LIST = 'application/vnd.elife.job-advert-list+json';

    use ApiClient;

    public function getJobAdvert(array $headers, string $id) : PromiseInterface
    {
        return $this->getRequest(
            $this->createUri(['path' => "job-adverts/$id"]),
            $headers
        );
    }

    public function listJobAdverts(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        string $show = 'all',
        bool $descendingOrder = true
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'job-adverts',
                'query' => [
                    'page' => $page,
                    'per-page' => $perPage,
                    'show' => $show,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                ],
            ]),
            $headers
        );
    }
}
