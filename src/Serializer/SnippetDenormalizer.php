<?php

namespace eLife\ApiSdk\Serializer;

use GuzzleHttp\Promise\Promise;
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

        echo "denormalize $id", PHP_EOL;
        if ($this->identityMap->has($id)) {
            return $this->identityMap->get($id);
        }

        $this->identityMap->reset($id);

        if (empty($this->globalCallback)) {
            $this->globalCallback = new Promise(function () {
                $this->identityMap->fillMissingWith($this->fetchComplete);

                $settled = $this->identityMap->waitForAll();

                $this->identityMap->reset();

                var_dump(
                    "Resolving globalCallback with "
                    .count($settled)
                    ." values of which "
                    .count(array_filter(
                        $settled,
                        function($v) { return !$v; }
                    ))
                    ." are null"
                );
                $this->globalCallback->resolve($settled);

                $this->globalCallback = null;
            });
        }

        return $this->globalCallback
            ->then(function (array $items) use ($id) {
                $retrieved = $items[$id];
                if (!$retrieved) {
                    throw new \RuntimeException('Null value from globalCallback: '.$id);
                }

                return $retrieved;
            });
    }
}
