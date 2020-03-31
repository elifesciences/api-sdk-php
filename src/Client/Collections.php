<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Collection;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Collections implements Iterator, Sequence
{
    const VERSION_COLLECTION = 2;
    const VERSION_COLLECTION_LIST = 1;

    use Client;
    use Contains;
    use ForSubject;

    private $count;
    private $descendingOrder = true;
    private $collectionsClient;
    private $denormalizer;

    public function __construct(CollectionsClient $collectionsClient, DenormalizerInterface $denormalizer)
    {
        $this->collectionsClient = $collectionsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->collectionsClient
            ->getCollection(
                ['Accept' => (string) new MediaType(CollectionsClient::TYPE_COLLECTION, self::VERSION_COLLECTION)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), Collection::class);
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

        return new PromiseSequence($this->collectionsClient
            ->listCollections(
                ['Accept' => (string) new MediaType(CollectionsClient::TYPE_COLLECTION_LIST, self::VERSION_COLLECTION_LIST)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder,
                $this->subjectsQuery,
                $this->containingQuery
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $collection) {
                    return $this->denormalizer->denormalize($collection, Collection::class, null, ['snippet' => true]);
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

    protected function invalidateDataIfDifferent(string $field, self $another)
    {
        if ($this->$field != $another->$field) {
            $this->count = null;
        }
    }
}
