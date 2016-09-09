<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Person;
use PHPUnit_Framework_TestCase;

final class PersonTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_preferred_name()
    {
        $person = new Person('preferred name', 'index name');

        $this->assertSame('preferred name', $person->getPreferredName());
    }

    /**
     * @test
     */
    public function it_has_a_index_name()
    {
        $person = new Person('preferred name', 'index name');

        $this->assertSame('index name', $person->getIndexName());
    }

    /**
     * @test
     */
    public function it_may_have_an_orcid()
    {
        $with = new Person('preferred name', 'index name', '0000-0002-1825-0097');
        $withOut = new Person('preferred name', 'index name');

        $this->assertSame('0000-0002-1825-0097', $with->getOrcid());
        $this->assertNull($withOut->getOrcid());
    }
}
