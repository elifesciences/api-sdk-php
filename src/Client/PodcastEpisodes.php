<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\PodcastEpisode;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class PodcastEpisodes implements Iterator, Sequence
{
    const VERSION_PODCAST_EPISODE = 1;
    const VERSION_PODCAST_EPISODE_LIST = 1;

    use Client;
    use Contains;

    private $count;
    private $descendingOrder = true;
    private $containingQuery = [];
    private $podcastClient;
    private $denormalizer;

    public function __construct(PodcastClient $podcastClient, DenormalizerInterface $denormalizer)
    {
        $this->podcastClient = $podcastClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(int $number) : PromiseInterface
    {
        return $this->podcastClient
            ->getEpisode(
                ['Accept' => (string) new MediaType(PodcastClient::TYPE_PODCAST_EPISODE, self::VERSION_PODCAST_EPISODE)],
                $number
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), PodcastEpisode::class);
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

        return new PromiseSequence($this->podcastClient
            ->listEpisodes(
                ['Accept' => (string) new MediaType(PodcastClient::TYPE_PODCAST_EPISODE_LIST, self::VERSION_PODCAST_EPISODE_LIST)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder,
                $this->containingQuery
            )
            ->then(function (Result $result) {
                $this->count = $result['total'];

                return $result;
            })
            ->then(function (Result $result) {
                $episodes = [];

                foreach ($result['items'] as $episode) {
                    return array_map(function (array $episode) {
                        return $this->denormalizer->denormalize($episode, PodcastEpisode::class, null, ['snippet' => true]);
                    }, $result['items']);
                }

                return new ArraySequence($episodes);
            })
        );
    }

    public function reverse() : Sequence
    {
        $clone = clone $this;

        $clone->descendingOrder = !$this->descendingOrder;

        return $clone;
    }

    protected function invalidateDataIfDifferent(string $field, self $another)
    {
        if ($this->$field != $another->$field) {
            $this->count = null;
        }
    }
}
