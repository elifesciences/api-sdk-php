<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class PromotionalCollectionsClient
{
    const TYPE_PROMOTIONAL_COLLECTION = 'application/vnd.elife.promotional-collection+json';
    const TYPE_PROMOTIONAL_COLLECTION_LIST = 'application/vnd.elife.promotional-collection-list+json';

    use ApiClient;

    public function getPromotionalCollection(array $headers, string $id) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "promotional-collections/$id"]), $headers);
    }

    public function listPromotionalCollections(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true,
        array $subjects = [],
        array $containing = []
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'promotional-collections',
                'query' => [
                    'page' => $page,
                    'per-page' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                    'subject[]' => $subjects,
                    'containing[]' => $containing,
                ],
            ]),
            $headers
        );
    }
}
