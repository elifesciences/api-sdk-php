<?php

namespace test\eLife\ApiSdk;

use DateTimeInterface;
use DateTimeZone;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\AnnualReport;
use eLife\ApiSdk\Model\Cover;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\LabsPost;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\Model\ReviewedPreprint;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectionException;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ReflectionMethod;
use RuntimeException;
use Traversable;

abstract class TestCase extends PHPUnitTestCase
{
    final protected function classNameProvider(string ...$classes) : Traversable
    {
        foreach ($classes as $class) {
            yield $class => [$class];
        }
    }

    final protected function assertObjectsAreEqual($expected, $actual, $detail = '')
    {
        $this->assertIsObject($expected, $detail);
        $this->assertIsObject($actual, $detail);
        $this->assertInstanceOf(get_class($expected), $actual, $detail);

        foreach (get_class_methods($actual) as $method) {
            if ('__' === substr($method, 0, 2)) {
                continue;
            }

            if ((new ReflectionMethod($actual, $method))->getNumberOfParameters() > 0) {
                continue;
            }

            $methodDetail = $detail.' '.get_class($actual).'::'.$method;
            try {
                $this->assertItemsAreEqual($expected->{$method}(), $actual->{$method}(), $methodDetail);
            } catch (RejectionException $e) {
                throw new RuntimeException("$methodDetail caused a Promise rejection", -1, $e);
            }
        }
    }

    private function assertItemsAreEqual($expected, $actual, $detail = null)
    {
        $actual = $this->normalise($actual);
        $expected = $this->normalise($expected);

        if (is_object($actual)) {
            $this->assertObjectsAreEqual($expected, $actual, $detail);
        } elseif (is_array($actual)) {
            $this->assertIsArray($expected, "We are getting an array out of $detail but we were not expecting it");
            $this->assertEquals(count($expected), count($actual), "Count of $detail doesn't match expected");
            foreach ($actual as $key => $actualItem) {
                $this->assertItemsAreEqual($expected[$key], $actualItem, $detail.' '.$key);
            }
        } else {
            $this->assertEquals($expected, $actual, $detail);
        }
    }

    private function normalise($value)
    {
        if ($value instanceof Traversable) {
            return iterator_to_array($value);
        } elseif ($value instanceof DateTimeInterface) {
            $this->assertEquals(new DateTimeZone('Z'), $value->getTimezone());

            return $value->format(ApiSdk::DATE_FORMAT);
        } elseif ($value instanceof PromiseInterface) {
            return $this->normalise($value->wait());
        }

        return $value;
    }

    final protected function tidyValue() : callable
    {
        return function ($value) {
            if ($value instanceof HasId) {
                return $value->getId();
            } elseif ($value instanceof AnnualReport) {
                return $value->getYear();
            } elseif ($value instanceof Cover) {
                return $value->getTitle();
            } elseif ($value instanceof LabsPost) {
                return $value->getTitle();
            } elseif ($value instanceof PodcastEpisode) {
                return $value->getTitle();
            }

            return $value;
        };
    }
}
