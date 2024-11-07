<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class BlogClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_an_article()
    {
        $request = new Request('GET', 'blog-articles/3',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.blog-article+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.blog-article+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getArticle(['Accept' => 'application/vnd.elife.blog-article+json; version=2'], '3')
            ->shouldBeLike($response)
        ;
    }

    public function it_lists_articles()
    {
        $request = new Request('GET', 'blog-articles?page=1&per-page=20&order=desc&subject[]=cell-biology',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.blog-article-list+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.blog-article-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listArticles(['Accept' => 'application/vnd.elife.blog-article-list+json; version=2'],
            1, 20, true,
            ['cell-biology'])->shouldBeLike($response)
        ;
    }
}
