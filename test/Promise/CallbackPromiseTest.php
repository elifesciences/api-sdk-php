<?php

namespace test\eLife\ApiSdk\Promise;

use eLife\ApiSdk\Promise\CallbackPromise;
use Exception;
use GuzzleHttp\Promise\CancellationException;
use GuzzleHttp\Promise\PromiseInterface;
use LogicException;
use PHPUnit_Framework_TestCase;

final class CallbackPromiseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_a_callback()
    {
        $promise = new CallbackPromise(function () {
            return 'foo';
        });

        $this->assertSame('foo', $promise->wait());
    }

    /**
     * @test
     */
    public function it_returns_a_callback_after_being_resolved()
    {
        $promise = new CallbackPromise(function () {
            return 'foo';
        });

        $promise->wait();

        $promise = $promise->then(function () {
            return 'bar';
        });

        $this->assertSame('bar', $promise->wait());
    }

    /**
     * @test
     */
    public function it_returns_a_callback_after_being_rejected()
    {
        $promise = new CallbackPromise(function () {
            return 'foo';
        });

        $promise->reject('bar');

        $promise = $promise->otherwise(function () {
            return 'baz';
        });

        $this->assertSame('baz', $promise->wait());
    }

    /**
     * @test
     * @dataProvider stateProvider
     */
    public function it_has_a_state(callable $callback, string $outcome)
    {
        $promise = new CallbackPromise($callback);

        $this->assertSame(PromiseInterface::PENDING, $promise->getState());

        $promise->wait(false);

        $this->assertSame($outcome, $promise->getState());
    }

    public function stateProvider() : array
    {
        return [
            'fulfilled' => [
                function () {
                    return 'foo';
                },
                PromiseInterface::FULFILLED,
            ],
            'rejected' => [
                function () {
                    throw new Exception();
                },
                PromiseInterface::REJECTED,
            ],
        ];
    }

    /**
     * @test
     */
    public function it_does_nothing_when_not_unwrapping()
    {
        $promise = new CallbackPromise(function () {
            throw new Exception();
        });

        $promise->wait(false);
    }

    /**
     * @test
     */
    public function it_chains_callbacks()
    {
        $i = 0;

        $promise = new CallbackPromise(function () use (&$i) {
            ++$i;

            return $i;
        });

        $promise1 = $promise->then(function (int $count) {
            return $count.' foo';
        });

        $promise2 = $promise->then(function (int $count) {
            return $count.' bar';
        });

        $this->assertSame('1 foo', $promise1->wait());
        $this->assertSame('1 bar', $promise2->wait());
    }

    /**
     * @test
     */
    public function it_runs_a_callbacks_on_failure()
    {
        $promise = new CallbackPromise(function () {
            throw new Exception('Should not be thrown');
        });

        $promise = $promise->then(function () {
            return 'foo';
        }, function () {
            return 'bar';
        });

        $this->assertSame('bar', $promise->wait());
    }

    /**
     * @test
     */
    public function it_runs_a_callbacks_on_failure_2()
    {
        $promise = new CallbackPromise(function () {
            throw new Exception('Should not be thrown');
        });

        $promise = $promise->otherwise(function () {
            return 'foo';
        });

        $this->assertSame('foo', $promise->wait());
    }

    /**
     * @test
     */
    public function it_cannot_be_resolved()
    {
        $promise = new CallbackPromise(function () {
            throw new Exception('Should not be thrown');
        });

        $this->expectException(LogicException::class);

        $promise->resolve('foo');
    }

    /**
     * @test
     */
    public function it_can_be_cancelled()
    {
        $promise = new CallbackPromise(function () {
            throw new Exception('Should not be thrown');
        });

        $this->expectException(CancellationException::class);

        $promise->cancel();

        $promise->wait();
    }
}
