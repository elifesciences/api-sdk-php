<?php

namespace eLife\ApiSdk\Model;

final class Place implements CastsToString
{
    private $name;
    private $address;

    public function __construct(
        array $name,
        Address $address = null
    ) {
        $this->name = $name;
        $this->address = $address;
    }

    /**
     * @return string[]
     */
    public function getName() : array
    {
        return $this->name;
    }

    /**
     * @return Address|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    public function toString() : string
    {
        $string = implode(', ', $this->getName());

        if ($this->getAddress()) {
            $string .= ', '.implode(', ', $this->getAddress()->getFormatted()->toArray());
        }

        return $string;
    }
}
