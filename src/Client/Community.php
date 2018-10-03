<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\CommunityClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Model;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Community implements Iterator, Sequence
{
    const VERSION_COMMUNITY_LIST = 1;

    use Client;

    private $count;
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $communityClient;
    private $denormalizer;

    public function __construct(CommunityClient $communityClient, DenormalizerInterface $denormalizer)
    {
        $this->communityClient = $communityClient;
        $this->denormalizer = $denormalizer;
    }

    public function forSubject(string ...$subjectId) : Community
    {
        $clone = clone $this;

        $clone->subjectsQuery = array_unique(array_merge($this->subjectsQuery, $subjectId));

        if ($clone->subjectsQuery !== $this->subjectsQuery) {
            $clone->count = null;
        }

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

        return new PromiseSequence($this->communityClient
            ->listContent(
                ['Accept' => new MediaType(CommunityClient::TYPE_COMMUNITY_LIST, self::VERSION_COMMUNITY_LIST)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder,
                $this->subjectsQuery
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
}
