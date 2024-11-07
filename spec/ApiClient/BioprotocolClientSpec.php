<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class BioprotocolClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_queries()
    {
        $request = new Request('GET', 'bioprotocol/article/12345',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.bioprotocol+json; version=1', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.bioprotocol+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->list(['Accept' => 'application/vnd.elife.bioprotocol+json; version=1'], 'article', '12345')
            ->shouldBeLike($response);
    }
}
