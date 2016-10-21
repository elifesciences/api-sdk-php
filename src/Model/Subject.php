<?php

namespace eLife\ApiSdk\Model;

use GuzzleHttp\Promise\PromiseInterface;

final class Subject
{
    private $id;
    private $name;
    private $impactStatement;
    private $banner;
    private $thumbnail;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $name,
        PromiseInterface $impactStatement,
        PromiseInterface $banner,
        PromiseInterface $thumbnail
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->impactStatement = $impactStatement;
        $this->banner = $banner;
        $this->thumbnail = $thumbnail;
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

    public function getBanner() : Image
    {
        return $this->banner->wait();
    }

    public function getThumbnail() : Image
    {
        return $this->thumbnail->wait();
    }
}
