<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\JobAdvertsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\JobAdvert;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class JobAdverts implements Iterator, Sequence
{
    use Client;

    private $count;
    private $descendingOrder = true;
    private $show = 'all';
    private $jobAdvertsClient;
    private $denormalizer;

    public function __construct(JobAdvertsClient $jobAdvertsClient, DenormalizerInterface $denormalizer)
    {
        $this->jobAdvertsClient = $jobAdvertsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->jobAdvertsClient
            ->getJobAdvert(
                ['Accept' => new MediaType(JobAdvertsClient::TYPE_JOB_ADVERT, 1)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), JobAdvert::class);
            });
    }

    public function show(string $show) : JobAdverts
    {
        $clone = clone $this;

        $clone->show = $show;

        if ($clone->show !== $this->show) {
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

        return new PromiseSequence($this->jobAdvertsClient
            ->listJobAdverts(
                ['Accept' => new MediaType(JobAdvertsClient::TYPE_JOB_ADVERT_LIST, 1)],
                ($offset / $length) + 1,
                $length,
                $this->show,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $jobAdvert) {
                    return $this->denormalizer->denormalize($jobAdvert, JobAdvert::class, null, ['snippet' => true]);
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
