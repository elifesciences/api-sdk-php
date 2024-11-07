<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class ArticlesClient
{
    const TYPE_ARTICLE_POA = 'application/vnd.elife.article-poa+json';
    const TYPE_ARTICLE_VOR = 'application/vnd.elife.article-vor+json';
    const TYPE_ARTICLE_LIST = 'application/vnd.elife.article-list+json';
    const TYPE_ARTICLE_HISTORY = 'application/vnd.elife.article-history+json';
    const TYPE_ARTICLE_RELATED = 'application/vnd.elife.article-related+json';

    use ApiClient;

    public function getArticleLatestVersion(array $headers, string $number) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "articles/$number"]), $headers);
    }

    public function getArticleHistory(array $headers, string $number) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "articles/$number/versions"]), $headers);
    }

    public function getRelatedArticles(array $headers, string $number) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "articles/$number/related"]), $headers);
    }

    public function getArticleVersion(array $headers, string $number, int $version) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "articles/$number/versions/$version"]), $headers);
    }

    public function listArticles(
        array $headers = [],
        int $page = 1,
        int $perPage = 20,
        bool $descendingOrder = true,
        array $subjects = []
    ) : PromiseInterface {
        return $this->getRequest(
            $this->createUri([
                'path' => 'articles',
                'query' => [
                    'page' => $page,
                    'per-page' => $perPage,
                    'order' => $descendingOrder ? 'desc' : 'asc',
                    'subject[]' => $subjects,
                ],
            ]),
            $headers
        );
    }
}
