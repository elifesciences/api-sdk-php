<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\PersonDetails;
use PHPUnit\Framework\TestCase;

final class PersonDetailsTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_preferred_name()
    {
        $person = new PersonDetails('preferred name', 'index name');

        $this->assertSame('preferred name', $person->getPreferredName());
    }

    /**
     * @test
     */
    public function it_has_a_index_name()
    {
        $person = new PersonDetails('preferred name', 'index name');

        $this->assertSame('index name', $person->getIndexName());
    }

    /**
     * @test
     */
    public function it_may_have_an_orcid()
    {
        $with = new PersonDetails('preferred name', 'index name', '0000-0002-1825-0097');
        $withOut = new PersonDetails('preferred name', 'index name');

        $this->assertSame('0000-0002-1825-0097', $with->getOrcid());
        $this->assertNull($withOut->getOrcid());
    }
}
