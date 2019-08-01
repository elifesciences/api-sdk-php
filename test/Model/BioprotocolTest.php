<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\Bioprotocol;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class BioprotocolTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_section_id()
    {
        $bioprotocol = Builder::for(Bioprotocol::class)
            ->withSectionId('sectionId')
            ->__invoke();

        $this->assertSame('sectionId', $bioprotocol->getSectionId());
    }

    /**
     * @test
     */
    public function it_has_a_status()
    {
        $bioprotocol = Builder::for(Bioprotocol::class)
            ->withStatus(true)
            ->__invoke();

        $this->assertTrue($bioprotocol->getStatus());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $bioprotocol = Builder::for(Bioprotocol::class)
            ->withUri('http://example.com')
            ->__invoke();

        $this->assertSame('http://example.com', $bioprotocol->getUri());
    }
}
