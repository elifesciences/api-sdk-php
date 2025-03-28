<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\AccessControl;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class AccessControlTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_value()
    {
        $accessControl = Builder::for(AccessControl::class)
            ->withValue('value1')
            ->__invoke();

        $this->assertSame('value1', $accessControl->getValue());
    }

    /**
     * @test
     */
    public function it_has_an_access()
    {
        $accessControl = Builder::for(AccessControl::class)
            ->withAccess(AccessControl::ACCESS_PUBLIC)
            ->__invoke();

        $this->assertSame(AccessControl::ACCESS_PUBLIC, $accessControl->getAccess());
    }
}
