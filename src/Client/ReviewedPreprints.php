<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\ReviewedPreprintsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ReviewedPreprint;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ReviewedPreprints implements Iterator, Sequence
{
    const VERSION_REVIEWED_PREPRINT_LIST = 1;

    use Client;

    private $reviewedPreprintsClient;
    private $denormalizer;
    private $descendingOrder = true;

    public function __construct(ReviewedPreprintsClient $reviewedPreprintsClient, DenormalizerInterface $denormalizer)
    {
        $this->reviewedPreprintsClient = $reviewedPreprintsClient;
        $this->denormalizer = $denormalizer;
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
                $this->descendingOrder
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
}
