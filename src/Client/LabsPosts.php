<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\LabsPost;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class LabsPosts implements Iterator, Sequence
{
    use Client;

    private $count;
    private $descendingOrder = true;
    private $labsClient;
    private $denormalizer;

    public function __construct(LabsClient $labsClient, DenormalizerInterface $denormalizer)
    {
        $this->labsClient = $labsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(int $number) : PromiseInterface
    {
        return $this->labsClient
            ->getPost(
                ['Accept' => new MediaType(LabsClient::TYPE_POST, 1)],
                $number
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), LabsPost::class);
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
            ->listPosts(
                ['Accept' => new MediaType(LabsClient::TYPE_POST_LIST, 1)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $post) {
                    return $this->denormalizer->denormalize($post, LabsPost::class, null, ['snippet' => true]);
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
