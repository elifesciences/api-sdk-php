<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ClinicalTrialReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use PHPUnit_Framework_TestCase;

final class ClinicalTrialReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_reference()
    {
        $reference = new ClinicalTrialReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertInstanceOf(Reference::class, $reference);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $reference = new ClinicalTrialReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertSame('id', $reference->getId());
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new ClinicalTrialReference('id', $date = new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_may_have_a_discriminator()
    {
        $with = new ClinicalTrialReference('id', new ReferenceDate(2000), 'a',
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');
        $withOut = new ClinicalTrialReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertSame('a', $with->getDiscriminator());
        $this->assertNull($withOut->getDiscriminator());
    }

    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new ClinicalTrialReference('id', new ReferenceDate(2000), null,
            $authors = [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new ClinicalTrialReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');
        $withOut = new ClinicalTrialReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function authors_have_a_type()
    {
        $reference = new ClinicalTrialReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertSame(ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, $reference->getAuthorsType());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $reference = new ClinicalTrialReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertSame('title', $reference->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $reference = new ClinicalTrialReference('id', new ReferenceDate(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertSame('http://www.example.com/', $reference->getUri());
    }
}
