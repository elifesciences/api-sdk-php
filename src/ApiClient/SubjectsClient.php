<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class SubjectsClient
{
    const TYPE_SUBJECT = 'application/vnd.elife.subject+json';
    const TYPE_SUBJECT_LIST = 'application/vnd.elife.subject-list+json';

    use ApiClient;

    public function getSubject(array $headers, string $id) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "subjects/$id"]), $headers);
    }

    public function listSubjects(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'subjects',
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
