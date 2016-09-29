<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reference\SoftwareReference;
use PHPUnit_Framework_TestCase;

final class SoftwareReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_authors()
    {
        $reference = new SoftwareReference($authors = [new PersonAuthor(new Person('preferred name', 'index name'))],
            false, 'title', new Place(null, null, ['publisher']));

        $this->assertEquals($authors, $reference->getAuthors());
    }

    /**
     * @test
     */
    public function it_may_have_further_authors()
    {
        $with = new SoftwareReference([new PersonAuthor(new Person('preferred name', 'index name'))], true,
            'title', new Place(null, null, ['publisher']));
        $withOut = new SoftwareReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'title', new Place(null, null, ['publisher']));

        $this->assertTrue($with->authorsEtAl());
        $this->assertFalse($withOut->authorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $reference = new SoftwareReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'title', new Place(null, null, ['publisher']));

        $this->assertSame('title', $reference->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_publisher()
    {
        $reference = new SoftwareReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'title', $software = new Place(null, null, ['publisher']));

        $this->assertEquals($software, $reference->getPublisher());
    }

    /**
     * @test
     */
    public function it_may_have_a_version()
    {
        $with = new SoftwareReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'title', new Place(null, null, ['publisher']), '1.0');
        $withOut = new SoftwareReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'title', new Place(null, null, ['publisher']));

        $this->assertSame('1.0', $with->getVersion());
        $this->assertNull($withOut->getVersion());
    }

    /**
     * @test
     */
    public function it_may_have_a_uri()
    {
        $with = new SoftwareReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'title', new Place(null, null, ['publisher']), null, 'http://www.example.com/');
        $withOut = new SoftwareReference([new PersonAuthor(new Person('preferred name', 'index name'))], false,
            'title', new Place(null, null, ['publisher']));

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
