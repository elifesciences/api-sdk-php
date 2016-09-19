<?php

namespace eLife\ApiSdk\Model;

final class Place
{
    private $id;
    private $coordinates;
    private $name;
    private $address;

    public function __construct(
        string $id = null,
        Coordinates $coordinates = null,
        array $name,
        Address $address = null
    ) {
        $this->id = $id;
        $this->coordinates = $coordinates;
        $this->name = $name;
        $this->address = $address;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Coordinates|null
     */
    public function getCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * @return string[]
     */
    public function getName(): array
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
}
