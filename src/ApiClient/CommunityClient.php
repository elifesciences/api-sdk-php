<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class CommunityClient
{
    const TYPE_COMMUNITY_LIST = 'application/vnd.elife.community-list+json';

    use ApiClient;

    public function listContent(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true,
        array $subjects = []
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'community',
                'query' => [
                    'page' => $page,
                    'per-page' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                    'subject[]' => $subjects,
                ],
            ]),
            $headers
        );
    }
}
