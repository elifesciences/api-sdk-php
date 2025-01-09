<?php

namespace eLife\ApiSdk\Model;

final class ElifeAssessment implements Model
{
    private $title;
    private $significance;
    private $strength;

    /**
     * @internal
     */
    public function __construct(
        string $title,
        array $significance = null,
        array $strength = null
    ) {
        $this->title = $title;
        $this->significance = $significance;
        $this->strength = $strength;
    }

    public function getTitle(): string
    {
        return $this->title;
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
