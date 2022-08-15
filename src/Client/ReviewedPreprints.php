<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\ReviewedPreprintClient;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ReviewedPreprint;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ReviewedPreprints implements Iterator, Sequence
{
    use Client;

    private $reviewedPreprintClient;
    private $denormalizer;
    private $descendingOrder;

    public function __construct(ReviewedPreprintClient $reviewedPreprintClient, DenormalizerInterface $denormalizer)
    {
        $this->reviewedPreprintClient = $reviewedPreprintClient;
        $this->denormalizer = $denormalizer;
    }

    public function slice(int $offset, int $length = null): Sequence
    {

        return new Collection\PromiseSequence($this->reviewedPreprintClient
            ->listReviewedPreprint()
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