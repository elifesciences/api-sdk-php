<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block\Section;
use PHPUnit_Framework_TestCase;

final class SectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_title()
    {
        $section = new Section('title', []);

        $this->assertSame('title', $section->getTitle());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [new Section('sub-title', [])];
        $section = new Section('title', $content);

        $this->assertEquals($content, $section->getContent());
    }
}
