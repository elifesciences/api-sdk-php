<?php

namespace spec\eLife\ApiSdk\ApiClient;

use eLife\ApiClient\HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result\ArrayResult;
use eLife\ApiClient\Version;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Request;
use PhpSpec\ObjectBehavior;

final class ArticlesClientSpec extends ObjectBehavior
{
    private $httpClient;

    public function let(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->beConstructedWith($httpClient, ['X-Foo' => 'bar']);
    }

    public function it_gets_a_latest_version_for_an_article()
    {
        $request = new Request('GET', 'articles/3',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.article-poa+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.article-poa+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getArticleLatestVersion(['Accept' => 'application/vnd.elife.article-poa+json; version=2'], '3')
            ->shouldBeLike($response)
        ;
    }

    public function it_gets_a_history_for_an_article()
    {
        $request = new Request('GET', 'articles/3/versions',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.article-history+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.article-history+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getArticleHistory(['Accept' => 'application/vnd.elife.article-history+json; version=2'], '3')
            ->shouldBeLike($response)
        ;
    }

    public function it_gets_related_articles_for_an_article()
    {
        $request = new Request('GET', 'articles/3/related',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.article-related+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.article-related+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getRelatedArticles(['Accept' => 'application/vnd.elife.article-related+json; version=2'], '3')
            ->shouldBeLike($response);
    }

    public function it_gets_a_version_for_an_article()
    {
        $request = new Request('GET', 'articles/3/versions/2',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.article-poa+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.article-poa+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->getArticleVersion(['Accept' => 'application/vnd.elife.article-poa+json; version=2'], '3', 2)
            ->shouldBeLike($response)
        ;
    }

    public function it_lists_articles()
    {
        $request = new Request('GET', 'articles?page=1&per-page=20&order=desc&subject[]=cell-biology',
            ['X-Foo' => 'bar', 'Accept' => 'application/vnd.elife.articles-list+json; version=2', 'User-Agent' => 'eLifeApiClient/'.Version::get()]);
        $response = new FulfilledPromise(new ArrayResult(new MediaType('application/vnd.elife.articles-list+json',
            2), ['foo' => ['bar', 'baz']]));

        $this->httpClient->send($request)->willReturn($response);

        $this->listArticles(['Accept' => 'application/vnd.elife.articles-list+json; version=2'],
            1, 20, true, ['cell-biology'])
            ->shouldBeLike($response)
        ;
    }
}
