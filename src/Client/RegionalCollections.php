<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\RegionalCollectionsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\RegionalCollection;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class RegionalCollections implements Iterator, Sequence
{
    const VERSION_REGIONAL_COLLECTION = 1;
    const VERSION_REGIONAL_COLLECTION_LIST = 1;

    use Client;
    use Contains;

    private $count;
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $regionalCollectionsClient;
    private $denormalizer;

    public function __construct(RegionalCollectionsClient $regionalCollectionsClient, DenormalizerInterface $denormalizer)
    {
        $this->regionalCollectionsClient = $regionalCollectionsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->regionalCollectionsClient
            ->getRegionalCollection(
                ['Accept' => (string) new MediaType(RegionalCollectionsClient::TYPE_REGIONAL_COLLECTION, self::VERSION_REGIONAL_COLLECTION)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), RegionalCollection::class);
            });
    }

    public function forSubject(string ...$subjectId) : RegionalCollections
    {
        $clone = clone $this;

        $clone->subjectsQuery = array_unique(array_merge($this->subjectsQuery, $subjectId));

        if ($clone->subjectsQuery !== $this->subjectsQuery) {
            $clone->count = null;
        }

        return $clone;
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

        return new PromiseSequence($this->regionalCollectionsClient
            ->listRegionalCollections(
                ['Accept' => (string) new MediaType(RegionalCollectionsClient::TYPE_REGIONAL_COLLECTION_LIST, self::VERSION_REGIONAL_COLLECTION_LIST)],
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
                return array_map(function (array $regionalCollection) {
                    return $this->denormalizer->denormalize($regionalCollection, RegionalCollection::class, null, ['snippet' => true]);
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
}
