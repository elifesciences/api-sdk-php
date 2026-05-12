<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\PersonResearch;
use eLife\ApiSdk\Model\Place;
use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Promise\Create;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class PersonTest extends TestCase
{
    #[Test]
    public function it_is_a_model()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertInstanceOf(Model::class, $person);
    }

    #[Test]
    public function it_has_an_identifier()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertInstanceOf(HasIdentifier::class, $person);
        $this->assertEquals(Identifier::person('id'), $person->getIdentifier());
    }

    #[Test]
    public function it_has_an_id()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertInstanceOf(HasId::class, $person);
        $this->assertSame('id', $person->getId());
    }

    #[Test]
    public function it_has_details()
    {
        $person = new Person('id', $details = new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertEquals($details, $person->getDetails());
    }

    #[Test]
    public function it_may_have_given_names()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::promiseFor('surname'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::promiseFor(null), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertEquals('surname', $with->getSurname());
        $this->assertNull($withOut->getSurname());
    }

    #[Test]
    public function it_may_have_a_surname()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), Create::promiseFor('given names'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), Create::promiseFor(null),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertEquals('given names', $with->getGivenNames());
        $this->assertNull($withOut->getGivenNames());
    }

    #[Test]
    public function it_has_a_type()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertSame('senior-editor', $person->getType());
    }

    #[Test]
    public function it_has_a_type_label()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertSame('label', $person->getTypeLabel());
    }

    #[Test]
    public function it_may_have_a_thumbnail()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label',
            $image = Builder::for(Image::class)->sample('thumbnail'),
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertInstanceOf(HasThumbnail::class, $with);
        $this->assertEquals($image, $with->getThumbnail());
        $this->assertNull($withOut->getThumbnail());
    }

    #[Test]
    public function it_may_have_affiliations()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            $affiliations = new ArraySequence([new Place(['affiliation'])]), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new EmptySequence(), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertEquals($affiliations, $with->getAffiliations());
        $this->assertCount(0, $withOut->getAffiliations());
    }

    #[Test]
    public function it_may_have_research()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')),
            Create::promiseFor($research = new PersonResearch(new EmptySequence(), ['focus'], [])),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')),
            Create::promiseFor(null), new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertEquals($research, $with->getResearch());
        $this->assertNull($withOut->getResearch());
    }

    #[Test]
    public function it_may_have_a_profile()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'), $profile = new ArraySequence([new Paragraph('profile')]),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'), new EmptySequence(),
            Create::rejectionFor('Competing interests should not be unwrapped'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertEquals($profile, $with->getProfile());
        $this->assertCount(0, $withOut->getProfile());
    }

    #[Test]
    public function it_may_have_competing_interests()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')), Create::promiseFor('competing interests'), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')), Create::promiseFor(null), new PromiseSequence(Create::rejectionFor('Email addresses should not be unwrapped')));

        $this->assertEquals('competing interests', $with->getCompetingInterests());
        $this->assertNull($withOut->getCompetingInterests());
    }

    #[Test]
    public function it_may_have_email_addresses()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')), Create::rejectionFor('Competing interests should not be unwrapped'),
            $emailAddresses = new ArraySequence(['foo@example.com']));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), Create::rejectionFor('Given names should not be unwrapped'),
            Create::rejectionFor('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(Create::rejectionFor('Affiliations should not be unwrapped')), Create::rejectionFor('Research should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Profile should not be unwrapped')), Create::rejectionFor('Competing interests should not be unwrapped'),
            new EmptySequence());

        $this->assertEquals($emailAddresses, $with->getEmailAddresses());
        $this->assertCount(0, $withOut->getEmailAddresses());
    }
}
