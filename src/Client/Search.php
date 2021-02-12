<?php

namespace eLife\ApiSdk\Client;

use DateTimeImmutable;
use eLife\ApiClient\ApiClient\SearchClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\SearchSubjects;
use eLife\ApiSdk\Model\SearchTypes;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Search implements Iterator, Sequence
{
    use Client;
    use ForSubject;

    const VERSION_SEARCH = 1;

    // collaborators
    private $searchClient;
    private $denormalizer;

    // inputs
    private $query = '';
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $typesQuery = [];
    private $sort = 'relevance';
    private $useDate = 'default';
    private $startDate;
    private $endDate;

    // cached outputs
    private $count;
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

    public function forType(string ...$type) : self
    {
        $clone = clone $this;

        $clone->typesQuery = array_unique(array_merge($this->typesQuery, $type));

        $clone->invalidateDataIfDifferent('typesQuery', $this);

        return $clone;
    }

    public function useDate(string $useDate) : self
    {
        $clone = clone $this;

        $clone->useDate = $useDate;

        return $clone;
    }

    public function startDate(DateTimeImmutable $startDate = null) : self
    {
        $clone = clone $this;

        $clone->startDate = $startDate;

        $clone->invalidateDataIfDifferent('startDate', $this);

        return $clone;
    }

    public function endDate(DateTimeImmutable $endDate = null) : self
    {
        $clone = clone $this;

        $clone->endDate = $endDate;

        $clone->invalidateDataIfDifferent('endDate', $this);

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
                ['Accept' => (string) new MediaType(SearchClient::TYPE_SEARCH, self::VERSION_SEARCH)],
                $this->query,
                ($offset / $length) + 1,
                $length,
                $this->sort,
                $this->descendingOrder,
                $this->subjectsQuery,
                $this->typesQuery,
                $this->useDate,
                $this->startDate,
                $this->endDate
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
                return array_map(function (array $item) {
                    return $this->denormalizer->denormalize($item, Model::class, null, ['snippet' => true]);
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
        if ($this->$field != $another->$field) {
            $this->invalidateData();
        }
    }

    protected function invalidateData()
    {
        $this->count = null;
        $this->types = null;
        $this->subjects = null;
    }
}
