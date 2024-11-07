<?php

namespace eLife\ApiSdk\Client;

use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\ReviewedPreprintsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ReviewedPreprint;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ReviewedPreprints implements Iterator, Sequence
{
    const VERSION_REVIEWED_PREPRINT = 1;
    const VERSION_REVIEWED_PREPRINT_LIST = 1;

    use Client;

    private $reviewedPreprintsClient;
    private $denormalizer;
    private $descendingOrder = true;
    private $useDate = 'default';
    private $startDate;
    private $endDate;

    public function __construct(ReviewedPreprintsClient $reviewedPreprintsClient, DenormalizerInterface $denormalizer)
    {
        $this->reviewedPreprintsClient = $reviewedPreprintsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->reviewedPreprintsClient
            ->getReviewedPreprint(
                ['Accept' => (string) new MediaType(ReviewedPreprintsClient::TYPE_REVIEWED_PREPRINT, self::VERSION_REVIEWED_PREPRINT)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), ReviewedPreprint::class);
            });
    }

    public function slice(int $offset, int $length = null): Sequence
    {
        if (null === $length) {
            return new PromiseSequence($this->all()
                ->then(function (Sequence $sequence) use ($offset) {
                    return $sequence->slice($offset);
                })
            );
        }

        return new Collection\PromiseSequence($this->reviewedPreprintsClient
            ->listReviewedPreprints(
                ['Accept' => (string) new MediaType(ReviewedPreprintsClient::TYPE_REVIEWED_PREPRINT_LIST,
                    self::VERSION_REVIEWED_PREPRINT_LIST)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder,
                $this->useDate,
                $this->startDate,
                $this->endDate
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })->then(function (Result $result) {
                return array_map(function (array $article) {
                    return $this->denormalizer->denormalize($article, ReviewedPreprint::class, null, ['snippet' => true]);
                }, $result['items']);
            })
        );
    }

    public function reverse(): Sequence
    {
        $clone = clone $this;

        $clone->descendingOrder = !$this->descendingOrder;

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

    private function invalidateDataIfDifferent(string $field, self $another)
    {
        if ($this->$field != $another->$field) {
            $this->count = null;
        }
    }
}
