<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class AnnotationsClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_lists_annotations()
    {
        $request = new Request('GET', 'annotations?by=user&page=1&per-page=20&order=desc&use-date=updated&access=restricted',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.annotation-list+json; version=1', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.annotation-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listAnnotations(['Accept' => 'application/vnd.elife.annotation-list+json; version=1'], 'user', 1, 20, true, 'updated', 'restricted')
            ->shouldBeLike($response);
    }
}
