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
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $searchClient;
    private $denormalizer;
    
    public function __construct(SearchClient $searchClient, DenormalizerInterface $denormalizer)
    {
        $this->searchClient = $searchClient;
    }

    public function __clone()
    {
        $this->resetIterator();
    }

    public function get()
    {
        
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
                $query = '',
                ($offset / $length) + 1,
                $length,
                $sort = 'relevance',
                $this->descendingOrder,
                $subjects = [],
                $types = []
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                $results = [];

                foreach ($result['items'] as $searchResult) {
                    /*
                    if (isset($this->collections[$collection['id']])) {
                        $collections[] = $this->collections[$collection['id']]->wait();
                    } else {
                        $collections[] = $collection = $this->denormalizer->denormalize($collection, Collection::class,
                            null, ['snippet' => true]);
                        $this->collections[$collection->getId()] = promise_for($collection);
                    }
                     */
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
