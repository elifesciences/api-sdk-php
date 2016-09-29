<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Reference\ClinicalTrialReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use PHPUnit_Framework_TestCase;

final class ClinicalTrialReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new ClinicalTrialReference($date = new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new ClinicalTrialReference(new ReferenceDate(2000),
            $authors = [new PersonAuthor(new Person('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new ClinicalTrialReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], true,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');
        $withOut = new ClinicalTrialReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function authors_have_a_type()
    {
        $reference = new ClinicalTrialReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertSame(ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, $reference->getAuthorsType());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $reference = new ClinicalTrialReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertSame('title', $reference->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $reference = new ClinicalTrialReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false,
            ClinicalTrialReference::AUTHOR_TYPE_AUTHORS, 'title', 'http://www.example.com/');

        $this->assertSame('http://www.example.com/', $reference->getUri());
    }
}
