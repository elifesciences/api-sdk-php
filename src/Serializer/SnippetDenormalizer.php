<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Promise\CallbackPromise;
use GuzzleHttp\Promise\PromiseInterface;

final class SnippetDenormalizer
{
    private $id;
    private $fetcher;
    private $identityMap;
    private $globalCallback;

    public function __construct(callable $id, callable $fetcher)
    {
        $this->id = $id;
        $this->fetcher = $fetcher;
        $this->identityMap = new IdentityMap();
    }

    public function denormalizeSnippet(array $item) : PromiseInterface
    {
        $id = call_user_func($this->id, $item);

        if ($this->identityMap->has($id)) {
            return $this->identityMap->get($id);
        }

        $this->identityMap->reset($id);

        if (empty($this->globalCallback)) {
            $this->globalCallback = new CallbackPromise(function () {
                $this->identityMap->fillMissingWith($this->fetcher);

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
