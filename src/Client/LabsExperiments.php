<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\LabsExperiment;
use eLife\ApiSdk\Promise\CallbackPromise;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class LabsExperiments implements Iterator, Sequence
{
    use ArrayFromIterator;
    use SlicedIterator;

    private $count;
    private $experiments;
    private $descendingOrder = true;
    private $labsClient;
    private $denormalizer;

    public function __construct(LabsClient $labsClient, DenormalizerInterface $denormalizer)
    {
        $this->experiments = new ArrayObject();
        $this->labsClient = $labsClient;
        $this->denormalizer = $denormalizer;
    }

    public function __clone()
    {
        $this->resetIterator();
    }

    public function get(int $number) : PromiseInterface
    {
        if (isset($this->experiments[$number])) {
            return $this->experiments[$number];
        }

        return $this->experiments[$number] = $this->labsClient
            ->getExperiment(
                ['Accept' => new MediaType(LabsClient::TYPE_EXPERIMENT, 1)],
                $number
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), LabsExperiment::class);
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

        return new PromiseSequence($this->labsClient
            ->listExperiments(
                ['Accept' => new MediaType(LabsClient::TYPE_EXPERIMENT_LIST, 1)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                $experiments = [];

                $fullPromise = new CallbackPromise(function () use ($result) {
                    $promises = [];
                    foreach ($result['items'] as $experiment) {
                        $promises[$experiment['number']] = $this->labsClient->getExperiment(
                            ['Accept' => new MediaType(LabsClient::TYPE_EXPERIMENT, 1)],
                            $experiment['number']
                        );
                    }

                    return $promises;
                });

                foreach ($result['items'] as $experiment) {
                    if (isset($this->experiments[$experiment['number']])) {
                        $experiments[] = $this->experiments[$experiment['number']]->wait();
                    } else {
                        $experiment['content'] = $fullPromise
                            ->then(function (array $promises) use ($experiment) {
                                return $promises[$experiment['number']]->wait()['content'];
                            });

                        $experiments[] = $experiment = $this->denormalizer
                            ->denormalize($experiment, LabsExperiment::class);
                        $this->experiments[$experiment->getNumber()] = promise_for($experiment);
                    }
                }

                return new ArraySequence($experiments);
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
