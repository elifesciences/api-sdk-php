<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class JobAdvertsClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_a_job_advert()
    {
        $request = new Request('GET', 'job-adverts/3',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.job-advert+json; version=1', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.job-advert+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getJobAdvert(['Accept' => 'application/vnd.elife.job-advert+json; version=1'], 3)
            ->shouldBeLike($response);
    }

    public function it_lists_job_adverts()
    {
        $request = new Request('GET', 'job-adverts?page=1&per-page=20&show=open&order=desc',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.job-advert-list+json; version=1', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.job-advert-list+json+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listJobAdverts(['Accept' => 'application/vnd.elife.job-advert-list+json; version=1'], 1, 20, 'open', true)
            ->shouldBeLike($response);
    }
}
