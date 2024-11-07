<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Article;
use eLife\ApiSdk\Model\ExternalArticle;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class ExternalArticleTest extends TestCase
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

        $this->assertSame('foo', $article->getTitle());
    }

    /**
     * @test
     */
    public function it_has_an_article_type()
    {
        /** @var ExternalArticle $article */
        $article = Builder::for(ExternalArticle::class)
            ->__invoke();

        $this->assertSame('external-article', $article->getType());
    }

    /**
     * @test
     */
    public function it_has_an_article_id()
    {
        /** @var ExternalArticle $article */
        $article = Builder::for(ExternalArticle::class)
            ->withUri('http://www.example.com/#1234')
            ->__invoke();

        $this->assertSame('external-64d4ba4ded4ab0e4c9e67550ff9520735adb2bfe', $article->getId());
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
            ->withJournal('foo')
            ->__invoke();

        $this->assertSame('foo', $article->getJournal());
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
