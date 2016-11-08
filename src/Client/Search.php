<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\SearchClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\SearchSubjects;
use eLife\ApiSdk\Model\SearchTypes;
use eLife\ApiSdk\SlicedArrayAccess;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class Search implements Iterator, Sequence
{
    use ArrayFromIterator;
    use SlicedArrayAccess;
    use SlicedIterator {
        SlicedIterator::getPage insteadof SlicedArrayAccess;
        SlicedIterator::isEmpty insteadof SlicedArrayAccess;
        SlicedIterator::resetPages insteadof SlicedArrayAccess;
    }

    // collaborators
    private $searchClient;
    private $denormalizer;

    // inputs
    private $query = '';
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $typesQuery = [];
    private $sort = 'relevance';

    // cached outputs
    private $count;
    private $items = [];
    /**
     * @var PromiseInterface
     */
    private $types;
    /**
     * @var PromiseInterface
     */
    private $subjects;

    public function __construct(SearchClient $searchClient, DenormalizerInterface $denormalizer)
    {
        $this->searchClient = $searchClient;
        $this->denormalizer = $denormalizer;
    }

    public function __clone()
    {
        $this->resetIterator();
    }

    public function forQuery(string $query)
    {
        $clone = clone $this;

        $clone->query = $query;

        $clone->invalidateDataIfDifferent('query', $this);

        return $clone;
    }

    public function forSubject(string ...$subjectId) : self
    {
        $clone = clone $this;

        $clone->subjectsQuery = array_unique(array_merge($this->subjectsQuery, $subjectId));

        $clone->invalidateDataIfDifferent('subjectsQuery', $this);

        return $clone;
    }

    public function forType(string ...$type) : self
    {
        $clone = clone $this;

        $clone->typesQuery = array_unique(array_merge($this->typesQuery, $type));

        $clone->invalidateDataIfDifferent('typesQuery', $this);

        return $clone;
    }

    /**
     * @param string $sort 'relevance' or 'date'?
     */
    public function sortBy(string $sort) : self
    {
        $clone = clone $this;

        $clone->sort = $sort;

        $clone->invalidateDataIfDifferent('sort', $this);

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

        $resultPromise = $this->searchClient
            ->query(
                ['Accept' => new MediaType(SearchClient::TYPE_SEARCH, 1)],
                $this->query,
                ($offset / $length) + 1,
                $length,
                $this->sort,
                $this->descendingOrder,
                $this->subjectsQuery,
                $this->typesQuery
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            });

        $this->types = $resultPromise
            ->then(function ($result) {
                return new SearchTypes($result['types']);
            });

        $this->subjects = $resultPromise
            ->then(function ($result) {
                return $this->denormalizer->denormalize($result['subjects'], SearchSubjects::class);
            });

        return new PromiseSequence($resultPromise
            ->then(function (Result $result) {
                $items = [];

                foreach ($result['items'] as $item) {
                    $key = $this->keyFor($item);
                    if (isset($this->items[$key])) {
                        $items[] = $this->items[$key]->wait();
                    } else {
                        $items[] = $model = $this->denormalizer->denormalize($item, Model::class, null, ['snippet' => true]);
                        $this->items[$key] = promise_for($model);
                    }
                }

                return new ArraySequence($items);
            })
        );

        return $sequencePromise;
    }

    private function keyFor(array $item)
    {
        return
            $item['type']
            .(
                isset($item['status'])
                ? '-'.$item['status']
                : ''
            )
            .'::'
            .(
                isset($item['id'])
                ? $item['id']
                : $item['number']
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

    public function types() : SearchTypes
    {
        if (null === $this->types) {
            $this->slice(0, 1);
        }

        return $this->types->wait();
    }

    public function subjects() : SearchSubjects
    {
        if (null === $this->subjects) {
            $this->slice(0, 1);
        }

        return $this->subjects->wait();
    }

    private function invalidateDataIfDifferent(string $field, self $another)
    {
        if ($this->$field !== $another->$field) {
            $this->count = null;
            $this->types = null;
            $this->subjects = null;
            $this->items = [];
        }
    }
}
