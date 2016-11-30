<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\PersonResearch;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class PersonTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor', null,
            rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'));

        $this->assertInstanceOf(Model::class, $person);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor', null,
            rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'));

        $this->assertInstanceOf(HasId::class, $person);
        $this->assertSame('id', $person->getId());
    }

    /**
     * @test
     */
    public function it_has_details()
    {
        $person = new Person('id', $details = new PersonDetails('preferred name', 'index name'), 'senior-editor', null,
            rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'));

        $this->assertEquals($details, $person->getDetails());
    }

    /**
     * @test
     */
    public function it_has_a_type()
    {
        $person = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor', null,
            rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'));

        $this->assertSame('senior-editor', $person->getType());
    }

    /**
     * @test
     */
    public function it_may_have_a_thumbnail()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor',
            $image = new Image('', [900 => 'https://placehold.it/900x450']),
            rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor', null,
            rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'));

        $this->assertInstanceOf(HasThumbnail::class, $with);
        $this->assertEquals($image, $with->getThumbnail());
        $this->assertNull($withOut->getThumbnail());
    }

    /**
     * @test
     */
    public function it_may_have_research()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor', null,
            promise_for($research = new PersonResearch(new EmptySequence(), ['focus'], [])),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor', null,
            promise_for(null), new PromiseSequence(rejection_for('Profile should not be unwrapped')),
            rejection_for('Competing interests should not be unwrapped'));

        $this->assertEquals($research, $with->getResearch());
        $this->assertNull($withOut->getResearch());
    }

    /**
     * @test
     */
    public function it_may_have_a_profile()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor', null,
            rejection_for('Research should not be unwrapped'), $profile = new ArraySequence([new Paragraph('profile')]),
            rejection_for('Competing interests should not be unwrapped'));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor', null,
            rejection_for('Research should not be unwrapped'), new EmptySequence(),
            rejection_for('Competing interests should not be unwrapped'));

        $this->assertEquals($profile, $with->getProfile());
        $this->assertCount(0, $withOut->getProfile());
    }

    /**
     * @test
     */
    public function it_may_have_competing_interests()
    {
        $with = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor', null,
            rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')), promise_for('competing interests'));
        $withOut = new Person('id', new PersonDetails('preferred name', 'index name'), 'senior-editor', null,
            rejection_for('Research should not be unwrapped'),
            new PromiseSequence(rejection_for('Profile should not be unwrapped')), promise_for(null));

        $this->assertEquals('competing interests', $with->getCompetingInterests());
        $this->assertNull($withOut->getCompetingInterests());
    }
}
