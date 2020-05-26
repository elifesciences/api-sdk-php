<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\HighlightsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Highlight;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Highlights
{
    const VERSION_HIGHLIGHT_LIST = 4;

    private $highlightsClient;
    private $denormalizer;

    public function __construct(HighlightsClient $highlightsClient, DenormalizerInterface $denormalizer)
    {
        $this->highlightsClient = $highlightsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : Sequence
    {
        $highlightsClient = $this->highlightsClient;
        $denormalizer = $this->denormalizer;

        return new class($highlightsClient, $denormalizer, $id) implements Iterator, Sequence {
            use Client;

            private $count;
            private $descendingOrder = true;
            private $highlightsClient;
            private $denormalizer;
            private $id;

            public function __construct(HighlightsClient $highlightsClient, DenormalizerInterface $denormalizer, string $id)
            {
                $this->highlightsClient = $highlightsClient;
                $this->denormalizer = $denormalizer;
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

                return new PromiseSequence($this->highlightsClient
                    ->listHighlights(
                        ['Accept' => (string) new MediaType(HighlightsClient::TYPE_HIGHLIGHT_LIST, Highlights::VERSION_HIGHLIGHT_LIST)],
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
                            return $this->denormalizer->denormalize($article, Highlight::class);
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
