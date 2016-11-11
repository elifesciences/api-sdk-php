<?php

namespace eLife\ApiSdk\Model;

final class OnBehalfOfAuthor implements AuthorEntry
{
    private $onBehalfOf;

    /**
     * @internal
     */
    public function __construct(string $onBehalfOf)
    {
        $this->onBehalfOf = $onBehalfOf;
    }

    public function toString() : string
    {
        return $this->onBehalfOf;
    }
}
