<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\Place;

final class GroupAuthorTest extends AuthorTest
{
    /**
     * @test
     */
    public function it_has_a_name()
    {
        $author = new GroupAuthor('name', new ArrayCollection([]), new ArrayCollection([]));

        $this->assertSame('name', $author->getName());
    }

    /**
     * @test
     */
    public function it_may_have_people()
    {
        $with = new GroupAuthor('name',
            $people = new ArrayCollection([new PersonAuthor(new Person('preferred name', 'index name'))]),
            new ArrayCollection([])
        );
        $withOut = new GroupAuthor('name', new ArrayCollection([]), new ArrayCollection([]));

        $this->assertEquals($people, $with->getPeople());
        $this->assertEmpty($withOut->getPeople());
    }

    /**
     * @test
     */
    public function it_may_have_groups()
    {
        $with = new GroupAuthor('name', new ArrayCollection([]),
            $groups = new ArrayCollection([
                new GroupAuthor('group', new ArrayCollection([]), new ArrayCollection([])),
            ]));
        $withOut = new GroupAuthor('name', new ArrayCollection([]), new ArrayCollection([]));

        $this->assertEquals($groups, $with->getGroups());
        $this->assertEmpty($withOut->getGroups());
    }

    protected function createAuthor(
        array $affiliations = [],
        string $competingInterests = null,
        string $contribution = null,
        array $emailAddresses = [],
        array $equalContributionGroups = [],
        Place $onBehalfOf = null,
        array $phoneNumbers = [],
        array $postalAddresses = []
    ) : Author {
        return new GroupAuthor('name', new ArrayCollection([]), new ArrayCollection([]), $affiliations,
            $competingInterests, $contribution, $emailAddresses, $equalContributionGroups, $onBehalfOf, $phoneNumbers,
            $postalAddresses);
    }
}
