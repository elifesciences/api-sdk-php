<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

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
        Sequence $formatted,
        Sequence $streetAddress,
        Sequence $locality,
        Sequence $area,
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
     * @return Sequence|string[]
     */
    public function getFormatted(): Sequence
    {
        return $this->formatted;
    }

    /**
     * @return Sequence|string[]
     */
    public function getStreetAddress(): Sequence
    {
        return $this->streetAddress;
    }

    /**
     * @return Sequence|string[]
     */
    public function getLocality(): Sequence
    {
        return $this->locality;
    }

    /**
     * @return Sequence|string[]
     */
    public function getArea(): Sequence
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

    public function toString() : string
    {
        return implode(', ', $this->formatted->toArray());
    }
}
