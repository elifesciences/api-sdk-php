<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\JournalReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Reference\StringReferencePage;
use PHPUnit_Framework_TestCase;

final class JournalReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_reference()
    {
        $reference = new JournalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['journal']), new StringReferencePage('pages'));

        $this->assertInstanceOf(Reference::class, $reference);
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new JournalReference($date = new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['journal']), new StringReferencePage('pages'));

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new JournalReference(new ReferenceDate(2000),
            $authors = [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['journal']), new StringReferencePage('pages'));

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new JournalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], true, 'article title',
            new Place(null, null, ['journal']), new StringReferencePage('pages'));
        $withOut = new JournalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['journal']), new StringReferencePage('pages'));

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_an_article_title()
    {
        $reference = new JournalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['journal']), new StringReferencePage('pages'));

        $this->assertSame('article title', $reference->getArticleTitle());
    }

    /**
     * @test
     */
    public function it_has_a_journal()
    {
        $reference = new JournalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            $journal = new Place(null, null, ['journal']), new StringReferencePage('pages'));

        $this->assertEquals($journal, $reference->getJournal());
    }

    /**
     * @test
     */
    public function it_has_pages()
    {
        $reference = new JournalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['journal']), $pages = new StringReferencePage('pages'));

        $this->assertEquals($pages, $reference->getPages());
    }

    /**
     * @test
     */
    public function it_may_have_a_volume()
    {
        $with = new JournalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['journal']), new StringReferencePage('pages'), 'volume');
        $withOut = new JournalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['journal']), new StringReferencePage('pages'));

        $this->assertSame('volume', $with->getVolume());
        $this->assertNull($withOut->getVolume());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new JournalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['journal']), new StringReferencePage('pages'), null, '10.1000/182');
        $withOut = new JournalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['journal']), new StringReferencePage('pages'));

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_a_pmid()
    {
        $with = new JournalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['journal']), new StringReferencePage('pages'), null, null, 18183754);
        $withOut = new JournalReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, 'article title',
            new Place(null, null, ['journal']), new StringReferencePage('pages'));

        $this->assertSame(18183754, $with->getPmid());
        $this->assertNull($withOut->getPmid());
    }
}
