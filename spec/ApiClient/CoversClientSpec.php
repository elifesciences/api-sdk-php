<?php

namespace spec\eLife\ApiSdk\ApiClient;

use DateTimeImmutable;
use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class CoversClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_lists_covers()
    {
        $request = new Request('GET', 'covers?page=1&per-page=20&sort=page-views&order=desc&use-date=published&start-date=2017-01-02&end-date=2017-02-03',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.cover-list+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.cover-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listCovers(['Accept' => 'application/vnd.elife.cover-list+json; version=2'], 1, 20, 'page-views', true, 'published', new DateTimeImmutable('2017-01-02'), new DateTimeImmutable('2017-02-03'))->shouldBeLike($response);
    }

    public function it_lists_current_covers()
    {
        $request = new Request('GET', 'covers/current',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.cover-list+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.cover-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listCurrentCovers(['Accept' => 'application/vnd.elife.cover-list+json; version=2'], 1, 20, true)->shouldBeLike($response);
    }
}
