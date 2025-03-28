<?php

namespace eLife\ApiSdk\Client;

use DateTimeImmutable;
use eLife\ApiSdk\ApiClient\CoversClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Cover;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Covers implements Iterator, Sequence
{
    const VERSION_COVERS_LIST = 1;

    use Client;

    private $count;
    private $sort = 'date';
    private $descendingOrder = true;
    private $useDate = 'default';
    private $startDate;
    private $endDate;
    private $coversClient;
    private $denormalizer;

    public function __construct(CoversClient $coversClient, DenormalizerInterface $denormalizer)
    {
        $this->coversClient = $coversClient;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @param string $sort 'date' or 'page-views'
     */
    public function sortBy(string $sort) : self
    {
        $clone = clone $this;

        $clone->sort = $sort;

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

    public function slice(int $offset, int $length = null) : Sequence
    {
        if (null === $length) {
            return new PromiseSequence($this->all()
                ->then(function (Sequence $sequence) use ($offset) {
                    return $sequence->slice($offset);
                })
            );
        }

        return new PromiseSequence($this->coversClient
            ->listCovers(
                ['Accept' => (string) new MediaType(CoversClient::TYPE_COVERS_LIST, self::VERSION_COVERS_LIST)],
                ($offset / $length) + 1,
                $length,
                $this->sort,
                $this->descendingOrder,
                $this->useDate,
                $this->startDate,
                $this->endDate
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return new ArraySequence(array_map(function (array $cover) {
                    return $this->denormalizer->denormalize($cover, Cover::class);
                }, $result['items']));
            }));
    }

    public function getCurrent() : Sequence
    {
        return new PromiseSequence($this->coversClient
            ->listCurrentCovers(['Accept' => (string) new MediaType(CoversClient::TYPE_COVERS_LIST, self::VERSION_COVERS_LIST)])
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $cover) {
                    return $this->denormalizer->denormalize($cover, Cover::class);
                }, $result['items']);
            }));
    }

    public function reverse() : Sequence
    {
        $clone = clone $this;

        $clone->descendingOrder = !$this->descendingOrder;

        return $clone;
    }

    private function invalidateDataIfDifferent(string $field, self $another)
    {
        if ($this->$field != $another->$field) {
            $this->count = null;
        }
    }
}
