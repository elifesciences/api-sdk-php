<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Code;
use PHPUnit_Framework_TestCase;

final class CodeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $code = new Code('foo');

        $this->assertInstanceOf(Block::class, $code);
    }

    /**
     * @test
     */
    public function it_has_code()
    {
        $code = new Code('foo');

        $this->assertSame('foo', $code->getCode());
    }

    /**
     * @test
     */
    public function it_may_have_a_language()
    {
        $with = new Code('foo', 'PHP');
        $withOut = new Code('foo');

        $this->assertSame('PHP', $with->getLanguage());
        $this->assertNull($withOut->getLanguage());
    }
}
