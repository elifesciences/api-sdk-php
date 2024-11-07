<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class PeopleClient
{
    const TYPE_PERSON = 'application/vnd.elife.person+json';
    const TYPE_PERSON_LIST = 'application/vnd.elife.person-list+json';

    use ApiClient;

    public function getPerson(array $headers, string $id) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "people/$id"]), $headers);
    }

    public function listPeople(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true,
        array $subjects = [],
        array $types = []
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'people',
                'query' => [
                    'page' => $page,
                    'per-page' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                    'subject[]' => $subjects,
                    'type[]' => $types,
                ],
            ]),
            $headers
        );
    }
}
