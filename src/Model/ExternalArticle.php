<?php

namespace eLife\ApiSdk\Model;

final class ExternalArticle implements Article
{
    private $articleTitle;
    private $journal;
    private $authorLine;
    private $uri;

    /**
     * @internal
     */
    public function __construct(string $articleTitle, Place $journal, string $authorLine, string $uri)
    {
        $this->articleTitle = $articleTitle;
        $this->journal = $journal;
        $this->authorLine = $authorLine;
        $this->uri = $uri;
    }

    public function getArticleTitle(): string
    {
        return $this->articleTitle;
    }

    public function getAuthorLine(): string
    {
        return $this->authorLine;
    }

    public function getJournal(): Place
    {
        return $this->journal;
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}
