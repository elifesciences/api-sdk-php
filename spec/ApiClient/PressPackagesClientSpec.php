<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class PressPackagesClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_a_package()
    {
        $request = new Request('GET', 'press-packages/3',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.press-package+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.press-package+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getPackage(['Accept' => 'application/vnd.elife.press-package+json; version=2'], '3')
            ->shouldBeLike($response);
    }

    public function it_lists_packages()
    {
        $request = new Request('GET', 'press-packages?page=1&per-page=20&order=desc&subject[]=cell-biology',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.press-package-list+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.press-package-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listPackages(['Accept' => 'application/vnd.elife.press-package-list+json; version=2'],
            1, 20, true,
            ['cell-biology'])->shouldBeLike($response);
    }
}
