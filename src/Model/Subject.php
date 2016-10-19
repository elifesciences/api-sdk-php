<?php

namespace eLife\ApiSdk\Model;

use GuzzleHttp\Promise\PromiseInterface;

final class Subject
{
    private $id;
    private $name;
    private $impactStatement;
    private $image;

    /**
     * @internal
     */
    public function __construct(string $id, string $name, PromiseInterface $impactStatement, PromiseInterface $image)
    {
        $this->id = $id;
        $this->name = $name;
        $this->impactStatement = $impactStatement;
        $this->image = $image;
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

    public function getImage() : Image
    {
        return $this->image->wait();
    }
}
