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

final class SearchClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_queries()
    {
        $request = new Request('GET',
            'search?for=foo/%23%20bar&page=1&per-page=20&sort=date&order=desc&subject[]=cell-biology&type[]=research-article&use-date=published&start-date=2017-01-02&end-date=2017-02-03',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.search+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.search+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->query((['Accept' => 'application/vnd.elife.search+json; version=2']), 'foo/# bar', 1, 20, 'date', true,
            ['cell-biology'], ['research-article'], 'published', new DateTimeImmutable('2017-01-02'), new DateTimeImmutable('2017-02-03'))
            ->shouldBeLike($response)
        ;
    }

    public function it_always_queries_with_for()
    {
        $request = new Request('GET',
            'search?for=&page=1&per-page=20&sort=relevance&order=desc&use-date=default',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.search+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.search+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->query((['Accept' => 'application/vnd.elife.search+json; version=2']))
            ->shouldBeLike($response)
        ;
    }
}
