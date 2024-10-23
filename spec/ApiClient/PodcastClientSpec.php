<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class PodcastClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_a_podcast_episode()
    {
        $request = new Request('GET', 'podcast-episodes/3',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.podcast-episode+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.podcast-episode+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getEpisode(['Accept' => 'application/vnd.elife.podcast-episode+json; version=2'], 3)
            ->shouldBeLike($response)
        ;
    }

    public function it_lists_episodes()
    {
        $request = new Request('GET', 'podcast-episodes?page=1&per-page=20&order=desc&containing[]=article/1234&containing[]=interview/5678',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.podcast-episode-list+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.podcast-episode-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listEpisodes(['Accept' => 'application/vnd.elife.podcast-episode-list+json; version=2'], 1, 20, true, ['article/1234', 'interview/5678'])
            ->shouldBeLike($response)
        ;
    }
}
