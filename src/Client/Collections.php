<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiSdk\Model\Collection;
use GuzzleHttp\Promise\PromiseInterface;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Collections
{
    public function __construct(CollectionsClient $collectionsClient, DenormalizerInterface $denormalizer)
    {
        $this->collections = new ArrayObject();
        $this->collectionsClient = $collectionsClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        if (isset($this->collections[$id])) {
            return $this->collections[$id];
        }

        return $this->collections[$id] = $this->collectionsClient
            ->getCollection(
                ['Accept' => new MediaType(CollectionsClient::TYPE_COLLECTION, 1)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), Collection::class);
            });
    }

}
