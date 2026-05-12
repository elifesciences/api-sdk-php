<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\PatentReference;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PatentReferenceTest extends TestCase
{
    #[Test]
    public function it_is_a_reference()
    {
        $reference = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertInstanceOf(Reference::class, $reference);
    }

    #[Test]
    public function it_has_an_id()
    {
        $reference = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertInstanceOf(HasId::class, $reference);
        $this->assertSame('id', $reference->getId());
    }

    #[Test]
    public function it_has_a_date()
    {
        $reference = new PatentReference('id', $date = new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertEquals($date, $reference->getDate());
    }

    #[Test]
    public function it_may_have_a_discriminator()
    {
        $with = new PatentReference('id', new Date(2000), 'a',
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');
        $withOut = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertSame('a', $with->getDiscriminator());
        $this->assertNull($withOut->getDiscriminator());
    }

    #[Test]
    public function it_has_inventors()
    {
        $reference = new PatentReference('id', new Date(2000), null,
            $inventors = [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false,
            'title',
            'type', 'country');

        $this->assertEquals($inventors, $reference->getInventors());
    }

    #[Test]
    public function it_may_have_further_inventors()
    {
        $with = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, [], false, 'title', 'type',
            'country');
        $withOut = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertTrue($with->inventorsEtAl());
        $this->assertFalse($withOut->inventorsEtAl());
    }

    #[Test]
    public function it_may_have_assignees()
    {
        $with = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('inventor preferred name', 'inventor index name'))], false,
            $assignees = [new PersonAuthor(new PersonDetails('assignee preferred name', 'assignee index name'))], false,
            'title', 'type', 'country');
        $withOut = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('inventor preferred name', 'inventor index name'))], false, [], false,
            'title',
            'type', 'country');

        $this->assertEquals($assignees, $with->getAssignees());
        $this->assertEmpty($withOut->getAssignees());
    }

    #[Test]
    public function it_may_have_further_assignees()
    {
        $with = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], true, [], false, 'title', 'type',
            'country');
        $withOut = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertTrue($with->inventorsEtAl());
        $this->assertFalse($withOut->inventorsEtAl());
    }

    #[Test]
    public function it_has_a_title()
    {
        $reference = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertSame('title', $reference->getTitle());
    }

    #[Test]
    public function it_has_a_type()
    {
        $reference = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertSame('type', $reference->getPatentType());
    }

    #[Test]
    public function it_may_have_a_country()
    {
        $reference = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertSame('country', $reference->getCountry());
    }

    #[Test]
    public function it_may_have_a_number()
    {
        $with = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country', 'number');
        $withOut = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertSame('number', $with->getNumber());
        $this->assertNull($withOut->getNumber());
    }

    #[Test]
    public function it_may_have_a_uri()
    {
        $with = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country', null, 'http://www.example.com/');
        $withOut = new PatentReference('id', new Date(2000), null,
            [new PersonAuthor(new PersonDetails('preferred name', 'index name'))], false, [], false, 'title', 'type',
            'country');

        $this->assertSame('http://www.example.com/', $with->getUri());
        $this->assertNull($withOut->getUri());
    }
}
