<?php

namespace eLife\ApiSdk\ApiClient;

use eLife\ApiClient\ApiClient;
use GuzzleHttp\Promise\PromiseInterface;

class BlogClient
{
    const TYPE_BLOG_ARTICLE = 'application/vnd.elife.blog-article+json';
    const TYPE_BLOG_ARTICLE_LIST = 'application/vnd.elife.blog-article-list+json';

    use ApiClient;

    public function getArticle(array $headers, string $id) : PromiseInterface
    {
        return $this->getRequest($this->createUri(['path' => "blog-articles/$id"]), $headers);
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
                'path' => 'blog-articles',
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
