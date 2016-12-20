<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Article;
use eLife\ApiSdk\Model\ExternalArticle;
use eLife\ApiSdk\Model\Place;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class ExternalArticleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_an_article()
    {
        $article = Builder::for(ExternalArticle::class)
            ->__invoke();

        $this->assertInstanceOf(Article::class, $article);
    }

    /**
     * @test
     */
    public function it_has_an_article_title()
    {
        $article = Builder::for(ExternalArticle::class)
            ->withArticleTitle('foo')
            ->__invoke();

        $this->assertSame('foo', $article->getArticleTitle());
    }

    /**
     * @test
     */
    public function it_has_an_author_line()
    {
        $article = Builder::for(ExternalArticle::class)
            ->withAuthorLine('foo')
            ->__invoke();

        $this->assertSame('foo', $article->getAuthorLine());
    }

    /**
     * @test
     */
    public function it_has_a_journal()
    {
        $article = Builder::for(ExternalArticle::class)
            ->withJournal($journal = new Place(null, null, ['foo']))
            ->__invoke();

        $this->assertEquals($journal, $article->getJournal());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $article = Builder::for(ExternalArticle::class)
            ->withUri('http://www.example.com/')
            ->__invoke();

        $this->assertSame('http://www.example.com/', $article->getUri());
    }
}
