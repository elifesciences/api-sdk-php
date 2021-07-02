<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticlePreprint;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasSocialImage;
use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class ArticlePreprintTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $preprint = new ArticlePreprint('description', 'http://www.example.com/', new DateTimeImmutable('now', new DateTimeZone('Z')));

        $this->assertInstanceOf(Model::class, $preprint);
    }

    /**
     * @test
     */
    public function it_has_a_description()
    {
        $preprint = new ArticlePreprint('description', 'http://www.example.com/', new DateTimeImmutable('now', new DateTimeZone('Z')));

        $this->assertSame('description', $preprint->getDescription());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $preprint = new ArticlePreprint('description', 'http://www.example.com/', new DateTimeImmutable('now', new DateTimeZone('Z')));

        $this->assertSame('http://www.example.com/', $preprint->getUri());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $preprint = new ArticlePreprint('description', 'http://www.example.com/', $date = new DateTimeImmutable('now', new DateTimeZone('Z')));

        $this->assertInstanceOf(HasPublishedDate::class, $preprint);
        $this->assertSame($date, $preprint->getPublishedDate());
    }
}
