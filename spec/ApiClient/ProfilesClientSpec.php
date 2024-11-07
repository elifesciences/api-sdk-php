<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class ProfilesClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_a_profile()
    {
        $request = new Request('GET', 'profiles/fbloggs',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.profile+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.profile+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getProfile(['Accept' => 'application/vnd.elife.profile+json; version=2'], 'fbloggs')
            ->shouldBeLike($response);
    }

    public function it_lists_profiles()
    {
        $request = new Request('GET', 'profiles?page=1&per-page=20&order=desc',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.profile-list+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.profile-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listProfiles(['Accept' => 'application/vnd.elife.profile-list+json; version=2'], 1, 20, true)
            ->shouldBeLike($response);
    }
}
