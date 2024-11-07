<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class SubjectsClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_a_subject()
    {
        $request = new Request('GET', 'subjects/cell-biology',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.subject+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.subject+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getSubject(['Accept' => 'application/vnd.elife.subject+json; version=2'], 'cell-biology')
            ->shouldBeLike($response)
        ;
    }

    public function it_lists_subjects()
    {
        $request = new Request('GET', 'subjects?page=1&per-page=20&order=desc',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.subject-list+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.subject-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listSubjects(['Accept' => 'application/vnd.elife.subject-list+json; version=2'])
            ->shouldBeLike($response)
        ;
    }
}
