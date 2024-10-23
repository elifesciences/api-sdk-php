<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiClient\ProfilesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Profile;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Profiles implements Iterator, Sequence
{
    const VERSION_PROFILE = 1;
    const VERSION_PROFILE_LIST = 1;

    use Client;

    private $count;
    private $descendingOrder = true;
    private $profilesClient;
    private $denormalizer;

    public function __construct(ProfilesClient $profilesClient, DenormalizerInterface $denormalizer)
    {
        $this->profilesClient = $profilesClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->profilesClient
            ->getProfile(
                ['Accept' => (string) new MediaType(ProfilesClient::TYPE_PROFILE, self::VERSION_PROFILE)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), Profile::class);
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

        return new PromiseSequence($this->profilesClient
            ->listProfiles(
                ['Accept' => (string) new MediaType(ProfilesClient::TYPE_PROFILE_LIST, self::VERSION_PROFILE_LIST)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                return array_map(function (array $profile) {
                    return $this->denormalizer->denormalize($profile, Profile::class);
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
