<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Quote;
use PHPUnit_Framework_TestCase;

final class QuoteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $quote = new Quote([new Paragraph('foo')]);

        $this->assertInstanceOf(Block::class, $quote);
    }

    /**
     * @test
     */
    public function it_has_text()
    {
        $quote = new Quote([new Paragraph('foo')]);

        $this->assertEquals([new Paragraph('foo')], $quote->getText());
    }

    /**
     * @test
     */
    public function it_may_have_a_citation()
    {
        $with = new Quote([new Paragraph('foo')], 'bar');
        $withOut = new Quote([new Paragraph('foo')]);

        $this->assertSame('bar', $with->getCite());
        $this->assertNull($withOut->getCite());
    }
}
