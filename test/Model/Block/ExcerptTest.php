<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Excerpt;
use eLife\ApiSdk\Model\Block\Paragraph;
use PHPUnit_Framework_TestCase;

final class ExcerptTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $excerpt = new Excerpt(new ArraySequence([new Paragraph('foo')]));

        $this->assertInstanceOf(Block::class, $excerpt);
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $excerpt = new Excerpt($content = new ArraySequence([new Paragraph('foo')]));

        $this->assertEquals($content, $excerpt->getContent());
    }

    /**
     * @test
     */
    public function it_may_have_a_citation()
    {
        $with = new Excerpt(new ArraySequence([new Paragraph('foo')]), 'bar');
        $withOut = new Excerpt(new ArraySequence([new Paragraph('foo')]));

        $this->assertSame('bar', $with->getCite());
        $this->assertNull($withOut->getCite());
    }
}
