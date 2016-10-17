<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Reference\ThesisReference;
use PHPUnit_Framework_TestCase;

final class ThesisReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_reference()
    {
        $reference = new ThesisReference(new ReferenceDate(2000), new Person('preferred name', 'index name'), 'title',
            new Place(null, null, ['publisher']));

        $this->assertInstanceOf(Reference::class, $reference);
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new ThesisReference($date = new ReferenceDate(2000), new Person('preferred name', 'index name'),
            'title', new Place(null, null, ['publisher']));

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_has_an_author()
    {
        $reference = new ThesisReference(new ReferenceDate(2000), $author = new Person('preferred name', 'index name'),
            'title', new Place(null, null, ['publisher']));

        $this->assertEquals($author, $reference->getAuthor());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $reference = new ThesisReference(new ReferenceDate(2000), new Person('preferred name', 'index name'), 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame('title', $reference->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_publisher()
    {
        $reference = new ThesisReference(new ReferenceDate(2000), new Person('preferred name', 'index name'), 'title',
            $publisher = new Place(null, null, ['publisher']));

        $this->assertEquals($publisher, $reference->getPublisher());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new ThesisReference(new ReferenceDate(2000), new Person('preferred name', 'index name'), 'title',
            new Place(null, null, ['publisher']), '10.1000/182');
        $withOut = new ThesisReference(new ReferenceDate(2000), new Person('preferred name', 'index name'), 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_a_uri()
    {
        $with = new ThesisReference(new ReferenceDate(2000), new Person('preferred name', 'index name'), 'title',
            new Place(null, null, ['publisher']), null, 'http://www.example.com/');
        $withOut = new ThesisReference(new ReferenceDate(2000), new Person('preferred name', 'index name'), 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
