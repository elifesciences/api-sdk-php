<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\MediumClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\MediumArticle;
use eLife\ApiSdk\SlicedArrayAccess;
use eLife\ApiSdk\SlicedIterator;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class MediumArticles implements Iterator, Sequence
{
    use ArrayFromIterator;
    use SlicedArrayAccess;
    use SlicedIterator {
        SlicedIterator::getPage insteadof SlicedArrayAccess;
        SlicedIterator::isEmpty insteadof SlicedArrayAccess;
        SlicedIterator::resetPages insteadof SlicedArrayAccess;
    }

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

                return new ArraySequence($mediumArticles);
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
