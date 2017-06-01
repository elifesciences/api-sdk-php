<?php

namespace eLife\ApiSdk\Model;

interface Article extends Model, HasId
{
    public function getId() : string;

    public function getType() : string;

    public function getTitle() : string;
}
