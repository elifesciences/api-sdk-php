<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\PersonAuthor;
use eLife\ApiSdk\Model\PersonDetails;

final class PersonAuthorTest extends AuthorTest
{
    /**
     * @test
     */
    public function it_has_a_preferred_name()
    {
        $author = new PersonAuthor(new PersonDetails('preferred name', 'index name'));

        $this->assertSame('preferred name', $author->getPreferredName());
        $this->assertSame('preferred name', $author->toString());
    }

    /**
     * @test
     */
    public function it_has_a_index_name()
    {
        $author = new PersonAuthor(new PersonDetails('preferred name', 'index name'));

        $this->assertSame('index name', $author->getIndexName());
    }

    /**
     * @test
     */
    public function it_may_have_an_orcid()
    {
        $with = new PersonAuthor(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'));
        $withOut = new PersonAuthor(new PersonDetails('preferred name', 'index name'));

        $this->assertSame('0000-0002-1825-0097', $with->getOrcid());
        $this->assertNull($withOut->getOrcid());
    }

    /**
     * @test
     */
    public function it_may_have_a_biography()
    {
        $biography = new ArraySequence([new Paragraph('biography')]);
        $with = new PersonAuthor(new PersonDetails('preferred name', 'index name'), $biography);
        $withOut = new PersonAuthor(new PersonDetails('preferred name', 'index name'));

        $this->assertEquals($biography, $with->getBiography());
        $this->assertEmpty($withOut->getBiography());
    }

    /**
     * @test
     */
    public function it_may_be_deceased()
    {
        $with = new PersonAuthor(new PersonDetails('preferred name', 'index name'), new EmptySequence(), true);
        $withOut = new PersonAuthor(new PersonDetails('preferred name', 'index name'));

        $this->assertTrue($with->isDeceased());
        $this->assertFalse($withOut->isDeceased());
    }

    /**
     * @test
     */
    public function it_may_have_a_role()
    {
        $with = new PersonAuthor(new PersonDetails('preferred name', 'index name'), new EmptySequence(), false, 'role');
        $withOut = new PersonAuthor(new PersonDetails('preferred name', 'index name'));

        $this->assertSame('role', $with->getRole());
        $this->assertNull($withOut->getRole());
    }

    protected function createAuthor(
        array $additionalInformation = [],
        array $affiliations = [],
        string $competingInterests = null,
        string $contribution = null,
        array $emailAddresses = [],
        array $equalContributionGroups = [],
        array $phoneNumbers = [],
        array $postalAddresses = []
    ) : Author {
        return new PersonAuthor(new PersonDetails('preferred name', 'index name'), new EmptySequence(), false,
            null, $additionalInformation, $affiliations, $competingInterests, $contribution, $emailAddresses,
            $equalContributionGroups, $phoneNumbers, $postalAddresses);
    }
}
