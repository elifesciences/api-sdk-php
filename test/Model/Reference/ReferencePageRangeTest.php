<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Reference\ReferencePageRange;
use PHPUnit_Framework_TestCase;

final class ReferencePageRangeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_first_page()
    {
        $page = new ReferencePageRange('foo', 'bar', 'foo, bar');

        $this->assertSame('foo', $page->getFirst());
    }

    /**
     * @test
     */
    public function it_has_a_last_page()
    {
        $page = new ReferencePageRange('foo', 'bar', 'foo, bar');

        $this->assertSame('bar', $page->getLast());
    }

    /**
     * @test
     */
    public function it_has_a_range()
    {
        $page = new ReferencePageRange('foo', 'bar', 'foo, bar');

        $this->assertSame('foo, bar', $page->getRange());
    }

    /**
     * @test
     */
    public function it_has_a_string()
    {
        $single = new ReferencePageRange('foo', 'foo', 'foo');
        $range = new ReferencePageRange('foo', 'bar', 'foo, bar');

        $this->assertSame('p. foo', $single->toString());
        $this->assertSame('pp. foo, bar', $range->toString());
    }
}
