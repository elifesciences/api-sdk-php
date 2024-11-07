<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiClient\InterviewsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Interview;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Interviews implements Iterator, Sequence
{
    const VERSION_INTERVIEW = 2;
    const VERSION_INTERVIEW_LIST = 1;

    use Client;

    private $count;
    private $descendingOrder = true;
    private $interviewsClient;
    private $denormalizer;

    public function __construct(InterviewsClient $interviewsClient, DenormalizerInterface $denormalizer)
    {
        $this->interviewsClient = $interviewsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->interviewsClient
            ->getInterview(
                ['Accept' => (string) new MediaType(InterviewsClient::TYPE_INTERVIEW, self::VERSION_INTERVIEW)],
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
                ['Accept' => (string) new MediaType(InterviewsClient::TYPE_INTERVIEW_LIST, self::VERSION_INTERVIEW_LIST)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $interview) {
                    return $this->denormalizer->denormalize($interview, Interview::class, null, ['snippet' => true]);
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
