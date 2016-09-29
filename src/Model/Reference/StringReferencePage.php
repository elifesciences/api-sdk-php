<?php

namespace eLife\ApiSdk\Model\Reference;

final class StringReferencePage implements ReferencePages
{
    private $string;

    /**
     * @internal
     */
    public function __construct(string $string)
    {
        $this->string = $string;
    }

    public function toString() : string
    {
        return $this->string;
    }
}
