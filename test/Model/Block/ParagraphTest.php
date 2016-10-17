<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use PHPUnit_Framework_TestCase;

final class ParagraphTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $paragraph = new Paragraph('foo');

        $this->assertInstanceOf(Block::class, $paragraph);
    }

    /**
     * @test
     */
    public function it_has_text()
    {
        $paragraph = new Paragraph('foo');

        $this->assertSame('foo', $paragraph->getText());
    }
}
