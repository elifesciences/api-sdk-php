<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Reference\SoftwareReference;
use PHPUnit_Framework_TestCase;

final class SoftwareReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_reference()
    {
        $reference = new SoftwareReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertInstanceOf(Reference::class, $reference);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $reference = new SoftwareReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame('id', $reference->getId());
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new SoftwareReference('id', $date = new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_may_have_a_discriminator()
    {
        $with = new SoftwareReference('id', new ReferenceDate(2000), 'a',
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));
        $withOut = new SoftwareReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame('a', $with->getDiscriminator());
        $this->assertNull($withOut->getDiscriminator());
    }

    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new SoftwareReference('id', new ReferenceDate(2000), null,
            $authors = [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new SoftwareReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'title',
            new Place(null, null, ['publisher']));
        $withOut = new SoftwareReference('id', new ReferenceDate(2000), null,
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
        $reference = new SoftwareReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame('title', $reference->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_publisher()
    {
        $reference = new SoftwareReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            $software = new Place(null, null, ['publisher']));

        $this->assertEquals($software, $reference->getPublisher());
    }

    /**
     * @test
     */
    public function it_may_have_a_version()
    {
        $with = new SoftwareReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']), '1.0');
        $withOut = new SoftwareReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame('1.0', $with->getVersion());
        $this->assertNull($withOut->getVersion());
    }

    /**
     * @test
     */
    public function it_may_have_a_uri()
    {
        $with = new SoftwareReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']), null, 'http://www.example.com/');
        $withOut = new SoftwareReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(null, null, ['publisher']));

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
