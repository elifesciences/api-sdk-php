<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiSdk\ApiClient\BlogClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\BlogArticle;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class BlogArticles implements Iterator, Sequence
{
    const VERSION_BLOG_ARTICLE = 2;
    const VERSION_BLOG_ARTICLE_LIST = 1;

    use Client;
    use ForSubject;

    private $count;
    private $descendingOrder = true;
    private $blogClient;
    private $denormalizer;

    public function __construct(BlogClient $blogClient, DenormalizerInterface $denormalizer)
    {
        $this->blogClient = $blogClient;
        $this->denormalizer = $denormalizer;
    }

    public function get(string $id) : PromiseInterface
    {
        return $this->blogClient
            ->getArticle(
                ['Accept' => (string) new MediaType(BlogClient::TYPE_BLOG_ARTICLE, self::VERSION_BLOG_ARTICLE)],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), BlogArticle::class);
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

        return new PromiseSequence($this->blogClient
            ->listArticles(
                ['Accept' => (string) new MediaType(BlogClient::TYPE_BLOG_ARTICLE_LIST, self::VERSION_BLOG_ARTICLE_LIST)],
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
                    return $this->denormalizer->denormalize($article, BlogArticle::class, null, ['snippet' => true]);
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

    protected function invalidateData()
    {
        $this->count = null;
    }
}
