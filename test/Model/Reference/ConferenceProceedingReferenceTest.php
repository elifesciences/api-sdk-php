<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference\ConferenceProceedingReference;
use PHPUnit_Framework_TestCase;

final class ConferenceProceedingReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new ConferenceProceedingReference($authors = [
            new PersonAuthor(new Person('preferred name', 'index name')),
        ], false, 'title', new Place(null, null, ['conference']));

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new ConferenceProceedingReference([new PersonAuthor(new Person('preferred name', 'index name'))], true,
            'title', new Place(null, null, ['conference']));
        $withOut = new ConferenceProceedingReference([new PersonAuthor(new Person('preferred name', 'index name'))],
            false, 'title', new Place(null, null, ['conference']));

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_an_article_title()
    {
        $reference = new ConferenceProceedingReference([new PersonAuthor(new Person('preferred name', 'index name'))],
            false, 'title', new Place(null, null, ['conference']));

        $this->assertSame('title', $reference->getArticleTitle());
    }

    /**
     * @test
     */
    public function it_has_a_conference()
    {
        $reference = new ConferenceProceedingReference([new PersonAuthor(new Person('preferred name', 'index name'))],
            false, 'title', $conference = new Place(null, null, ['conference']));

        $this->assertEquals($conference, $reference->getConference());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new ConferenceProceedingReference([new PersonAuthor(new Person('preferred name', 'index name'))],
            false, 'title', new Place(null, null, ['conference']), '10.1000/182');
        $withOut = new ConferenceProceedingReference([new PersonAuthor(new Person('preferred name', 'index name'))],
            false, 'title', new Place(null, null, ['conference']));

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_a_uri()
    {
        $with = new ConferenceProceedingReference([new PersonAuthor(new Person('preferred name', 'index name'))],
            false, 'title', new Place(null, null, ['conference']), null, 'http://www.example.com/');
        $withOut = new ConferenceProceedingReference([new PersonAuthor(new Person('preferred name', 'index name'))],
            false, 'title', new Place(null, null, ['conference']));

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
