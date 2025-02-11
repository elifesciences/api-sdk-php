<?php

namespace eLife\ApiSdk\Client;

trait ForElifeAssessment
{
    use InvalidateData;

    private $elifeAssessmentSignificancesQuery = [];
    private $elifeAssessmentStrengthsQuery = [];

    final public function forElifeAssessmentSignificance(string ...$significance) : self
    {
        $clone = clone $this;

        $clone->elifeAssessmentSignificancesQuery = array_unique(array_merge($this->elifeAssessmentSignificancesQuery, $significance));

        if ($clone->elifeAssessmentSignificancesQuery !== $this->elifeAssessmentSignificancesQuery) {
            $clone->invalidateData();
        }

        return $clone;
    }

    final public function forElifeAssessmentStrength(string ...$strength) : self
    {
        $clone = clone $this;

        $clone->elifeAssessmentStrengthsQuery = array_unique(array_merge($this->elifeAssessmentStrengthsQuery, $strength));

        if ($clone->elifeAssessmentStrengthsQuery !== $this->elifeAssessmentStrengthsQuery) {
            $clone->invalidateData();
        }

        return $clone;
    }
}
