<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Copyright;
use PHPUnit\Framework\TestCase;

final class CopyrightTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_license()
    {
        $copyright = new Copyright('license', 'statement');

        $this->assertSame('license', $copyright->getLicense());
    }

    /**
     * @test
     */
    public function it_has_a_statement()
    {
        $copyright = new Copyright('license', 'statement');

        $this->assertSame('statement', $copyright->getStatement());
    }

    /**
     * @test
     */
    public function it_may_have_a_holder()
    {
        $with = new Copyright('license', 'statement', 'holder');
        $withOut = new Copyright('license', 'statement');

        $this->assertSame('holder', $with->getHolder());
        $this->assertNull($withOut->getHolder());
    }
}
