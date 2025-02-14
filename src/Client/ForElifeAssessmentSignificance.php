<?php

namespace eLife\ApiSdk\Client;

trait ForElifeAssessmentSignificance
{
    private $elifeAssessmentSignificanceQuery = [];

    final public function forElifeAssessmentSignificance(string ...$elifeAssessmentSignificanceTerm) : self
    {
        return $this;
    }
}
