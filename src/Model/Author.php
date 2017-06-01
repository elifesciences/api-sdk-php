<?php

namespace eLife\ApiSdk\Model;

abstract class Author implements AuthorEntry
{
    private $additionalInformation;
    private $affiliations;
    private $competingInterests;
    private $contribution;
    private $emailAddresses;
    private $equalContributionGroups;
    private $phoneNumbers;
    private $postalAddresses;

    /**
     * @internal
     */
    public function __construct(
        array $additionalInformation = [],
        array $affiliations = [],
        string $competingInterests = null,
        string $contribution = null,
        array $emailAddresses = [],
        array $equalContributionGroups = [],
        array $phoneNumbers = [],
        array $postalAddresses = []
    ) {
        $this->additionalInformation = $additionalInformation;
        $this->affiliations = $affiliations;
        $this->competingInterests = $competingInterests;
        $this->contribution = $contribution;
        $this->emailAddresses = $emailAddresses;
        $this->equalContributionGroups = $equalContributionGroups;
        $this->phoneNumbers = $phoneNumbers;
        $this->postalAddresses = $postalAddresses;
    }

    /**
     * @return string[]
     */
    public function getAdditionalInformation() : array
    {
        return $this->additionalInformation;
    }

    /**
     * @return Place[]
     */
    final public function getAffiliations(): array
    {
        return $this->affiliations;
    }

    /**
     * @return string|null
     */
    final public function getCompetingInterests()
    {
        return $this->competingInterests;
    }

    /**
     * @return string|null
     */
    final public function getContribution()
    {
        return $this->contribution;
    }

    /**
     * @return string[]
     */
    final public function getEmailAddresses(): array
    {
        return $this->emailAddresses;
    }

    /**
     * @return int[]
     */
    final public function getEqualContributionGroups(): array
    {
        return $this->equalContributionGroups;
    }

    /**
     * @return string[]
     */
    final public function getPhoneNumbers(): array
    {
        return $this->phoneNumbers;
    }

    /**
     * @return Address[]
     */
    final public function getPostalAddresses(): array
    {
        return $this->postalAddresses;
    }
}
