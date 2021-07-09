<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;

final class ArticleHistory
{
    private $received;
    private $accepted;
    private $versions;

    /**
     * @internal
     */
    public function __construct(Date $received = null, Date $accepted = null, Sequence $versions)
    {
        $this->received = $received;
        $this->accepted = $accepted;
        $this->versions = $versions;
    }

    /**
     * @return Date|null
     */
    public function getReceived()
    {
        return $this->received;
    }

    /**
     * @return Date|null
     */
    public function getAccepted()
    {
        return $this->accepted;
    }

    public function getVersions() : Sequence
    {
        return $this->versions;
    }
}
