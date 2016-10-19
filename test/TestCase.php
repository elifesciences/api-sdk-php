<?php

namespace test\eLife\ApiSdk;

use DateTimeInterface;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use Traversable;

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

            $this->assertItemsAreEqual($expected->{$method}(), $actual->{$method}());
        }
    }

    private function assertItemsAreEqual($expected, $actual)
    {
        $actual = $this->normalise($actual);
        $expected = $this->normalise($expected);

        if (is_object($actual)) {
            $this->assertObjectsAreEqual($expected, $actual);
        } elseif (is_array($actual)) {
            foreach ($actual as $key => $actualItem) {
                $this->assertItemsAreEqual($expected[$key], $actualItem);
            }
        } else {
            $this->assertEquals($expected, $actual);
        }
    }

    private function normalise($value)
    {
        if ($value instanceof Traversable) {
            return iterator_to_array($value);
        } elseif ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        } elseif ($value instanceof PromiseInterface) {
            return $this->normalise($value->wait());
        }

        return $value;
    }
}
