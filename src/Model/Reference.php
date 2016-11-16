<?php

namespace eLife\ApiSdk\Model;

interface Reference
{
    public function getId() : string;

    public function getDate() : Date;

    /**
     * @return string|null
     */
    public function getDiscriminator();
}
