<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Promise\CallbackPromise;
use GuzzleHttp\Promise\PromiseInterface;

final class SnippetDenormalizer
{
    private $determineId;
    private $fetchComplete;
    private $identityMap;
    private $globalCallback;

    public function __construct(callable $determineId, callable $fetchComplete)
    {
        $this->determineId = $determineId;
        $this->fetchComplete = $fetchComplete;
        $this->identityMap = new IdentityMap();
    }

    public function denormalizeSnippet(array $item) : PromiseInterface
    {
        $id = call_user_func($this->determineId, $item);

        if ($this->identityMap->has($id)) {
            return $this->identityMap->get($id);
        }

        $this->identityMap->reset($id);

        if (empty($this->globalCallback)) {
            $this->globalCallback = new CallbackPromise(function () {
                $this->identityMap->fillMissingWith($this->fetchComplete);

                $this->globalCallback = null;

                $settled = $this->identityMap->waitForAll();

                $this->identityMap->reset();

                return $settled;
            });
        }

        return $this->globalCallback
            ->then(function (array $items) use ($id) {
                return $items[$id];
            });
    }
}
