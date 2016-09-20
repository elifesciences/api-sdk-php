<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\ArticleVersion;
use eLife\ApiSdk\Promise\CallbackPromise;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class Articles implements Iterator, Collection
{
    use ArrayFromIterator;
    use SlicedIterator;

    private $count;
    private $articles;
    private $articleVersions;
    private $descendingOrder = true;
    private $subjectsQuery = [];
    private $articlesClient;
    private $denormalizer;

    public function __construct(ArticlesClient $articlesClient, DenormalizerInterface $denormalizer)
    {
        $this->articles = new ArrayObject();
        $this->articleVersions = new ArrayObject();
        $this->articlesClient = $articlesClient;
        $this->denormalizer = $denormalizer;
    }

    public function __clone()
    {
        $this->resetIterator();
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
            if (isset($this->articles[$id])) {
                return $this->articles[$id];
            }

            return $this->articles[$id] = $this->articlesClient
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

        if (isset($this->articleVersions[$id][$version])) {
            return $this->articles[$id][$version];
        }

        return $this->articles[$id][$version] = $this->articlesClient
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

    public function slice(int $offset, int $length = null) : Collection
    {
        if (null === $length) {
            return new PromiseCollection($this->all()
                ->then(function (Collection $collection) use ($offset) {
                    return $collection->slice($offset);
                })
            );
        }

        return new PromiseCollection($this->articlesClient
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
                $articles = [];

                $fullPromise = new CallbackPromise(function () use ($result) {
                    $promises = [];
                    foreach ($result['items'] as $article) {
                        $promises[$article['id']] = $this->articlesClient->getArticleLatestVersion(
                            [
                                'Accept' => [
                                    new MediaType(ArticlesClient::TYPE_ARTICLE_POA, 1),
                                    new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, 1),
                                ],
                            ],
                            $article['id']
                        );
                    }

                    return $promises;
                });

                foreach ($result['items'] as $article) {
                    if (isset($this->articles[$article['id']])) {
                        $articles[] = $this->articles[$article['id']]->wait();
                    } else {
                        $article['abstract'] = $fullPromise
                            ->then(function (array $promises) use ($article) {
                                return $promises[$article['id']]->wait()['abstract'] ?? null;
                            });
                        $article['authors'] = $fullPromise
                            ->then(function (array $promises) use ($article) {
                                return $promises[$article['id']]->wait()['authors'];
                            });
                        $article['body'] = $fullPromise
                            ->then(function (array $promises) use ($article) {
                                return $promises[$article['id']]->wait()['body'] ?? null;
                            });
                        $article['copyright'] = $fullPromise
                            ->then(function (array $promises) use ($article) {
                                return $promises[$article['id']]->wait()['copyright'];
                            });
                        $article['digest'] = $fullPromise
                            ->then(function (array $promises) use ($article) {
                                return $promises[$article['id']]->wait()['digest'] ?? null;
                            });
                        $article['issue'] = $fullPromise
                            ->then(function (array $promises) use ($article) {
                                return $promises[$article['id']]->wait()['issue'];
                            });

                        switch ($article['status']) {
                            case 'vor':
                                $article['keywords'] = $fullPromise
                                    ->then(function (array $promises) use ($article) {
                                        return $promises[$article['id']]->wait()['keywords'];
                                    });

                                break;
                        }

                        $articles[] = $article = $this->denormalizer->denormalize($article, ArticleVersion::class);
                        $this->articles[$article->getId()] = promise_for($article);
                    }
                }

                return new ArrayCollection($articles);
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
