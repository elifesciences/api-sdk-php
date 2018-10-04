<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Subject;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Subjects implements Iterator, Sequence
{
    const VERSION_SUBJECT = 1;
    const VERSION_SUBJECT_LIST = 1;

    use Client;

    private $count;
    private $descendingOrder = true;
    private $subjectsClient;
    private $denormalizer;

    public function __construct(SubjectsClient $subjectsClient, DenormalizerInterface $denormalizer)
    {
        $this->subjectsClient = $subjectsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->subjectsClient
            ->getSubject(
                ['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT, self::VERSION_SUBJECT)],
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
                ['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT_LIST, self::VERSION_SUBJECT_LIST)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $subject) {
                    return $this->denormalizer->denormalize($subject, Subject::class);
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
