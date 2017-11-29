<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\AccessControl;
use eLife\ApiSdk\Model\Model;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class AccessControlTest extends PHPUnit_Framework_TestCase
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
            ->withValue('value1')
            ->withAccess('public')
            ->__invoke();

        $this->assertSame('public', $accessControl->getAccess());
    }
}
