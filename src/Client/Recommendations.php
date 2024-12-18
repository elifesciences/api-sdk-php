<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiClient\RecommendationsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Model;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Recommendations
{
    const VERSION_RECOMMENDATIONS = 3;

    private $recommendationsClient;
    private $denormalizer;

    public function __construct(RecommendationsClient $recommendationsClient, DenormalizerInterface $denormalizer)
    {
        $this->recommendationsClient = $recommendationsClient;
        $this->denormalizer = $denormalizer;
    }

    public function list(Identifier $identifier) : Sequence
    {
        $recommendationsClient = $this->recommendationsClient;
        $denormalizer = $this->denormalizer;

        return new class($recommendationsClient, $denormalizer, $identifier->getType(), $identifier->getId()) implements Iterator, Sequence {
            use Client;

            private $count;
            private $descendingOrder = true;
            private $recommendationsClient;
            private $denormalizer;
            private $type;
            private $id;

            public function __construct(RecommendationsClient $recommendationsClient, DenormalizerInterface $denormalizer, string $type, $id)
            {
                $this->recommendationsClient = $recommendationsClient;
                $this->denormalizer = $denormalizer;
                $this->type = $type;
                $this->id = $id;
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

                return new PromiseSequence($this->recommendationsClient
                    ->list(
                        ['Accept' => (string) new MediaType(RecommendationsClient::TYPE_RECOMMENDATIONS, Recommendations::VERSION_RECOMMENDATIONS)],
                        $this->type,
                        $this->id,
                        ($offset / $length) + 1,
                        $length,
                        $this->descendingOrder
                    )
                    ->then(function (Result $result) {
                        $this->count = $result['total'];

                        return $result;
                    })
                    ->then(function (Result $result) {
                        return array_map(function (array $article) {
                            return $this->denormalizer->denormalize($article, Model::class, null, ['snippet' => true]);
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
        };
    }
}
