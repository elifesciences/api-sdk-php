<?php

namespace eLife\ApiSdk\Client;

use ArrayObject;
use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\ArticleVersion;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function GuzzleHttp\Promise\promise_for;

final class Articles implements Iterator, Sequence
{
    use Client;

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
                $articles = [];

                foreach ($result['items'] as $article) {
                    if (isset($article['-invalid'])) {
                        $articles[] = null;
                    } elseif (isset($this->articles[$article['id']])) {
                        $articles[] = $this->articles[$article['id']]->wait();
                    } else {
                        $articles[] = $article = $this->denormalizer->denormalize($article, ArticleVersion::class, null,
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
}
