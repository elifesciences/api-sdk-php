<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class EventsClient
{
    const TYPE_EVENT = 'application/vnd.elife.event+json';
    const TYPE_EVENT_LIST = 'application/vnd.elife.event-list+json';

    use ApiClient;

    public function getEvent(array $headers, string $id) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "events/$id"]), $headers);
    }

    public function listEvents(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        string $show = 'all',
        bool $descendingOrder = true
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'events',
                'query' => [
                    'page' => $page,
                    'per-page' => $perPage,
                    'show' => $show,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                ],
            ]),
            $headers
        );
    }
}
