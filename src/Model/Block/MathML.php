<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;

final class MathML implements Block
{
    private $id;
    private $label;
    private $mathml;

    /**
     * @internal
     */
    public function __construct(string $id = null, string $label = null, string $mathml)
    {
        $this->id = $id;
        $this->label = $label;
        $this->mathml = $mathml;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function getMathML() : string
    {
        return $this->mathml;
    }
}
