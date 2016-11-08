<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\LabsExperiment;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class LabsExperiments implements Iterator, Sequence
{
    use Client;

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

                foreach ($result['items'] as $experiment) {
                    if (isset($this->experiments[$experiment['number']])) {
                        $experiments[] = $this->experiments[$experiment['number']]->wait();
                    } else {
                        $experiments[] = $experiment = $this->denormalizer->denormalize($experiment,
                            LabsExperiment::class, null, ['snippet' => true]);
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
}
