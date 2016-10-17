<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\PatentReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use PHPUnit_Framework_TestCase;

final class PatentReferenceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_reference()
    {
        $reference = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertInstanceOf(Reference::class, $reference);
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $reference = new PatentReference($date = new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertEquals($date, $reference->getDate());
    }

    /**
     * @test
     */
    public function it_has_inventors()
    {
        $reference = new PatentReference(new ReferenceDate(2000),
            $inventors = [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title',
            'type', 'country');

        $this->assertEquals($inventors, $reference->getInventors());
    }

    /**
     * @test
     */
    public function it_may_have_further_inventors()
    {
        $with = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], true, [], false, 'title', 'type',
            'country');
        $withOut = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertTrue($with->inventorsEtAl());
        $this->assertFalse($withOut->inventorsEtAl());
    }

    /**
     * @test
     */
    public function it_may_have_assignees()
    {
        $with = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('inventor preferred name', 'inventor index name'))], false,
            $assignees = [new PersonAuthor(new Person('assignee preferred name', 'assignee index name'))], false,
            'title', 'type', 'country');
        $withOut = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('inventor preferred name', 'inventor index name'))], false, [], false, 'title',
            'type', 'country');

        $this->assertEquals($assignees, $with->getAssignees());
        $this->assertEmpty($withOut->getAssignees());
    }

    /**
     * @test
     */
    public function it_may_have_further_assignees()
    {
        $with = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], true, [], false, 'title', 'type',
            'country');
        $withOut = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertTrue($with->inventorsEtAl());
        $this->assertFalse($withOut->inventorsEtAl());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $reference = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertSame('title', $reference->getTitle());
    }

    /**
     * @test
     */
    public function it_has_a_type()
    {
        $reference = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertSame('type', $reference->getPatentType());
    }

    /**
     * @test
     */
    public function it_may_have_a_country()
    {
        $reference = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertSame('country', $reference->getCountry());
    }

    /**
     * @test
     */
    public function it_may_have_a_number()
    {
        $with = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country', 'number');
        $withOut = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertSame('number', $with->getNumber());
        $this->assertNull($withOut->getNumber());
    }

    /**
     * @test
     */
    public function it_may_have_a_uri()
    {
        $with = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country', null, 'http://www.example.com/');
        $withOut = new PatentReference(new ReferenceDate(2000),
            [new PersonAuthor(new Person('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
