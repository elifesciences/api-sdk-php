<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\PeopleClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Person;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class People implements Iterator, Sequence
{
    use Client;

    private $count;
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $typeQuery;
    private $peopleClient;
    private $denormalizer;

    public function __construct(PeopleClient $peopleClient, DenormalizerInterface $denormalizer)
    {
        $this->peopleClient = $peopleClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->peopleClient
            ->getPerson(
                ['Accept' => new MediaType(PeopleClient::TYPE_PERSON, 1)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), Person::class);
            });
    }

    public function forSubject(string ...$subjectId) : People
    {
        $clone = clone $this;

        $clone->subjectsQuery = array_unique(array_merge($this->subjectsQuery, $subjectId));

        if ($clone->subjectsQuery !== $this->subjectsQuery) {
            $clone->count = null;
        }

        return $clone;
    }

    public function forType(string $type) : People
    {
        $clone = clone $this;

        $clone->typeQuery = $type;

        if ($clone->typeQuery !== $this->typeQuery) {
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

        return new PromiseSequence($this->peopleClient
            ->listPeople(
                ['Accept' => new MediaType(PeopleClient::TYPE_PERSON_LIST, 1)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder,
                $this->subjectsQuery,
                $this->typeQuery
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $person) {
                    return $this->denormalizer->denormalize($person, Person::class, null, ['snippet' => true]);
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
