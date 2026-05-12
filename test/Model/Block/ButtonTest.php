<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Button;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ButtonTest extends TestCase
{
    #[Test]
    public function it_is_a_block()
    {
        $button = new Button('foo', 'http://www.example.com/');

        $this->assertInstanceOf(Block::class, $button);
    }

    #[Test]
    public function it_has_text()
    {
        $button = new Button('foo', 'http://www.example.com/');

        $this->assertSame('foo', $button->getText());
    }

    #[Test]
    public function it_has_a_uri()
    {
        $button = new Button('foo', 'http://www.example.com/');

        $this->assertSame('http://www.example.com/', $button->getUri());
    }
}
