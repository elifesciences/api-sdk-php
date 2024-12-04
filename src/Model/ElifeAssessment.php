<?php

namespace eLife\ApiSdk\Model;

final class ElifeAssessment implements Model
{
    private $significance;
    private $strength;

    /**
     * @internal
     */
    public function __construct(
        array $significance,
        array $strength
    ) {
        $this->significance = $significance;
        $this->strength = $strength;
    }

    public function getSignificance() : array
    {
        return $this->significance;
    }

    public function getStrength() : array
    {
        return $this->strength;
    }
}
