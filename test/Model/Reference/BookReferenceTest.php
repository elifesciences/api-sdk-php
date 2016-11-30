<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\BookReference;
use PHPUnit_Framework_TestCase;

final class BookReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_reference()
    {
        $reference = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));

        $this->assertInstanceOf(Reference::class, $reference);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $reference = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));

        $this->assertInstanceOf(HasId::class, $reference);
        $this->assertSame('id', $reference->getId());
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new BookReference('id', $date = new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_may_have_a_discriminator()
    {
        $with = new BookReference('id', new Date(2000), 'a',
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));
        $withOut = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));

        $this->assertSame('a', $with->getDiscriminator());
        $this->assertNull($withOut->getDiscriminator());
    }

    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new BookReference('id', new Date(2000), null,
            $authors = [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'book title',
            new Place(null, null, ['publisher']));
        $withOut = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_a_book_title()
    {
        $reference = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));

        $this->assertSame('book title', $reference->getBookTitle());
    }

    /**
     * @test
     */
    public function it_has_a_publisher()
    {
        $reference = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            $publisher = new Place(null, null, ['publisher']));

        $this->assertEquals($publisher, $reference->getPublisher());
    }

    /**
     * @test
     */
    public function it_may_have_a_volume()
    {
        $with = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']), 'volume');
        $withOut = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));

        $this->assertSame('volume', $with->getVolume());
        $this->assertNull($withOut->getVolume());
    }

    /**
     * @test
     */
    public function it_may_have_an_edition()
    {
        $with = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']), null, 'edition');
        $withOut = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));

        $this->assertSame('edition', $with->getEdition());
        $this->assertNull($withOut->getEdition());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']), null, null, '10.1000/182');
        $withOut = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));

        $this->assertInstanceOf(HasDoi::class, $with);
        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_a_pmid()
    {
        $with = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']), null, null, null, 18183754);
        $withOut = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));

        $this->assertSame(18183754, $with->getPmid());
        $this->assertNull($withOut->getPmid());
    }

    /**
     * @test
     */
    public function it_may_have_an_isbn()
    {
        $with = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']), null, null, null, null, '978-3-16-148410-0');
        $withOut = new BookReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'book title',
            new Place(null, null, ['publisher']));

        $this->assertSame('978-3-16-148410-0', $with->getIsbn());
        $this->assertNull($withOut->getIsbn());
    }
}
