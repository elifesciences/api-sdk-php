<?php

namespace eLife\ApiSdk\Model;

final class Person
{
    private $preferredName;
    private $indexName;
    private $orcid;

    /**
     * @internal
     */
    public function __construct(string $preferredName, string $indexName, string $orcid = null)
    {
        $this->preferredName = $preferredName;
        $this->indexName = $indexName;
        $this->orcid = $orcid;
    }

    public function getPreferredName() : string
    {
        return $this->preferredName;
    }

    public function getIndexName() : string
    {
        return $this->indexName;
    }

    /**
     * @return string|null
     */
    public function getOrcid()
    {
        return $this->orcid;
    }
}
