<?php

namespace eLife\ApiSdk\Client;

trait ForElifeAssessmentStrength
{
    private $elifeAssessmentStrengthQuery = [];
    final public function forElifeAssessmentStrength(string ...$elifeAssessmentStrengthTerm) : self
    {
        $clone = clone $this;

        $clone->elifeAssessmentStrengthQuery = array_unique(array_merge($this->elifeAssessmentStrengthQuery, $elifeAssessmentStrengthTerm));

        if ($clone->elifeAssessmentStrengthQuery !== $this->elifeAssessmentStrengthQuery) {
            $clone->invalidateData();
        }

        return $clone;
    }
}
