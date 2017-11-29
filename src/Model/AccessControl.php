<?php

namespace eLife\ApiSdk\Model;

final class AccessControl implements Model
{
    const ACCESS_PUBLIC = 'public';
    const ACCESS_RESTRICTED = 'restricted';

    private $value;
    private $access;

    /**
     * @internal
     *
     * @param mixed $value
     */
    public function __construct(
        $value,
        string $access = self::ACCESS_PUBLIC
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
