<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasId;

final class GoogleMap implements Block, HasId
{
    private $id;
    private $title;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title
    ) {
        $this->id = $id;
        $this->title = $title;
    }

    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}
