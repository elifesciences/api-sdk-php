<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
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
        Sequence $streetAddress = null,
        Sequence $locality = null,
        Sequence $area = null,
        string $country = null,
        string $postalCode = null
    ) {
        $this->formatted = $formatted;
        $this->streetAddress = ArraySequence::coalesce($streetAddress);
        $this->locality = ArraySequence::coalesce($locality);
        $this->area = ArraySequence::coalesce($area);
        $this->country = $country;
        $this->postalCode = $postalCode;
    }

    /**
     * @return Sequence of string
     */
    public function getFormatted(): Sequence
    {
        return $this->formatted;
    }

    /**
     * @return Sequence of string
     */
    public function getStreetAddress(): Sequence
    {
        return $this->streetAddress;
    }

    /**
     * @return Sequence of string
     */
    public function getLocality(): Sequence
    {
        return $this->locality;
    }

    /**
     * @return Sequence of string
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
}
