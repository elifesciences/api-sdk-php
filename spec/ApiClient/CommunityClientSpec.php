<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class CommunityClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_lists_several_content_types()
    {
        $request = new Request(
            'GET',
            'community?page=1&per-page=20&order=desc&subject[]=cell-biology',
            [
                'X-Foo' => 'bar',
                'Accept' => 'application/vnd.elife.community-list+json; version=2',
                'User-Agent' => 'eLifeApiClient/'.Version::get(),
            ]
        );
        $response = new FulfilledPromise(new ArrayResult(
            new MediaType('application/vnd.elife.community-list+json', 2),
            ['items' => ['bar', 'baz'], 'total' => 2]
        ));

        $this->httpClient->send($request)->willReturn($response);

        $this
            ->listContent(
                ['Accept' => 'application/vnd.elife.community-list+json; version=2'],
                1,
                20,
                true,
                ['cell-biology']
            )
            ->shouldBeLike($response)
        ;
    }
}
