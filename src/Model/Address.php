<?php

namespace eLife\ApiSdk\Model;

final class Address
{
    private $formatted;
    private $streetAddress;
    private $locality;
    private $area;
    private $country;
    private $postalCode;

    /**
     * @internal
     */
    public function __construct(
        array $formatted,
        array $streetAddress = [],
        array $locality = [],
        array $area = [],
        string $country = null,
        string $postalCode = null
    ) {
        $this->formatted = $formatted;
        $this->streetAddress = $streetAddress;
        $this->locality = $locality;
        $this->area = $area;
        $this->country = $country;
        $this->postalCode = $postalCode;
    }

    /**
     * @return string[]
     */
    public function getFormatted(): array
    {
        return $this->formatted;
    }

    /**
     * @return string[]
     */
    public function getStreetAddress(): array
    {
        return $this->streetAddress;
    }

    /**
     * @return string[]
     */
    public function getLocality(): array
    {
        return $this->locality;
    }

    /**
     * @return string[]
     */
    public function getArea(): array
    {
        return $this->area;
    }

    /**
     * @return string|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }
}
