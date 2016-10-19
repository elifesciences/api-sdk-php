<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class BlogArticles implements Iterator, Sequence
{
    use ArrayFromIterator;
    use SlicedIterator;

    private $count;
    private $articles;
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $blogClient;
    private $denormalizer;

    public function __construct(BlogClient $blogClient, DenormalizerInterface $denormalizer)
    {
        $this->articles = new ArrayObject();
        $this->blogClient = $blogClient;
        $this->denormalizer = $denormalizer;
    }

    public function __clone()
    {
        $this->resetIterator();
    }

    public function get(string $id) : PromiseInterface
    {
        if (isset($this->articles[$id])) {
            return $this->articles[$id];
        }

        return $this->articles[$id] = $this->blogClient
            ->getArticle(
                ['Accept' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE, 1)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), BlogArticle::class);
            });
    }

    public function forSubject(string ...$subjectId) : BlogArticles
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

        return new PromiseSequence($this->blogClient
            ->listArticles(
                ['Accept' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE_LIST, 1)],
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
                $articles = [];

                foreach ($result['items'] as $article) {
                    if (isset($this->articles[$article['id']])) {
                        $articles[] = $this->articles[$article['id']]->wait();
                    } else {
                        $articles[] = $article = $this->denormalizer->denormalize($article, BlogArticle::class, null,
                            ['snippet' => true]);
                        $this->articles[$article->getId()] = promise_for($article);
                    }
                }

                return new ArraySequence($articles);
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
