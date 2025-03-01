<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ConferenceProceedingReference;
use eLife\ApiSdk\Model\Reference\StringReferencePage;
use PHPUnit\Framework\TestCase;

final class ConferenceProceedingReferenceTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_reference()
    {
        $reference = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']));

        $this->assertInstanceOf(Reference::class, $reference);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $reference = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']));

        $this->assertInstanceOf(HasId::class, $reference);
        $this->assertSame('id', $reference->getId());
    }

    /**
     * @test
     */
    public function it_has_date()
    {
        $reference = new ConferenceProceedingReference('id', $date = new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']));

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_may_have_a_discriminator()
    {
        $with = new ConferenceProceedingReference('id', new Date(2000), 'a',
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']));
        $withOut = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']));

        $this->assertSame('a', $with->getDiscriminator());
        $this->assertNull($withOut->getDiscriminator());
    }

    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new ConferenceProceedingReference('id', new Date(2000), null,
            $authors = [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']));

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, 'title',
            new Place(['conference']));
        $withOut = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']));

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_an_article_title()
    {
        $reference = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']));

        $this->assertSame('title', $reference->getArticleTitle());
    }

    /**
     * @test
     */
    public function it_has_a_conference()
    {
        $reference = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            $conference = new Place(['conference']));

        $this->assertEquals($conference, $reference->getConference());
    }

    /**
     * @test
     */
    public function it_may_have_pages()
    {
        $with = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']), $pages = new StringReferencePage('foo'));
        $withOut = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']));

        $this->assertEquals($pages, $with->getPages());
        $this->assertNull($withOut->getPages());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']), null, '10.1000/182');
        $withOut = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']));

        $this->assertInstanceOf(HasDoi::class, $with);
        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_a_uri()
    {
        $with = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']), null, null, 'http://www.example.com/');
        $withOut = new ConferenceProceedingReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, 'title',
            new Place(['conference']));

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
