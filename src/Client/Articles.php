<?php

namespace eLife\ApiSdk\Client;

use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Article;
use eLife\ApiSdk\Model\ArticleHistory;
use eLife\ApiSdk\Model\ArticleVersion;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Articles implements Iterator, Sequence
{
    use Client;

    private $count;
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $articlesClient;
    private $denormalizer;

    public function __construct(ArticlesClient $articlesClient, DenormalizerInterface $denormalizer)
    {
        $this->articlesClient = $articlesClient;
        $this->denormalizer = $denormalizer;
    }

    public function forSubject(string ...$subjectId) : Articles
    {
        $clone = clone $this;

        $clone->subjectsQuery = array_unique(array_merge($this->subjectsQuery, $subjectId));

        if ($clone->subjectsQuery !== $this->subjectsQuery) {
            $clone->count = null;
        }

        return $clone;
    }

    public function get(string $id, int $version = null) : PromiseInterface
    {
        if (null === $version) {
            return $this->articlesClient
                ->getArticleLatestVersion(
                    [
                        'Accept' => [
                            new MediaType(ArticlesClient::TYPE_ARTICLE_POA, 1),
                            new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, 1),
                        ],
                    ],
                    $id
                )
                ->then(function (Result $result) {
                    return $this->denormalizer->denormalize($result->toArray(), ArticleVersion::class);
                });
        }

        return $this->articlesClient
            ->getArticleVersion(
                [
                    'Accept' => [
                        new MediaType(ArticlesClient::TYPE_ARTICLE_POA, 1),
                        new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, 1),
                    ],
                ],
                $id,
                $version
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), ArticleVersion::class);
            });
    }

    public function getHistory(string $id) : PromiseInterface
    {
        return $this->articlesClient
            ->getArticleHistory(
                [
                    'Accept' => [new MediaType(ArticlesClient::TYPE_ARTICLE_HISTORY, 1)],
                ],
                $id
            )
            ->then(function (Result $result) {
                return $this->denormalizer->denormalize($result->toArray(), ArticleHistory::class);
            });
    }

    public function getRelatedArticles(string $id) : Sequence
    {
        return new PromiseSequence($this->articlesClient
            ->getRelatedArticles(
                [
                    'Accept' => [new MediaType(ArticlesClient::TYPE_ARTICLE_RELATED, 1)],
                ],
                $id
            )
            ->then(function (Result $result) {
                return array_map(function (array $article) {
                    return $this->denormalizer->denormalize($article, Article::class, null, ['snippet' => true]);
                }, $result->toArray());
            }));
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

        return new PromiseSequence($this->articlesClient
            ->listArticles(
                ['Accept' => new MediaType(ArticlesClient::TYPE_ARTICLE_LIST, 1)],
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
                    // *** temporary hack for invalid articles
                    // we resolve them as null, and let the client applications
                    // skip them rather than exploding when encountering them
                    if (isset($article['-invalid'])) {
                        return null;
                    }

                    // *** end of temporary hack

                    return $this->denormalizer->denormalize($article, ArticleVersion::class, null, ['snippet' => true]);
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
