<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\BookChapterReference;
use eLife\ApiSdk\Model\Reference\StringReferencePage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BookChapterReferenceTest extends TestCase
{
    #[Test]
    public function it_is_a_reference()
    {
        $reference = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertInstanceOf(Reference::class, $reference);
    }

    #[Test]
    public function it_has_an_id()
    {
        $reference = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertInstanceOf(HasId::class, $reference);
        $this->assertSame('id', $reference->getId());
    }

    #[Test]
    public function it_has_a_date()
    {
        $reference = new BookChapterReference('id', $date = new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertEquals($date, $reference->getDate());
    }

    #[Test]
    public function it_may_have_a_discriminator()
    {
        $with = new BookChapterReference('id', new Date(2000), 'a',
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));
        $withOut = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertSame('a', $with->getDiscriminator());
        $this->assertNull($withOut->getDiscriminator());
    }

    #[Test]
    public function it_has_authors()
    {
        $reference = new BookChapterReference('id', new Date(2000), null,
            $authors = [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertEquals($authors, $reference->getAuthors());
    }

    #[Test]
    public function it_may_have_further_authors()
    {
        $with = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], true,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));
        $withOut = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    #[Test]
    public function it_has_editors()
    {
        $reference = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            $editors = [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false,
            'chapter title', 'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertEquals($editors, $reference->getEditors());
    }

    #[Test]
    public function it_may_have_further_editors()
    {
        $with = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], true, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));
        $withOut = new BookChapterReference('id', new Date(2000), null, [
            new PersonAuthor(new PersonDetails('author preferred name', 'author index name')),
        ], false, [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false,
            'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertTrue($with->editorsEtAl());
        $this->assertFalse($withOut->editorsEtAl());
    }

    #[Test]
    public function it_has_a_chapter_title()
    {
        $reference = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertSame('chapter title', $reference->getChapterTitle());
    }

    #[Test]
    public function it_has_a_book_title()
    {
        $reference = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertSame('book title', $reference->getBookTitle());
    }

    #[Test]
    public function it_has_a_publisher()
    {
        $reference = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', $publisher = new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertEquals($publisher, $reference->getPublisher());
    }

    #[Test]
    public function it_has_pages()
    {
        $reference = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), $pages = new StringReferencePage('foo'));

        $this->assertEquals($pages, $reference->getPages());
    }

    #[Test]
    public function it_may_have_a_volume()
    {
        $with = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'), 'volume');
        $withOut = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertSame('volume', $with->getVolume());
        $this->assertNull($withOut->getVolume());
    }

    #[Test]
    public function it_may_have_an_edition()
    {
        $with = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'), null, 'edition');
        $withOut = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertSame('edition', $with->getEdition());
        $this->assertNull($withOut->getEdition());
    }

    #[Test]
    public function it_may_have_a_doi()
    {
        $with = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'), null, null,
            '10.1000/182');
        $withOut = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertInstanceOf(HasDoi::class, $with);
        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    #[Test]
    public function it_may_have_a_pmid()
    {
        $with = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'), null, null, null,
            18183754);
        $withOut = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertSame(18183754, $with->getPmid());
        $this->assertNull($withOut->getPmid());
    }

    #[Test]
    public function it_may_have_an_isbn()
    {
        $with = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'), null, null, null, null,
            '978-3-16-148410-0');
        $withOut = new BookChapterReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('author preferred name', 'author index name'))], false,
            [new PersonAuthor(new PersonDetails('editor preferred name', 'editor index name'))], false, 'chapter title',
            'book title', new Place(['publisher']), new StringReferencePage('foo'));

        $this->assertSame('978-3-16-148410-0', $with->getIsbn());
        $this->assertNull($withOut->getIsbn());
    }
}
