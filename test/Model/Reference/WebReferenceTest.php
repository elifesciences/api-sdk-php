<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\WebReference;
use PHPUnit_Framework_TestCase;

final class WebReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_reference()
    {
        $reference = new WebReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com');

        $this->assertInstanceOf(Reference::class, $reference);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $reference = new WebReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com');

        $this->assertInstanceOf(HasId::class, $reference);
        $this->assertSame('id', $reference->getId());
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new WebReference('id', $date = new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com');

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_may_have_a_discriminator()
    {
        $with = new WebReference('id', new Date(2000), 'a',
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com');
        $withOut = new WebReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com');

        $this->assertSame('a', $with->getDiscriminator());
        $this->assertNull($withOut->getDiscriminator());
    }

    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new WebReference('id', new Date(2000), null,
            $authors = [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com');

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new WebReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'title',
            'http://www.example.com');
        $withOut = new WebReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com');

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $reference = new WebReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com');

        $this->assertSame('title', $reference->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $reference = new WebReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com/');

        $this->assertSame('http://www.example.com/', $reference->getUri());
    }

    /**
     * @test
     */
    public function it_may_have_a_website()
    {
        $with = new WebReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com',
            'website');
        $withOut = new WebReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com');

        $this->assertSame('website', $with->getWebsite());
        $this->assertNull($withOut->getWebsite());
    }

    /**
     * @test
     */
    public function it_may_have_an_accessed_date()
    {
        $with = new WebReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com',
            null, $accessedDate = new Date('2001'));
        $withOut = new WebReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            'http://www.example.com');

        $this->assertEquals($accessedDate, $with->getAccessed());
        $this->assertNull($withOut->getWebsite());
    }
}
