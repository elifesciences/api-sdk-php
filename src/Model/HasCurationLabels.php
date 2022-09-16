<?php

namespace eLife\ApiSdk\Model;

interface HasCurationLabels
{
    /**
     * @return string[]
     */
    public function getCurationLabels() : array;
}
