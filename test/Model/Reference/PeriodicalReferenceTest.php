<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference\PeriodicalReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use PHPUnit_Framework_TestCase;

final class PeriodicalReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new PeriodicalReference($date = new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['periodical']));

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new PeriodicalReference(new ReferenceDate(2000),
            $authors = [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['periodical']));

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new PeriodicalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], true, 'article title',
            new Place(null, null, ['periodical']));
        $withOut = new PeriodicalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['periodical']));

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_an_article_title()
    {
        $reference = new PeriodicalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['periodical']));

        $this->assertSame('article title', $reference->getArticleTitle());
    }

    /**
     * @test
     */
    public function it_has_a_periodical()
    {
        $reference = new PeriodicalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            $periodical = new Place(null, null, ['periodical']));

        $this->assertEquals($periodical, $reference->getPeriodical());
    }

    /**
     * @test
     */
    public function it_may_have_a_volume()
    {
        $with = new PeriodicalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['periodical']), 'volume');
        $withOut = new PeriodicalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['periodical']));

        $this->assertSame('volume', $with->getVolume());
        $this->assertNull($withOut->getVolume());
    }

    /**
     * @test
     */
    public function it_may_have_a_uri()
    {
        $with = new PeriodicalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['periodical']), null, 'http://www.example.com/');
        $withOut = new PeriodicalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['periodical']));

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
