<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class LabsClient
{
    const TYPE_POST = 'application/vnd.elife.labs-post+json';
    const TYPE_POST_LIST = 'application/vnd.elife.labs-post-list+json';

    use ApiClient;

    public function getPost(array $headers, string $id) : PromiseInterface
    {
        return $this->getRequest(
            $this->createUri(['path' => "labs-posts/$id"]),
            $headers
        );
    }

    public function listPosts(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'labs-posts',
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
