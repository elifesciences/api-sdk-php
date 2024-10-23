<?php

namespace spec\eLife\ApiSdk\ApiClient;

use DateTimeImmutable;
use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class ReviewedPreprintsClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_a_reviewed_preprint()
    {
        $request = new Request('GET', 'reviewed-preprints/3',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.reviewed-preprint+json; version=1', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.reviewed-preprint+json',
            1), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getReviewedPreprint(['Accept' => 'application/vnd.elife.reviewed-preprint+json; version=1'], '3')
            ->shouldBeLike($response)
        ;
    }

    public function it_lists_reviewed_preprints()
    {
        $request = new Request('GET', 'reviewed-preprints?page=1&per-page=20&order=desc&use-date=published&start-date=2017-01-02&end-date=2017-02-03',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.reviewed-preprint-list+json; version=1', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.reviewed-preprint-list+json',
            1), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listReviewedPreprints(['Accept' => 'application/vnd.elife.reviewed-preprint-list+json; version=1'],
            1, 20, true, 'published',
            new DateTimeImmutable('2017-01-02'), new DateTimeImmutable('2017-02-03'))->shouldBeLike($response)
        ;
    }
}
