<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Reference\ReportReference;
use PHPUnit_Framework_TestCase;

final class ReportReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_reference()
    {
        $reference = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertInstanceOf(Reference::class, $reference);
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new ReportReference($date = new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new ReportReference(new ReferenceDate(2000),
            $authors = [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'title',
            new Place(null, null, ['publisher']));
        $withOut = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $reference = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame('title', $reference->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_publisher()
    {
        $reference = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            $report = new Place(null, null, ['publisher']));

        $this->assertEquals($report, $reference->getPublisher());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']), '10.1000/182');
        $withOut = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_a_pmid()
    {
        $with = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']), null, 18183754);
        $withOut = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame(18183754, $with->getPmid());
        $this->assertNull($withOut->getPmid());
    }

    /**
     * @test
     */
    public function it_may_have_an_isbn()
    {
        $with = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']), null, null, '978-3-16-148410-0');
        $withOut = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame('978-3-16-148410-0', $with->getIsbn());
        $this->assertNull($withOut->getIsbn());
    }

    /**
     * @test
     */
    public function it_may_have_a_uri()
    {
        $with = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']), null, null, null, 'http://www.example.com/');
        $withOut = new ReportReference(new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
