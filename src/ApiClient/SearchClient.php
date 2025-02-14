<?php

namespace eLife\ApiSdk\ApiClient;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Psr7\parse_query;

class SearchClient
{
    const TYPE_SEARCH = 'application/vnd.elife.search+json';

    use ApiClient;

    public function query(
        array $headers = [],
        string $query = '',
        int $page = 1,
        int $perPage = 20,
        string $sort = 'relevance',
        bool $descendingOrder = true,
        array $subjects = [],
        array $elifeAssessmentSignificance = [],
        array $types = [],
        string $useDate = 'default',
        DateTimeImmutable $starts = null,
        DateTimeImmutable $ends = null
    ) : PromiseInterface {
        $uri = $this->createUri([
            'path' => 'search',
            'query' => [
                'for' => $query,
                'page' => $page,
                'per-page' => $perPage,
                'sort' => $sort,
                'order' => $descendingOrder ? 'desc' : 'asc',
                'subject[]' => $subjects,
                'type[]' => $types,
                'use-date' => $useDate,
                'start-date' => $starts ? $starts->format('Y-m-d') : null,
                'end-date' => $ends ? $ends->format('Y-m-d') : null,
            ],
        ]);

        if (!isset(parse_query($uri->getQuery())['for'])) {
            $uri = $uri->withQuery('for=&'.$uri->getQuery());
        }

        return $this->getRequest($uri, $headers);
    }
}
