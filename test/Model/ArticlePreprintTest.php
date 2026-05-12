<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Model\ArticlePreprint;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\Model;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ArticlePreprintTest extends TestCase
{
    #[Test]
    public function it_is_a_model()
    {
        $preprint = new ArticlePreprint('description', 'http://www.example.com/', new DateTimeImmutable('now', new DateTimeZone('Z')));

        $this->assertInstanceOf(Model::class, $preprint);
    }

    #[Test]
    public function it_has_a_description()
    {
        $preprint = new ArticlePreprint('description', 'http://www.example.com/', new DateTimeImmutable('now', new DateTimeZone('Z')));

        $this->assertSame('description', $preprint->getDescription());
    }

    #[Test]
    public function it_has_a_uri()
    {
        $preprint = new ArticlePreprint('description', 'http://www.example.com/', new DateTimeImmutable('now', new DateTimeZone('Z')));

        $this->assertSame('http://www.example.com/', $preprint->getUri());
    }

    #[Test]
    public function it_has_a_published_date()
    {
        $preprint = new ArticlePreprint('description', 'http://www.example.com/', $date = new DateTimeImmutable('now', new DateTimeZone('Z')));

        $this->assertInstanceOf(HasPublishedDate::class, $preprint);
        $this->assertSame($date, $preprint->getPublishedDate());
    }
}
