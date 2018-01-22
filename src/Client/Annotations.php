<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\AnnotationsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Annotation;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Annotations
{
    private $annotationsClient;
    private $denormalizer;

    public function __construct(AnnotationsClient $annotationsClient, DenormalizerInterface $denormalizer)
    {
        $this->annotationsClient = $annotationsClient;
        $this->denormalizer = $denormalizer;
    }

    public function list(string $by) : Sequence
    {
        $annotationsClient = $this->annotationsClient;
        $denormalizer = $this->denormalizer;

        return new class($annotationsClient, $denormalizer, $by) implements Iterator, Sequence {
            use Client;

            private $count;
            private $descendingOrder = true;
            private $useDate = 'updated';
            private $annotationsClient;
            private $denormalizer;
            private $by;

            public function __construct(AnnotationsClient $annotationsClient, DenormalizerInterface $denormalizer, string $by)
            {
                $this->annotationsClient = $annotationsClient;
                $this->denormalizer = $denormalizer;
                $this->by = $by;
            }

            /**
             * @param string $useDate 'updated' or 'created'
             */
            public function useDate(string $useDate) : Sequence
            {
                $clone = clone $this;

                $clone->useDate = $useDate;

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

                return new PromiseSequence($this->annotationsClient
                    ->listAnnotations(
                        ['Accept' => new MediaType(AnnotationsClient::TYPE_ANNOTATION_LIST, 1)],
                        $this->by,
                        ($offset / $length) + 1,
                        $length,
                        $this->descendingOrder,
                        $this->useDate
                    )
                    ->then(function (Result $result) {
                        $this->count = $result['total'];

                        return $result;
                    })
                    ->then(function (Result $result) {
                        return new ArraySequence(array_map(function (array $annotation) {
                            return $this->denormalizer->denormalize($annotation, Annotation::class);
                        }, $result['items']));
                    }));
            }

            public function reverse() : Sequence
            {
                $clone = clone $this;

                $clone->descendingOrder = !$this->descendingOrder;

                return $clone;
            }
        };
    }
}
