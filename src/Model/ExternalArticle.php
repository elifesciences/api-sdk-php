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
    public function __construct(string $articleTitle, string $journal, string $authorLine, string $uri)
    {
        $this->articleTitle = $articleTitle;
        $this->journal = $journal;
        $this->authorLine = $authorLine;
        $this->uri = $uri;
    }

    public function getTitle() : string
    {
        return $this->articleTitle;
    }

    public function getAuthorLine() : string
    {
        return $this->authorLine;
    }

    public function getJournal() : string
    {
        return $this->journal;
    }

    public function getUri() : string
    {
        return $this->uri;
    }

    public function getId() : string
    {
        return 'external-'.sha1($this->uri);
    }

    public function getType() : string
    {
        return 'external-article';
    }
}
