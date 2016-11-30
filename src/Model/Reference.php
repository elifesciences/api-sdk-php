<?php

namespace eLife\ApiSdk\Model;

interface Reference extends HasId
{
    public function getId() : string;

    public function getDate() : Date;

    /**
     * @return string|null
     */
    public function getDiscriminator();
}
