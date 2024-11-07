<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class DigestsClient
{
    const TYPE_DIGEST = 'application/vnd.elife.digest+json';
    const TYPE_DIGEST_LIST = 'application/vnd.elife.digest-list+json';

    use ApiClient;

    public function getDigest(array $headers, string $id) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "digests/$id"]), $headers);
    }

    public function listDigests(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'digests',
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
