<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\CreatesObjects;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;

final class Subjects implements Iterator, Collection
{
    use ArrayFromIterator;
    use CreatesObjects;
    use SlicedIterator;

    private $count;
    private $subjects;
    private $descendingOrder = true;
    private $subjectsClient;

    public function __construct(SubjectsClient $subjectsClient)
    {
        $this->subjects = new ArrayObject();
        $this->subjectsClient = $subjectsClient;
    }

    public function __clone()
    {
        $this->resetIterator();
    }

    public function get(string $id) : PromiseInterface
    {
        if (isset($this->subjects[$id])) {
            return $this->subjects[$id];
        }

        return $this->subjects[$id] = $this->subjectsClient
            ->getSubject(
                ['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT, 1)],
                $id
            )
            ->then(function (Result $result) {
                return $this->createSubject($result->toArray());
            });
    }

    public function slice(int $offset, int $length = null) : Collection
    {
        if (null === $length) {
            return new PromiseCollection($this->all()
                ->then(function (Collection $collection) use ($offset) {
                    return $collection->slice($offset);
                })
            );
        }

        return new PromiseCollection($this->subjectsClient
            ->listSubjects(
                ['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT_LIST, 1)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                $subjects = [];

                foreach ($result['items'] as $subject) {
                    if (isset($this->subjects[$subject['id']])) {
                        $subjects[] = $this->subjects[$subject['id']]->wait();
                    } else {
                        $subjects[] = $subject = $this->createSubject($subject);
                        $this->subjects[$subject->getId()] = new FulfilledPromise($subject);
                    }
                }

                return new ArrayCollection($subjects);
            })
        );
    }

    public function reverse() : Collection
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
