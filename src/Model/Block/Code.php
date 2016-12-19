<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;

final class Code implements Block
{
    private $code;
    private $language;

    /**
     * @internal
     */
    public function __construct(string $code, string $language = null)
    {
        $this->code = $code;
        $this->language = $language;
    }

    public function getCode() : string
    {
        return $this->code;
    }

    /**
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->language;
    }
}
