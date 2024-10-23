<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class DigestsClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_a_digest()
    {
        $request = new Request('GET', 'digests/3',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.digest+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);

        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.digest+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getDigest(['Accept' => 'application/vnd.elife.digest+json; version=2'], '3')
            ->shouldBeLike($response)
        ;
    }

    public function it_lists_digests()
    {
        $request = new Request('GET', 'digests?page=1&per-page=20&order=desc',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.digest-list+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.digest-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listDigests(['Accept' => 'application/vnd.elife.digest-list+json; version=2'],
            1, 20, true)->shouldBeLike($response)
        ;
    }
}
