<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\SearchClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
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
    private $searchClient;
    private $denormalizer;
    
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

        if ($clone->query !== $this->query) {
            $clone->count = null;
        }

        return $clone;
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

        return new PromiseSequence($this->searchClient
            ->query(
                ['Accept' => new MediaType(SearchClient::TYPE_SEARCH, 1)],
                $this->query,
                ($offset / $length) + 1,
                $length,
                $sort = 'relevance',
                $this->descendingOrder,
                $this->subjectsQuery,
                $types = []
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                $results = [];

                foreach ($result['items'] as $searchResult) {
                    $results[] = $model = $this->denormalizer->denormalize($searchResult, Model::class, null, ['snippet' => true]);
                    $this->results[$model->getId()] = promise_for($model);
                }

                return new ArraySequence($results);
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
