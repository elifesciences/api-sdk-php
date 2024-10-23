<?php

namespace eLife\ApiSdk\ApiClient;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class ReviewedPreprintsClient
{
    const TYPE_REVIEWED_PREPRINT = 'application/vnd.elife.reviewed-preprint+json';
    const TYPE_REVIEWED_PREPRINT_LIST = 'application/vnd.elife.reviewed-preprint-list+json';

    use ApiClient;

    public function getReviewedPreprint(array $headers, string $id) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "reviewed-preprints/$id"]), $headers);
    }

    public function listReviewedPreprints(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true,
        string $useDate = 'default',
        DateTimeImmutable $starts = null,
        DateTimeImmutable $ends = null
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'reviewed-preprints',
                'query' => [
                    'page' => $page,
                    'per-page' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                    'use-date' => $useDate,
                    'start-date' => $starts ? $starts->format('Y-m-d') : null,
                    'end-date' => $ends ? $ends->format('Y-m-d') : null,
                ],
            ]),
            $headers
        );
    }
}
