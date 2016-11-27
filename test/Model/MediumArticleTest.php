<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\MediumArticle;
use eLife\ApiSdk\Model\Model;
use PHPUnit_Framework_TestCase;

final class MediumArticleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $mediumArticle = new MediumArticle('http://www.example.com/', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null);

        $this->assertInstanceOf(Model::class, $mediumArticle);
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $mediumArticle = new MediumArticle('http://www.example.com/', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null);

        $this->assertSame('http://www.example.com/', $mediumArticle->getUri());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $mediumArticle = new MediumArticle('http://www.example.com/', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null);

        $this->assertSame('title', $mediumArticle->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new MediumArticle('http://www.example.com/', 'title', 'impact statement', new DateTimeImmutable('now', new DateTimeZone('Z')),
            null);
        $withOut = new MediumArticle('http://www.example.com/', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null);

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $mediumArticle = new MediumArticle('http://www.example.com/', 'title', null, $date = new DateTimeImmutable('now', new DateTimeZone('Z')),
            null);

        $this->assertEquals($date, $mediumArticle->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_image()
    {
        $image = new Image('', [900 => 'https://placehold.it/900x450']);
        $with = new MediumArticle('http://www.example.com/', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), $image);
        $withOut = new MediumArticle('http://www.example.com/', 'title', null, new DateTimeImmutable('now', new DateTimeZone('Z')), null);

        $this->assertEquals($image, $with->getImage());
        $this->assertNull($withOut->getImage());
    }
}
