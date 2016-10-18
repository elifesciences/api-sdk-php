<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\GroupAuthor;
use eLife\ApiSdk\Model\Person;
use eLife\ApiSdk\Model\PersonAuthor;

final class GroupAuthorTest extends AuthorTest
{
    /**
     * @test
     */
    public function it_has_a_name()
    {
        $author = new GroupAuthor('name', new ArraySequence([]));

        $this->assertSame('name', $author->getName());
    }

    /**
     * @test
     */
    public function it_may_have_people()
    {
        $with = new GroupAuthor('name',
            $people = new ArraySequence([new PersonAuthor(new Person('preferred name', 'index name'))]));
        $withOut = new GroupAuthor('name', new ArraySequence([]));

        $this->assertEquals($people, $with->getPeople());
        $this->assertEmpty($withOut->getPeople());
    }

    /**
     * @test
     */
    public function it_may_have_groups()
    {
        $with = new GroupAuthor('name', new ArraySequence([]),
            $groups = ['group' => [new Person('preferred name', 'index name')]]);
        $withOut = new GroupAuthor('name', new ArraySequence([]));

        $this->assertEquals($groups, $with->getGroups());
        $this->assertEmpty($withOut->getGroups());
    }

    protected function createAuthor(
        array $affiliations = [],
        string $competingInterests = null,
        string $contribution = null,
        array $emailAddresses = [],
        array $equalContributionGroups = [],
        array $phoneNumbers = [],
        array $postalAddresses = []
    ) : Author {
        return new GroupAuthor('name', new ArraySequence([]), [], $affiliations,
            $competingInterests, $contribution, $emailAddresses, $equalContributionGroups, $phoneNumbers,
            $postalAddresses);
    }
}
