<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\MediumClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\MediumArticle;
use eLife\ApiSdk\SlicedIterator;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class MediumArticles implements Iterator, Collection
{
    use ArrayFromIterator;
    use SlicedIterator;

    private $count;
    private $articles;
    private $descendingOrder = true;
    private $mediumArticlesClient;
    private $denormalizer;

    public function __construct(MediumClient $mediumArticlesClient, DenormalizerInterface $denormalizer)
    {
        $this->articles = new ArrayObject();
        $this->mediumArticlesClient = $mediumArticlesClient;
        $this->denormalizer = $denormalizer;
    }

    public function __clone()
    {
        $this->resetIterator();
    }

    public function slice(int $offset, int $length = null) : Collection
    {
        if (null === $length) {
            return new PromiseCollection($this->all()
                ->then(function (Collection $collection) use ($offset) {
                    return $collection->slice($offset);
                })
            );
        }

        return new PromiseCollection($this->mediumArticlesClient
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
                $mediumArticles = [];

                foreach ($result['items'] as $mediumArticle) {
                    if (isset($this->articles[$mediumArticle['uri']])) {
                        $mediumArticles[] = $this->articles[$mediumArticle['uri']]->wait();
                    } else {
                        $mediumArticles[] = $mediumArticle = $this->denormalizer->denormalize($mediumArticle,
                            MediumArticle::class);
                        $this->articles[$mediumArticle->getUri()] = promise_for($mediumArticle);
                    }
                }

                return new ArrayCollection($mediumArticles);
            })
        );
    }

    public function reverse() : Collection
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
