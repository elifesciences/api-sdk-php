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
use eLife\ApiSdk\Model\ReviewedPreprint;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class Articles implements Iterator, Sequence
{
    const VERSION_ARTICLE_POA = 3;
    const VERSION_ARTICLE_VOR = 7;
    const VERSION_ARTICLE_LIST = 1;
    const VERSION_ARTICLE_HISTORY = 2;
    const VERSION_ARTICLE_RELATED = 2;

    use Client;
    use ForSubject;

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

    public function get(string $id, int $version = null) : PromiseInterface
    {
        if (null === $version) {
            return $this->articlesClient
                ->getArticleLatestVersion(
                    [
                        'Accept' => implode(', ', [
                            new MediaType(ArticlesClient::TYPE_ARTICLE_POA, self::VERSION_ARTICLE_POA),
                            new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, self::VERSION_ARTICLE_VOR),
                        ]),
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
                    'Accept' => implode(', ', [
                        new MediaType(ArticlesClient::TYPE_ARTICLE_POA, self::VERSION_ARTICLE_POA),
                        new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, self::VERSION_ARTICLE_VOR),
                    ]),
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
                    'Accept' => [(string) new MediaType(ArticlesClient::TYPE_ARTICLE_HISTORY, self::VERSION_ARTICLE_HISTORY)],
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
                    'Accept' => [(string) new MediaType(ArticlesClient::TYPE_ARTICLE_RELATED, self::VERSION_ARTICLE_RELATED)],
                ],
                $id
            )
            ->then(function (Result $result) {
                return array_map(function (array $article) {
                    return $this->denormalizer->denormalize(
                        $article,
                        $article['type'] === 'reviewed-preprint' ? ReviewedPreprint::class : Article::class,
                        null,
                        ['snippet' => true]
                    );
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
                ['Accept' => (string) new MediaType(ArticlesClient::TYPE_ARTICLE_LIST, self::VERSION_ARTICLE_LIST)],
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

    protected function invalidateData()
    {
        $this->count = null;
    }
}
