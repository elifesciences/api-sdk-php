<?php

namespace eLife\ApiSdk\Serializer;

use PHPUnit\Framework\TestCase;

class IdentityMapTest extends TestCase
{
    public function testResetValuesAreNotConsidered()
    {
        $map = new IdentityMap();
        $map->reset(42);
        $this->assertFalse($map->has(42));
    }
}
