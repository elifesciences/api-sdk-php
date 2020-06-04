<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\PromotionalCollectionsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\PromotionalCollection;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class PromotionalCollections implements Iterator, Sequence
{
    const VERSION_PROMOTIONAL_COLLECTION = 1;
    const VERSION_PROMOTIONAL_COLLECTION_LIST = 1;

    use Client;
    use Contains;
    use ForSubject;

    private $count;
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $promotionalCollectionsClient;
    private $denormalizer;

    public function __construct(PromotionalCollectionsClient $promotionalCollectionsClient, DenormalizerInterface $denormalizer)
    {
        $this->promotionalCollectionsClient = $promotionalCollectionsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->promotionalCollectionsClient
            ->getPromotionalCollection(
                ['Accept' => (string) new MediaType(PromotionalCollectionsClient::TYPE_PROMOTIONAL_COLLECTION, self::VERSION_PROMOTIONAL_COLLECTION)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), PromotionalCollection::class);
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

        return new PromiseSequence($this->promotionalCollectionsClient
            ->listPromotionalCollections(
                ['Accept' => (string) new MediaType(PromotionalCollectionsClient::TYPE_PROMOTIONAL_COLLECTION_LIST, self::VERSION_PROMOTIONAL_COLLECTION_LIST)],
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
                return array_map(function (array $promotionalCollection) {
                    return $this->denormalizer->denormalize($promotionalCollection, PromotionalCollection::class, null, ['snippet' => true]);
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
