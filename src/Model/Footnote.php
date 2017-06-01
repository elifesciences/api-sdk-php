<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class Footnote implements HasId
{
    private $id;
    private $label;
    private $text;

    /**
     * @internal
     */
    public function __construct(string $id = null, string $label = null, Sequence $text)
    {
        $this->id = $id;
        $this->label = $label;
        $this->text = $text;
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

    /**
     * @return Sequence|Block[]
     */
    public function getText() : Sequence
    {
        return $this->text;
    }
}
