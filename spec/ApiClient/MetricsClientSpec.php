<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class MetricsClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_citations()
    {
        $request = new Request('GET', 'metrics/article/01234/citations',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.metric-citations+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.metric-citations+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->citations(['Accept' => 'application/vnd.elife.metric-citations+json; version=2'], 'article', '01234')
            ->shouldBeLike($response);
    }

    public function it_gets_downloads()
    {
        $request = new Request('GET', 'metrics/article/01234/downloads?by=month&page=1&per-page=20&order=desc',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.metric-time-period+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.metric-time-period+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->downloads(['Accept' => 'application/vnd.elife.metric-time-period+json; version=2'], 'article', '01234')
            ->shouldBeLike($response);
    }

    public function it_gets_page_views()
    {
        $request = new Request('GET', 'metrics/article/01234/page-views?by=month&page=1&per-page=20&order=desc',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.metric-time-period+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.metric-time-period+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->pageViews(['Accept' => 'application/vnd.elife.metric-time-period+json; version=2'], 'article', '01234')
            ->shouldBeLike($response);
    }
}
