<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\SlicedArrayAccess;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class Collections implements Iterator, Sequence
{
    use ArrayFromIterator;
    use SlicedArrayAccess;
    use SlicedIterator {
        SlicedIterator::getPage insteadof SlicedArrayAccess;
        SlicedIterator::isEmpty insteadof SlicedArrayAccess;
        SlicedIterator::resetPages insteadof SlicedArrayAccess;
    }

    private $count;
    private $collections = [];
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $collectionsClient;
    private $denormalizer;

    public function __construct(CollectionsClient $collectionsClient, DenormalizerInterface $denormalizer)
    {
        $this->collections = new ArrayObject();
        $this->collectionsClient = $collectionsClient;
        $this->denormalizer = $denormalizer;
    }

    public function __clone()
    {
        $this->resetIterator();
    }

    public function get(string $id) : PromiseInterface
    {
        if (isset($this->collections[$id])) {
            return $this->collections[$id];
        }

        return $this->collections[$id] = $this->collectionsClient
            ->getCollection(
                ['Accept' => new MediaType(CollectionsClient::TYPE_COLLECTION, 1)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), Collection::class);
            });
    }

    public function forSubject(string ...$subjectId) : self
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

        return new PromiseSequence($this->collectionsClient
            ->listCollections(
                ['Accept' => new MediaType(CollectionsClient::TYPE_COLLECTION_LIST, 1)],
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
                $collections = [];

                foreach ($result['items'] as $collection) {
                    if (isset($this->collections[$collection['id']])) {
                        $collections[] = $this->collections[$collection['id']]->wait();
                    } else {
                        $collections[] = $collection = $this->denormalizer->denormalize($collection, Collection::class,
                            null, ['snippet' => true]);
                        $this->collections[$collection->getId()] = promise_for($collection);
                    }
                }

                return new ArraySequence($collections);
            })
        );
    }

    public function reverse() : Sequence
    {
        $clone = clone $this;

        $clone->descendingOrder = !$this->descendingOrder;

        return $clone;
    }

    public function count() : int
    {
        if (null === $this->count) {
            $this->slice(0, 1)->count();
        }

        return $this->count;
    }
}
