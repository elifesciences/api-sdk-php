<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ArrayFromIterator;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\CreatesObjects;
use eLife\ApiSdk\Promise\CallbackPromise;
use eLife\ApiSdk\SlicedIterator;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use function GuzzleHttp\Promise\all;
use function GuzzleHttp\Promise\promise_for;

final class BlogArticles implements Iterator, Collection
{
    use ArrayFromIterator;
    use CreatesObjects;
    use SlicedIterator;

    private $count;
    private $articles;
    private $descendingOrder = true;
    private $blogClient;
    private $subjects;

    public function __construct(
        BlogClient $blogClient,
        Subjects $subjects
    ) {
        $this->articles = new ArrayObject();
        $this->blogClient = $blogClient;
        $this->subjects = $subjects;
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
                $content = new FulfilledPromise($result['content']);

                if (!empty($result->toArray()['subjects'])) {
                    $subjects = new CallbackPromise(function () use ($result) {
                        return $this->getSubjects($result['subjects'] ?? [])->wait();
                    });
                } else {
                    $subjects = null;
                }

                return $this->createBlogArticle($result->toArray(), $content, $subjects);
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

        return new PromiseCollection($this->blogClient
            ->listArticles(
                ['Accept' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE_LIST, 1)],
                ($offset / $length) + 1,
                $length,
                $this->descendingOrder
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
                        $promises[$article['id']] = $this->blogClient->getArticle(
                            ['Accept' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE, 1)],
                            $article['id']
                        );
                    }

                    return $promises;
                });

                $subjectPromise = new CallbackPromise(function () use ($result) {
                    $promises = [];
                    foreach ($result['items'] as $article) {
                        $promises[$article['id']] = $this->getSubjects($article['subjects'] ?? []);
                    }

                    return $promises;
                });

                foreach ($result['items'] as $article) {
                    if (isset($this->articles[$article['id']])) {
                        $articles[] = $this->articles[$article['id']]->wait();
                    } else {
                        $content = $fullPromise
                            ->then(function (array $promises) use ($article) {
                                return $promises[$article['id']]->wait()['content'];
                            });

                        if (!empty($article['subjects'])) {
                            $subjects = $subjectPromise
                                ->then(function (array $promises) use ($article) {
                                    return $promises[$article['id']]->wait();
                                });
                        } else {
                            $subjects = null;
                        }

                        $articles[] = $article = $this->createBlogArticle($article, $content, $subjects);
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

    private function getSubjects(array $ids) : PromiseInterface
    {
        $subjects = [];

        foreach ($ids as $id) {
            $subjects[] = $this->subjects->get($id);
        }

        return all($subjects);
    }
}
