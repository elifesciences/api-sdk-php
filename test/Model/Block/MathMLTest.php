<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\MathML;
use eLife\ApiSdk\Model\HasId;
use PHPUnit_Framework_TestCase;

final class MathMLTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $mathML = new MathML(null, null, '<math></math>');

        $this->assertInstanceOf(Block::class, $mathML);
    }

    /**
     * @test
     */
    public function it_may_have_an_id()
    {
        $with = new MathML('id', null, '<math></math>');
        $withOut = new MathML(null, null, '<math></math>');

        $this->assertInstanceOf(HasId::class, $with);
        $this->assertSame('id', $with->getId());
        $this->assertNull($withOut->getId());
    }

    /**
     * @test
     */
    public function it_may_have_a_label()
    {
        $with = new MathML(null, 'label', '<math></math>');
        $withOut = new MathML(null, null, '<math></math>');

        $this->assertSame('label', $with->getLabel());
        $this->assertNull($withOut->getLabel());
    }

    /**
     * @test
     */
    public function it_has_math_ml()
    {
        $mathML = new MathML(null, null, '<math></math>');

        $this->assertSame('<math></math>', $mathML->getMathML());
    }
}
