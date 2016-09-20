<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection;

final class ArticleSection
{
    private $content;
    private $doi;

    /**
     * @internal
     */
    public function __construct(Collection $content, string $doi = null)
    {
        $this->content = $content;
        $this->doi = $doi;
    }

    /**
     * @return Collection|Block[]
     */
    public function getContent() : Collection
    {
        return $this->content;
    }

    /**
     * @return string|null
     */
    public function getDoi()
    {
        return $this->doi;
    }
}
