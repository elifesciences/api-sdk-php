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
        array $significance = null,
        array $strength = null
    ) {
        $this->significance = $significance;
        $this->strength = $strength;
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
