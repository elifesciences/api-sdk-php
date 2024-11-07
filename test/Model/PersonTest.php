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
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class PersonTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertInstanceOf(Model::class, $person);
    }

    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertInstanceOf(HasIdentifier::class, $person);
        $this->assertEquals(Identifier::person('id'), $person->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertInstanceOf(HasId::class, $person);
        $this->assertSame('id', $person->getId());
    }

    /**
     * @test
     */
    public function it_has_details()
    {
        $person = new Person('id', $details = new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertEquals($details, $person->getDetails());
    }

    /**
     * @test
     */
    public function it_may_have_given_names()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            promise_for('surname'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            promise_for(null), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertEquals('surname', $with->getSurname());
        $this->assertNull($withOut->getSurname());
    }

    /**
     * @test
     */
    public function it_may_have_a_surname()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), promise_for('given names'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), promise_for(null),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertEquals('given names', $with->getGivenNames());
        $this->assertNull($withOut->getGivenNames());
    }

    /**
     * @test
     */
    public function it_has_a_type()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertSame('senior-editor', $person->getType());
    }

    /**
     * @test
     */
    public function it_has_a_type_label()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertSame('label', $person->getTypeLabel());
    }

    /**
     * @test
     */
    public function it_may_have_a_thumbnail()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label',
            $image = Builder::for(Image::class)->sample('thumbnail'),
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertInstanceOf(HasThumbnail::class, $with);
        $this->assertEquals($image, $with->getThumbnail());
        $this->assertNull($withOut->getThumbnail());
    }

    /**
     * @test
     */
    public function it_may_have_affiliations()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            $affiliations = new ArraySequence([new Place(['affiliation'])]), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new EmptySequence(), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertEquals($affiliations, $with->getAffiliations());
        $this->assertCount(0, $withOut->getAffiliations());
    }

    /**
     * @test
     */
    public function it_may_have_research()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')),
            promise_for($research = new PersonResearch(new EmptySequence(), ['focus'], [])),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')),
            promise_for(null), new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertEquals($research, $with->getResearch());
        $this->assertNull($withOut->getResearch());
    }

    /**
     * @test
     */
    public function it_may_have_a_profile()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'), $profile = new ArraySequence([new Paragraph('profile')]),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'), new EmptySequence(),
            rejection_for('Competing interests should not be unwrapped'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertEquals($profile, $with->getProfile());
        $this->assertCount(0, $withOut->getProfile());
    }

    /**
     * @test
     */
    public function it_may_have_competing_interests()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')), promise_for('competing interests'), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')), promise_for(null), new PromiseSequence(rejection_for('Email addresses should not be unwrapped')));

        $this->assertEquals('competing interests', $with->getCompetingInterests());
        $this->assertNull($withOut->getCompetingInterests());
    }

    /**
     * @test
     */
    public function it_may_have_email_addresses()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')), rejection_for('Competing interests should not be unwrapped'),
            $emailAddresses = new ArraySequence(['foo@example.com']));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), rejection_for('Given names should not be unwrapped'),
            rejection_for('Surname should not be unwrapped'), 'senior-editor', 'label', null,
            new PromiseSequence(rejection_for('Affiliations should not be unwrapped')), rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')), rejection_for('Competing interests should not be unwrapped'),
            new EmptySequence());

        $this->assertEquals($emailAddresses, $with->getEmailAddresses());
        $this->assertCount(0, $withOut->getEmailAddresses());
    }
}
