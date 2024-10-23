<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class ProfilesClient
{
    const TYPE_PROFILE = 'application/vnd.elife.profile+json';
    const TYPE_PROFILE_LIST = 'application/vnd.elife.profile-list+json';

    use ApiClient;

    public function getProfile(array $headers, string $id) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "profiles/$id"]), $headers);
    }

    public function listProfiles(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'profiles',
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
