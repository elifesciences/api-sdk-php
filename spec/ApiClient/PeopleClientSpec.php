<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class PeopleClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_a_person()
    {
        $request = new Request('GET', 'people/fbloggs',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.person+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.person+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getPerson(['Accept' => 'application/vnd.elife.person+json; version=2'], 'fbloggs')
            ->shouldBeLike($response)
        ;
    }

    public function it_lists_people()
    {
        $request = new Request('GET', 'people?page=1&per-page=20&order=desc&subject[]=cell-biology&type[]=senior-editor',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.person-list+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.person-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listPeople(['Accept' => 'application/vnd.elife.person-list+json; version=2'], 1, 20, true,
            ['cell-biology'], ['senior-editor'])->shouldBeLike($response)
        ;
    }
}
