<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Reference\PreprintReference;
use PHPUnit_Framework_TestCase;

final class PreprintReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new PreprintReference($authors = [new PersonAuthor(new Person('preferred name', 'index name'))],
            false, 'article title', 'source');

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new PreprintReference([new PersonAuthor(new Person('preferred name', 'index name'))], true,
            'article title', 'source');
        $withOut = new PreprintReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'article title', 'source');

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_an_article_title()
    {
        $reference = new PreprintReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'article title', 'source');

        $this->assertSame('article title', $reference->getArticleTitle());
    }

    /**
     * @test
     */
    public function it_has_a_source()
    {
        $reference = new PreprintReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'article title', 'source');

        $this->assertSame('source', $reference->getSource());
    }

    /**
     * @test
     */
    public function it_may_have_a_doi()
    {
        $with = new PreprintReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'article title', 'source', '10.1000/182');
        $withOut = new PreprintReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'article title', 'source');

        $this->assertSame('10.1000/182', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_a_uri()
    {
        $with = new PreprintReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'article title', 'source', null, 'http://www.example.com/');
        $withOut = new PreprintReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'article title', 'source');

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
