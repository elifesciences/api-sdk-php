<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\MediumClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\MediumArticle;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class MediumArticles implements Iterator, Sequence
{
    use Client;

    private $count;
    private $descendingOrder = true;
    private $mediumArticlesClient;
    private $denormalizer;

    public function __construct(MediumClient $mediumArticlesClient, DenormalizerInterface $denormalizer)
    {
        $this->mediumArticlesClient = $mediumArticlesClient;
        $this->denormalizer = $denormalizer;
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

        return new PromiseSequence($this->mediumArticlesClient
            ->listArticles(
                ['Accept' => new MediaType(MediumClient::TYPE_MEDIUM_ARTICLE_LIST, 1)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $mediumArticle) {
                    return $this->denormalizer->denormalize($mediumArticle, MediumArticle::class);
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
