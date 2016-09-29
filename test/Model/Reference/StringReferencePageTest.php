<?php

namespace test\eLife\ApiSdk\Model\Reference;

use eLife\ApiSdk\Model\Reference\StringReferencePage;
use PHPUnit_Framework_TestCase;

final class StringReferencePageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_string()
    {
        $page = new StringReferencePage('foo');

        $this->assertSame('foo', $page->toString());
    }
}
