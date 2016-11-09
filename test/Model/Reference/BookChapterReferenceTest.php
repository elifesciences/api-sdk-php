<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\BookChapterReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use eLife\ApiSdk\Model\Reference\StringReferencePage;
use PHPUnit_Framework_TestCase;

final class BookChapterReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_reference()
    {
        $reference = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertInstanceOf(Reference::class, $reference);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $reference = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertSame('id', $reference->getId());
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new BookChapterReference('id', $date = new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new BookChapterReference('id', new ReferenceDate(2000),
            $authors = [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], true,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));
        $withOut = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_editors()
    {
        $reference = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            $editors = [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false,
            'chapter title', 'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertEquals($editors, $reference->getEditors());
    }

    /**
     * @test
     */
    public function it_may_have_further_editors()
    {
        $with = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], true, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));
        $withOut = new BookChapterReference('id', new ReferenceDate(2000), [
            new PersonAuthor(new PersonDetails('author preferred name', 'author index name')),
        ], false, [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false,
            'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertTrue($with->editorsEtAl());
        $this->assertFalse($withOut->editorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_a_chapter_title()
    {
        $reference = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertSame('chapter title', $reference->getChapterTitle());
    }

    /**
     * @test
     */
    public function it_has_a_book_title()
    {
        $reference = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertSame('book title', $reference->getBookTitle());
    }

    /**
     * @test
     */
    public function it_has_a_publisher()
    {
        $reference = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', $publisher = new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertEquals($publisher, $reference->getPublisher());
    }

    /**
     * @test
     */
    public function it_has_pages()
    {
        $reference = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), $pages = new StringReferencePage('foo'));

        $this->assertEquals($pages, $reference->getPages());
    }

    /**
     * @test
     */
    public function it_may_have_a_volume()
    {
        $with = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'), 'volume');
        $withOut = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertSame('volume', $with->getVolume());
        $this->assertNull($withOut->getVolume());
    }

    /**
     * @test
     */
    public function it_may_have_an_edition()
    {
        $with = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'), null, 'edition');
        $withOut = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertSame('edition', $with->getEdition());
        $this->assertNull($withOut->getEdition());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'), null, null,
            '10.1000/182');
        $withOut = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_a_pmid()
    {
        $with = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'), null, null, null,
            18183754);
        $withOut = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertSame(18183754, $with->getPmid());
        $this->assertNull($withOut->getPmid());
    }

    /**
     * @test
     */
    public function it_may_have_an_isbn()
    {
        $with = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'), null, null, null, null,
            '978-3-16-148410-0');
        $withOut = new BookChapterReference('id', new ReferenceDate(2000),
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(null, null, ['publisher']), new StringReferencePage('foo'));

        $this->assertSame('978-3-16-148410-0', $with->getIsbn());
        $this->assertNull($withOut->getIsbn());
    }
}
