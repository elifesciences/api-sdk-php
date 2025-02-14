<?php

namespace eLife\ApiSdk\Client;

trait ForElifeAssessmentSignificance
{
    private $elifeAssessmentSignificanceQuery = [];

    final public function forElifeAssessmentSignificance(string ...$elifeAssessmentSignificanceTerm) : self
    {
        $clone = clone $this;

        $clone->elifeAssessmentSignificanceQuery = array_unique(array_merge($this->elifeAssessmentSignificanceQuery, $elifeAssessmentSignificanceTerm));

        if ($clone->elifeAssessmentSignificanceQuery !== $this->elifeAssessmentSignificanceQuery) {
            $clone->invalidateData();
        }

        return $clone;
    }
}
