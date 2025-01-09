<?php

namespace eLife\ApiSdk\Model;

final class ElifeAssessment implements Model
{
    private $title;
    private $articleSection;
    private $significance;
    private $strength;

    /**
     * @internal
     */
    public function __construct(
        string $title,
        ArticleSection $articleSection = null,
        array $significance = null,
        array $strength = null
    ) {
        $this->title = $title;
        $this->articleSection = $articleSection;
        $this->significance = $significance;
        $this->strength = $strength;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return ArticleSection|null
     */
    public function getArticleSection()
    {
        return $this->articleSection;
    }

    /**
     * @return array|null
     */
    public function getSignificance()
    {
        return $this->significance;
    }

    /**
     * @return array|null
     */
    public function getStrength()
    {
        return $this->strength;
    }
}
