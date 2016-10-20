<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Promise\CallbackPromise;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class Interviews implements Iterator, Sequence
{
    use ArrayFromIterator;
    use SlicedIterator;

    private $count;
    private $interviews;
    private $descendingOrder = true;
    private $interviewsClient;
    private $denormalizer;

    public function __construct(InterviewsClient $interviewsClient, DenormalizerInterface $denormalizer)
    {
        $this->interviews = new ArrayObject();
        $this->interviewsClient = $interviewsClient;
        $this->denormalizer = $denormalizer;
    }

    public function __clone()
    {
        $this->resetIterator();
    }

    public function get(string $id) : PromiseInterface
    {
        if (isset($this->interviews[$id])) {
            return $this->interviews[$id];
        }

        return $this->interviews[$id] = $this->interviewsClient
            ->getInterview(
                ['Accept' => new MediaType(InterviewsClient::TYPE_INTERVIEW, 1)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), Interview::class);
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

        return new PromiseSequence($this->interviewsClient
            ->listInterviews(
                ['Accept' => new MediaType(InterviewsClient::TYPE_INTERVIEW_LIST, 1)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                $interviews = [];

                $fullPromise = new CallbackPromise(function () use ($result) {
                    $promises = [];
                    foreach ($result['items'] as $interview) {
                        $promises[$interview['id']] = $this->interviewsClient->getInterview(
                            ['Accept' => new MediaType(InterviewsClient::TYPE_INTERVIEW, 1)],
                            $interview['id']
                        );
                    }

                    return $promises;
                });

                foreach ($result['items'] as $interview) {
                    if (isset($this->interviews[$interview['id']])) {
                        $interviews[] = $this->interviews[$interview['id']]->wait();
                    } else {
                        $interviews[] = $interview = $this->denormalizer->denormalize($interview, Interview::class,
                            null, ['snippet' => true]);
                        $this->interviews[$interview->getId()] = promise_for($interview);
                    }
                }

                return new ArraySequence($interviews);
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
