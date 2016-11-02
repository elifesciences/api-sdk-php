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
use eLife\ApiSdk\SlicedIterator;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class Search implements Iterator, Sequence
{
    use ArrayFromIterator;
    use SlicedIterator;

    private $count;
    private $collections = [];
    private $query = '';
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $typesQuery = [];
    private $sort = 'relevance';
    private $searchClient;
    private $denormalizer;
    private $results = [];

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

        return new PromiseSequence($this->searchClient
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
            })
            ->then(function (Result $result) {
                $results = [];

                foreach ($result['items'] as $searchResult) {
                    $key = $this->keyFor($searchResult);
                    if (isset($this->results[$key])) {
                        $results[] = $this->results[$key]->wait();
                    } else {
                        $results[] = $model = $this->denormalizer->denormalize($searchResult, Model::class, null, ['snippet' => true]);
                        $this->results[$key] = promise_for($model);
                    }
                }

                return new ArraySequence($results);
            })
        );
    }

    private function keyFor(array $searchResult)
    {
        return
            $searchResult['type']
            .(
                isset($searchResult['status'])
                ? '-'.$searchResult['status']
                : ''
            )
            .'::'
            .(
                isset($searchResult['id'])
                ? $searchResult['id']
                : $searchResult['number']
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

    private function invalidateDataIfDifferent(string $field, self $another)
    {
        if ($this->$field !== $another->$field) {
            $this->count = null;
        }
    }
}
