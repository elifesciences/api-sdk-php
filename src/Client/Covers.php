<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\CoversClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Cover;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Covers implements Iterator, Sequence
{
    use Client;

    private $count;
    private $descendingOrder = true;
    private $coversClient;
    private $denormalizer;

    public function __construct(CoversClient $coversClient, DenormalizerInterface $denormalizer)
    {
        $this->coversClient = $coversClient;
        $this->denormalizer = $denormalizer;
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

        return new PromiseSequence($this->coversClient
            ->listCovers(
                ['Accept' => new MediaType(CoversClient::TYPE_COVERS_LIST, 1)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return new ArraySequence(array_map(function (array $cover) {
                    return $this->denormalizer->denormalize($cover, Cover::class);
                }, $result['items']));
            }));
    }

    public function getCurrent() : Sequence
    {
        return new PromiseSequence($this->coversClient
            ->listCurrentCovers(['Accept' => new MediaType(CoversClient::TYPE_COVERS_LIST, 1)])
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return new ArraySequence(array_map(function (array $cover) {
                    return $this->denormalizer->denormalize($cover, Cover::class);
                }, $result['items']));
            }));
    }

    public function reverse() : Sequence
    {
        $clone = clone $this;

        $clone->descendingOrder = !$this->descendingOrder;

        return $clone;
    }
}
