<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class AnnualReportsClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_an_annual_report()
    {
        $request = new Request('GET', 'annual-reports/2012',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.annual-report+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.annual-report+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getReport(['Accept' => 'application/vnd.elife.annual-report+json; version=2'], 2012)
            ->shouldBeLike($response);
    }

    public function it_lists_annual_reports()
    {
        $request = new Request('GET', 'annual-reports?page=1&per-page=20&order=desc',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.annual-report-list+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.annual-report-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listReports(['Accept' => 'application/vnd.elife.annual-report-list+json; version=2'], 1, 20, true)
            ->shouldBeLike($response);
    }
}
