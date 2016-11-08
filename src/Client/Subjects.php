<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Subject;
use eLife\ApiSdk\SlicedArrayAccess;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class Subjects implements Iterator, Sequence
{
    use ArrayFromIterator;
    use SlicedArrayAccess;
    use SlicedIterator {
        SlicedIterator::getPage insteadof SlicedArrayAccess;
        SlicedIterator::isEmpty insteadof SlicedArrayAccess;
        SlicedIterator::resetPages insteadof SlicedArrayAccess;
    }

    private $count;
    private $subjects;
    private $descendingOrder = true;
    private $subjectsClient;
    private $denormalizer;

    public function __construct(SubjectsClient $subjectsClient, DenormalizerInterface $denormalizer)
    {
        $this->subjects = new ArrayObject();
        $this->subjectsClient = $subjectsClient;
        $this->denormalizer = $denormalizer;
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
                return $this->denormalizer->denormalize($result->toArray(), Subject::class);
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

        return new PromiseSequence($this->subjectsClient
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
                        $subjects[] = $subject = $this->denormalizer->denormalize($subject, Subject::class);
                        $this->subjects[$subject->getId()] = promise_for($subject);
                    }
                }

                return new ArraySequence($subjects);
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
