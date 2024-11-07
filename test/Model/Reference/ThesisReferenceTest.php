<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ThesisReference;
use PHPUnit\Framework\TestCase;

final class ThesisReferenceTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_reference()
    {
        $reference = new ThesisReference('id', new Date(2000), null, new PersonDetails('preferred name', 'index name'),
            'title',
            new Place(['publisher']));

        $this->assertInstanceOf(Reference::class, $reference);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $reference = new ThesisReference('id', new Date(2000), null,
            new PersonDetails('preferred name', 'index name'),
            'title', new Place(['publisher']));

        $this->assertInstanceOf(HasId::class, $reference);
        $this->assertSame('id', $reference->getId());
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new ThesisReference('id', $date = new Date(2000), null,
            new PersonDetails('preferred name', 'index name'),
            'title', new Place(['publisher']));

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_may_have_a_discriminator()
    {
        $with = new ThesisReference('id', new Date(2000), 'a',
            new PersonDetails('preferred name', 'index name'),
            'title', new Place(['publisher']));
        $withOut = new ThesisReference('id', new Date(2000), null,
            new PersonDetails('preferred name', 'index name'),
            'title', new Place(['publisher']));

        $this->assertSame('a', $with->getDiscriminator());
        $this->assertNull($withOut->getDiscriminator());
    }

    /**
     * @test
     */
    public function it_has_an_author()
    {
        $reference = new ThesisReference('id', new Date(2000), null,
            $author = new PersonDetails('preferred name', 'index name'),
            'title', new Place(['publisher']));

        $this->assertEquals($author, $reference->getAuthor());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $reference = new ThesisReference('id', new Date(2000), null, new PersonDetails('preferred name', 'index name'),
            'title',
            new Place(['publisher']));

        $this->assertSame('title', $reference->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_publisher()
    {
        $reference = new ThesisReference('id', new Date(2000), null, new PersonDetails('preferred name', 'index name'),
            'title',
            $publisher = new Place(['publisher']));

        $this->assertEquals($publisher, $reference->getPublisher());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new ThesisReference('id', new Date(2000), null, new PersonDetails('preferred name', 'index name'), 'title',
            new Place(['publisher']), '10.1000/182');
        $withOut = new ThesisReference('id', new Date(2000), null, new PersonDetails('preferred name', 'index name'),
            'title',
            new Place(['publisher']));

        $this->assertInstanceOf(HasDoi::class, $with);
        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_a_uri()
    {
        $with = new ThesisReference('id', new Date(2000), null, new PersonDetails('preferred name', 'index name'), 'title',
            new Place(['publisher']), null, 'http://www.example.com/');
        $withOut = new ThesisReference('id', new Date(2000), null, new PersonDetails('preferred name', 'index name'),
            'title',
            new Place(['publisher']));

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
