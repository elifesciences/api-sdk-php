<?php

namespace eLife\ApiSdk\Promise;

use Exception;
use GuzzleHttp\Promise\CancellationException;
use GuzzleHttp\Promise\PromiseInterface;
use LogicException;
use RuntimeException;
use function GuzzleHttp\Promise\exception_for;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

/**
 * @internal
 */
final class CallbackPromise implements PromiseInterface
{
    private $callback;
    private $callbackOnRejected;
    private $result;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
        $this->callbackOnRejected = function (Exception $e) {
            throw $e;
        };
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        switch ($this->getState()) {
            case PromiseInterface::FULFILLED:
                return $onFulfilled
                    ? promise_for($this->result)->then($onFulfilled)
                    : promise_for($this->result);
            case PromiseInterface::REJECTED:
                return $onRejected
                    ? rejection_for($this->result)->otherwise($onRejected)
                    : rejection_for($this->result);
        }

        $clone = clone $this;

        if ($onFulfilled) {
            $clone->callback = function () use ($onFulfilled) {
                return call_user_func($onFulfilled, $this->wait());
            };
        }

        if ($onRejected) {
            $clone->callbackOnRejected = function (Exception $e) use ($onRejected) {
                try {
                    return call_user_func($this->callbackOnRejected, $e);
                } catch (Exception $e) {
                    return call_user_func($onRejected, $e);
                }
            };
        }

        return $clone;
    }

    public function otherwise(callable $onRejected)
    {
        return $this->then(null, $onRejected);
    }

    public function getState()
    {
        if ($this->result instanceof Exception) {
            return PromiseInterface::REJECTED;
        } elseif ($this->result) {
            return PromiseInterface::FULFILLED;
        }

        return PromiseInterface::PENDING;
    }

    public function resolve($value)
    {
        throw new LogicException('Cannot resolve a callback promise');
    }

    public function reject($reason)
    {
        if (PromiseInterface::PENDING !== $this->getState()) {
            throw new RuntimeException('Promise is already resolved');
        }

        $this->result = exception_for($reason);
    }

    public function cancel()
    {
        $this->result = new CancellationException('Promise has been cancelled');
    }

    public function wait($unwrap = true)
    {
        $this->run();

        if (false === $unwrap) {
            return null;
        }

        if ($this->result instanceof Exception) {
            throw $this->result;
        }

        return $this->result;
    }

    private function run()
    {
        if (PromiseInterface::PENDING !== $this->getState()) {
            return;
        }

        try {
            $this->result = call_user_func($this->callback);
        } catch (Exception $e) {
            try {
                $this->result = call_user_func($this->callbackOnRejected, $e);
            } catch (Exception $e) {
                $this->result = $e;
            }
        }

        $this->callback = null;
    }
}
