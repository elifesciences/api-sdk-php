<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\PressPackagesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\PressPackage;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class PressPackages implements Iterator, Sequence
{
    const VERSION_PRESS_PACKAGE = 3;
    const VERSION_PRESS_PACKAGE_LIST = 1;

    use Client;
    use ForSubject;

    private $count;
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $pressPackagesClient;
    private $denormalizer;

    public function __construct(PressPackagesClient $pressPackagesClient, DenormalizerInterface $denormalizer)
    {
        $this->pressPackagesClient = $pressPackagesClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->pressPackagesClient
            ->getPackage(
                ['Accept' => (string) new MediaType(PressPackagesClient::TYPE_PRESS_PACKAGE, self::VERSION_PRESS_PACKAGE)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), PressPackage::class);
            });
    }

    public function slice(int $offset, int $length = null) : Sequence
    {
        if (null === $length) {
            return new PromiseSequence($this->all()
                ->then(function (Sequence $sequence) use ($offset) {
                    return $sequence->slice($offset);
                })
            );
        }

        return new PromiseSequence($this->pressPackagesClient
            ->listPackages(
                ['Accept' => (string) new MediaType(PressPackagesClient::TYPE_PRESS_PACKAGE_LIST, self::VERSION_PRESS_PACKAGE_LIST)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder,
                $this->subjectsQuery
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $package) {
                    return $this->denormalizer->denormalize($package, PressPackage::class, null, ['snippet' => true]);
                }, $result['items']);
            })
        );
    }

    public function reverse() : Sequence
    {
        $clone = clone $this;

        $clone->descendingOrder = !$this->descendingOrder;

        return $clone;
    }

    protected function invalidateData()
    {
        $this->count = null;
    }
}
