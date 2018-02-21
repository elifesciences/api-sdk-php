<?php

namespace eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class Subject implements Model, HasBanner, HasId, HasIdentifier, HasImpactStatement, HasThumbnail
{
    private $id;
    private $name;
    private $impactStatement;
    private $aimsAndScope;
    private $banner;
    private $thumbnail;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $name,
        PromiseInterface $impactStatement,
        Sequence $aimsAndScope,
        PromiseInterface $banner,
        PromiseInterface $thumbnail
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->impactStatement = $impactStatement;
        $this->aimsAndScope = $aimsAndScope;
        $this->banner = $banner;
        $this->thumbnail = $thumbnail;
    }

    public function getIdentifier() : Identifier
    {
        return Identifier::subject($this->id);
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getImpactStatement()
    {
        return $this->impactStatement->wait();
    }

    /**
     * @return Sequence|Block[]
     */
    public function getAimsAndScope() : Sequence
    {
        return $this->aimsAndScope;
    }

    public function getBanner() : Image
    {
        return $this->banner->wait();
    }

    public function getThumbnail() : Image
    {
        return $this->thumbnail->wait();
    }
}
