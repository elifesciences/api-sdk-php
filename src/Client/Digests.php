<?php

namespace eLife\ApiSdk\Client;

use function array_map;
use eLife\ApiClient\ApiClient\DigestsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Digest;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Digests implements Iterator, Sequence
{
    const VERSION_DIGEST = 2;
    const VERSION_DIGEST_LIST = 1;

    use Client;

    private $count;
    private $descendingOrder = true;
    private $digestsClient;
    private $denormalizer;

    public function __construct(DigestsClient $digestsClient, DenormalizerInterface $denormalizer)
    {
        $this->digestsClient = $digestsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->digestsClient
            ->getDigest(
                ['Accept' => (string) new MediaType(DigestsClient::TYPE_DIGEST, self::VERSION_DIGEST)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), Digest::class);
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

        return new PromiseSequence($this->digestsClient
            ->listDigests(
                ['Accept' => (string) new MediaType(DigestsClient::TYPE_DIGEST_LIST, self::VERSION_DIGEST_LIST)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $digest) {
                    return $this->denormalizer->denormalize($digest, Digest::class, null, ['snippet' => true]);
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
