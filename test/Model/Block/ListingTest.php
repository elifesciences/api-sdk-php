<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Listing;
use PHPUnit\Framework\TestCase;

final class ListingTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $listing = new Listing(Listing::PREFIX_NONE, new ArraySequence(['foo']));

        $this->assertInstanceOf(Block::class, $listing);
    }

    /**
     * @test
     */
    public function it_has_a_prefix()
    {
        $listing = new Listing(Listing::PREFIX_NONE, new ArraySequence(['foo']));

        $this->assertSame(Listing::PREFIX_NONE, $listing->getPrefix());
    }

    /**
     * @test
     */
    public function it_has_items()
    {
        $listing = new Listing(Listing::PREFIX_NONE, $items = new ArraySequence(['foo']));

        $this->assertEquals($items, $listing->getItems());
    }
}
