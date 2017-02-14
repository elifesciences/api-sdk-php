<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\HighlightsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Highlight;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Highlights
{
    private $highlightsClient;
    private $denormalizer;

    public function __construct(HighlightsClient $highlightsClient, DenormalizerInterface $denormalizer)
    {
        $this->highlightsClient = $highlightsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : Sequence
    {
        return new PromiseSequence($this->highlightsClient
            ->list(
                ['Accept' => new MediaType(HighlightsClient::TYPE_HIGHLIGHTS, 1)],
                $id
            )
            ->then(function (Result $result) {
                return array_map(function (array $item) {
                    return $this->denormalizer->denormalize($item, Highlight::class);
                }, $result->toArray());
            })
        );
    }
}
