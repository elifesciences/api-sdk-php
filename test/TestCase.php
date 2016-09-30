<?php

namespace test\eLife\ApiSdk;

use DateTimeInterface;
use eLife\ApiSdk\Collection;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    protected function assertObjectsAreEqual($expected, $actual)
    {
        $this->assertInstanceOf(get_class($expected), $actual);

        foreach (get_class_methods($actual) as $method) {
            if ('__' === substr($method, 0, 2)) {
                continue;
            }

            if ((new ReflectionMethod($actual, $method))->getNumberOfParameters() > 0) {
                continue;
            }

            $actualMethod = $this->normalise($actual->{$method}());
            $expectedMethod = $this->normalise($expected->{$method}());

            if (is_object($actualMethod)) {
                $this->assertObjectsAreEqual($expectedMethod, $actualMethod);
            } else {
                $this->assertEquals($expectedMethod, $actualMethod);
            }
        }
    }

    private function normalise($value)
    {
        if ($value instanceof Collection) {
            return $value->toArray();
        } elseif ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        } elseif ($value instanceof PromiseInterface) {
            return $this->normalise($value->wait());
        }

        return $value;
    }
}
