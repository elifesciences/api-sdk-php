<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\Place;
use eLife\ApiSdk\Model\Reviewer;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class ReviewerTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_preferred_name()
    {
        $reviewer = Builder::for(Reviewer::class)
            ->withPerson(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'))
            ->__invoke();

        $this->assertSame('preferred name', $reviewer->getPreferredName());
    }

    /**
     * @test
     */
    public function it_has_a_index_name()
    {
        $reviewer = Builder::for(Reviewer::class)
            ->withPerson(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'))
            ->__invoke();

        $this->assertSame('index name', $reviewer->getIndexName());
    }

    /**
     * @test
     */
    public function it_may_have_an_orcid()
    {
        $with = Builder::for(Reviewer::class)
            ->withPerson(new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097'))
            ->__invoke();
        $withOut = Builder::for(Reviewer::class)
            ->withPerson(new PersonDetails('preferred name', 'index name'))
            ->__invoke();

        $this->assertSame('0000-0002-1825-0097', $with->getOrcid());
        $this->assertNull($withOut->getOrcid());
    }

    /**
     * @test
     */
    public function it_has_a_role()
    {
        $reviewer = Builder::for(Reviewer::class)
            ->withRole('role')
            ->__invoke();

        $this->assertSame('role', $reviewer->getRole());
    }

    /**
     * @test
     */
    public function it_may_have_affiliations()
    {
        $with = Builder::for(Reviewer::class)
            ->withAffiliations($affiliations = [new Place(['affiliation'])])
            ->__invoke();
        $withOut = Builder::for(Reviewer::class)
            ->withAffiliations([])
            ->__invoke();

        $this->assertEquals($affiliations, $with->getAffiliations());
        $this->assertEmpty($withOut->getAffiliations());
    }
}
