<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\PodcastEpisode;
use eLife\ApiSdk\SlicedArrayAccess;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class PodcastEpisodes implements Iterator, Sequence
{
    use ArrayFromIterator;
    use SlicedArrayAccess;
    use SlicedIterator {
        SlicedIterator::getPage insteadof SlicedArrayAccess;
        SlicedIterator::isEmpty insteadof SlicedArrayAccess;
        SlicedIterator::resetPages insteadof SlicedArrayAccess;
    }

    private $count;
    private $episodes;
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $podcastClient;
    private $denormalizer;

    public function __construct(PodcastClient $podcastClient, DenormalizerInterface $denormalizer)
    {
        $this->episodes = new ArrayObject();
        $this->podcastClient = $podcastClient;
        $this->denormalizer = $denormalizer;
    }

    public function __clone()
    {
        $this->resetIterator();
    }

    public function get(int $number) : PromiseInterface
    {
        if (isset($this->episodes[$number])) {
            return $this->episodes[$number];
        }

        return $this->episodes[$number] = $this->podcastClient
            ->getEpisode(
                ['Accept' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE, 1)],
                $number
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), PodcastEpisode::class);
            });
    }

    public function forSubject(string ...$subjectId) : PodcastEpisodes
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

        return new PromiseSequence($this->podcastClient
            ->listEpisodes(
                ['Accept' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE_LIST, 1)],
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
                $episodes = [];

                foreach ($result['items'] as $episode) {
                    if (isset($this->episodes[$episode['number']])) {
                        $episodes[] = $this->episodes[$episode['number']]->wait();
                    } else {
                        $episodes[] = $episode = $this->denormalizer->denormalize($episode, PodcastEpisode::class,
                            null, ['snippet' => true]);
                        $this->episodes[$episode->getNumber()] = promise_for($episode);
                    }
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

    public function count() : int
    {
        if (null === $this->count) {
            $this->slice(0, 1)->count();
        }

        return $this->count;
    }
}
