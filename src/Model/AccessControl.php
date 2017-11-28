<?php

namespace eLife\ApiSdk\Model;

final class AccessControl implements Model
{
    private $value;
    private $access;

    /**
     * @internal
     *
     * @param mixed $value
     */
    public function __construct(
        $value,
        string $access = 'public'
    ) {
        $this->value = $value;
        $this->access = $access;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getAccess() : string
    {
        return $this->access;
    }
}
