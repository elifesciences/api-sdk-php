<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class CollectionsClient
{
    const TYPE_COLLECTION = 'application/vnd.elife.collection+json';
    const TYPE_COLLECTION_LIST = 'application/vnd.elife.collection-list+json';

    use ApiClient;

    public function getCollection(array $headers, string $id) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "collections/$id"]), $headers);
    }

    public function listCollections(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true,
        array $subjects = [],
        array $containing = []
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'collections',
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
