<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class Funding
{
    private $awards;
    private $statement;

    /**
     * @internal
     */
    public function __construct(Sequence $awards, string $statement)
    {
        $this->awards = $awards;
        $this->statement = $statement;
    }

    /**
     * @return Sequence|FundingAward[]
     */
    public function getAwards() : Sequence
    {
        return $this->awards;
    }

    public function getStatement() : string
    {
        return $this->statement;
    }
}
