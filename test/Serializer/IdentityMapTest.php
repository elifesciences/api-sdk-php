<?php

namespace eLife\ApiSdk\Serializer;

class IdentityMapTest extends \PHPUnit_Framework_TestCase
{
    public function testResetValuesAreNotConsidered()
    {
        $map = new IdentityMap();
        $map->reset(42);
        $this->assertFalse($map->has(42));
    }
}
