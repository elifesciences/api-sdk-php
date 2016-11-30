<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class FundingAward implements HasId
{
    private $id;
    private $source;
    private $awardId;
    private $recipients;

    /**
     * @internal
     */
    public function __construct(string $id, Funder $source, string $awardId = null, Sequence $recipients)
    {
        $this->id = $id;
        $this->source = $source;
        $this->awardId = $awardId;
        $this->recipients = $recipients;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSource(): Funder
    {
        return $this->source;
    }

    /**
     * @return string|null
     */
    public function getAwardId()
    {
        return $this->awardId;
    }

    /**
     * @return Sequence|Author[]
     */
    public function getRecipients(): Sequence
    {
        return $this->recipients;
    }
}
