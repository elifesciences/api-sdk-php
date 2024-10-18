<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Button;
use PHPUnit\Framework\TestCase;

final class ButtonTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $button = new Button('foo', 'http://www.example.com/');

        $this->assertInstanceOf(Block::class, $button);
    }

    /**
     * @test
     */
    public function it_has_text()
    {
        $button = new Button('foo', 'http://www.example.com/');

        $this->assertSame('foo', $button->getText());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $button = new Button('foo', 'http://www.example.com/');

        $this->assertSame('http://www.example.com/', $button->getUri());
    }
}
