<?php

namespace eLife\ApiSdk\Model;

final class MediaContact implements Model
{
    private $details;
    private $affiliations;
    private $emailAddresses;
    private $phoneNumbers;

    /**
     * @internal
     */
    public function __construct(
        PersonDetails $details,
        array $affiliations = [],
        array $emailAddresses = [],
        array $phoneNumbers = []
    ) {
        $this->details = $details;
        $this->affiliations = $affiliations;
        $this->emailAddresses = $emailAddresses;
        $this->phoneNumbers = $phoneNumbers;
    }

    public function getDetails() : PersonDetails
    {
        return $this->details;
    }

    /**
     * @return Place[]
     */
    public function getAffiliations() : array
    {
        return $this->affiliations;
    }

    /**
     * @return string[]
     */
    public function getEmailAddresses() : array
    {
        return $this->emailAddresses;
    }

    /**
     * @return string[]
     */
    public function getPhoneNumbers() : array
    {
        return $this->phoneNumbers;
    }
}
