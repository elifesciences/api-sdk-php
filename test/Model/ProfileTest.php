<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\AccessControl;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Profile;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class ProfileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $profile = Builder::for(Profile::class)
            ->__invoke();

        $this->assertInstanceOf(Model::class, $profile);
    }

    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $profile = Builder::for(Profile::class)
            ->withId('id')
            ->__invoke();

        $this->assertInstanceOf(HasIdentifier::class, $profile);
        $this->assertEquals(Identifier::person('id'), $profile->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $profile = Builder::for(Profile::class)
            ->withId('id')
            ->__invoke();

        $this->assertInstanceOf(HasId::class, $profile);
        $this->assertSame('id', $profile->getId());
    }

    /**
     * @test
     */
    public function it_has_details()
    {
        $profile = Builder::for(Profile::class)
            ->withDetails($details = new PersonDetails('preferred name', 'index name'))
            ->__invoke();

        $this->assertEquals($details, $profile->getDetails());
    }

    /**
     * @test
     */
    public function it_may_have_affiliations()
    {
        $with = Builder::for(Profile::class)
            ->withAffiliations($affiliations = new ArraySequence([new Place(['affiliation'])]))
            ->__invoke();

        $withOut = Builder::for(Profile::class)
            ->withSequenceOfAffiliations()
            ->__invoke();

        $this->assertEquals($affiliations, $with->getAffiliations());
        $this->assertCount(0, $withOut->getAffiliations());
    }

    /**
     * @test
     */
    public function it_may_have_email_addresses()
    {
        $with = Builder::for(Profile::class)
            ->withEmailAddresses($emailAddresses = new ArraySequence([
                new AccessControl('foo@example.com'),
            ]))
            ->__invoke();

        $withOut = Builder::for(Profile::class)
            ->withSequenceOfEmailAddresses()
            ->__invoke();

        $this->assertEquals($emailAddresses, $with->getEmailAddresses());
        $this->assertCount(0, $withOut->getEmailAddresses());
    }
}
