<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Place;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

abstract class AuthorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    final public function it_is_an_author_entry()
    {
        $author = $this->createAuthor();

        $this->assertInstanceOf(AuthorEntry::class, $author);
    }

    /**
     * @test
     */
    final public function it_may_have_affiliations()
    {
        $with = $this->createAuthor($affiliations = [new Place(null, null, ['affiliation'])]);
        $withOut = $this->createAuthor();

        $this->assertEquals($affiliations, $with->getAffiliations());
        $this->assertEmpty($withOut->getAffiliations());
    }

    /**
     * @test
     */
    final public function it_may_have_a_competing_interests_statement()
    {
        $with = $this->createAuthor([], 'competing interests');
        $withOut = $this->createAuthor();

        $this->assertSame('competing interests', $with->getCompetingInterests());
        $this->assertNull($withOut->getCompetingInterests());
    }

    /**
     * @test
     */
    final public function it_may_have_a_contribution()
    {
        $with = $this->createAuthor([], null, 'contribution');
        $withOut = $this->createAuthor();

        $this->assertSame('contribution', $with->getContribution());
        $this->assertNull($withOut->getContribution());
    }

    /**
     * @test
     */
    final public function it_may_have_email_addresses()
    {
        $with = $this->createAuthor([], null, null, ['foo@example.com']);
        $withOut = $this->createAuthor();

        $this->assertSame(['foo@example.com'], $with->getEmailAddresses());
        $this->assertEmpty($withOut->getEmailAddresses());
    }

    /**
     * @test
     */
    final public function it_may_have_equal_contribution_groups()
    {
        $with = $this->createAuthor([], null, null, [], [1, 2]);
        $withOut = $this->createAuthor();

        $this->assertSame([1, 2], $with->getEqualContributionGroups());
        $this->assertEmpty($withOut->getEqualContributionGroups());
    }

    /**
     * @test
     */
    final public function it_may_have_phone_numbers()
    {
        $with = $this->createAuthor([], null, null, [], [], ['+447700900415']);
        $withOut = $this->createAuthor();

        $this->assertSame(['+447700900415'], $with->getPhoneNumbers());
        $this->assertEmpty($withOut->getPhoneNumbers());
    }

    /**
     * @test
     */
    final public function it_may_have_postal_addresses()
    {
        $with = $this->createAuthor([], null, null, [], [], [], $postalAddresses = [Builder::dummy(Address::class)]);
        $withOut = $this->createAuthor();

        $this->assertEquals($postalAddresses, $with->getPostalAddresses());
        $this->assertEmpty($withOut->getPostalAddresses());
    }

    abstract protected function createAuthor(
        array $affiliations = [],
        string $competingInterests = null,
        string $contribution = null,
        array $emailAddresses = [],
        array $equalContributionGroups = [],
        array $phoneNumbers = [],
        array $postalAddresses = []
    ) : Author;
}
