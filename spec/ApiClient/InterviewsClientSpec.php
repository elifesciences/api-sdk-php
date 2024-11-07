<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class InterviewsClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_an_interview()
    {
        $request = new Request('GET', 'interviews/3',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.interview+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.interview+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getInterview(['Accept' => 'application/vnd.elife.interview+json; version=2'], '3')
            ->shouldBeLike($response)
        ;
    }

    public function it_lists_interviews()
    {
        $request = new Request('GET', 'interviews?page=1&per-page=20&order=desc',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.interview-list+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.interview-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listInterviews(['Accept' => 'application/vnd.elife.interview-list+json; version=2'],
            1, 20, true)->shouldBeLike($response)
        ;
    }
}
