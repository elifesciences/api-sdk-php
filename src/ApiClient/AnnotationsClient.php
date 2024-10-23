<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class AnnotationsClient
{
    const TYPE_ANNOTATION_LIST = 'application/vnd.elife.annotation-list+json';

    use ApiClient;

    public function listAnnotations(
        array $headers,
        string $by,
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true,
        string $useDate = 'updated',
        string $access = 'public'
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'annotations',
                'query' => [
                    'by' => $by,
                    'page' => $page,
                    'per-page' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                    'use-date' => $useDate,
                    'access' => $access,
                ],
            ]),
            $headers
        );
    }
}
