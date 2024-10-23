<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class PodcastClient
{
    const TYPE_PODCAST_EPISODE = 'application/vnd.elife.podcast-episode+json';
    const TYPE_PODCAST_EPISODE_LIST = 'application/vnd.elife.podcast-episode-list+json';

    use ApiClient;

    public function getEpisode(array $headers, int $number) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "podcast-episodes/$number"]), $headers);
    }

    public function listEpisodes(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true,
        array $containing = []
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'podcast-episodes',
                'query' => [
                    'page' => $page,
                    'per-page' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                    'containing[]' => $containing,
                ],
            ]),
            $headers
        );
    }
}
