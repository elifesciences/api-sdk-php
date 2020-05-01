<?php

namespace eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlockWithCaption;
use eLife\ApiSdk\Model\HasId;

final class GoogleMap implements BlockWithCaption, HasId
{
    private $id;
    private $title;
    private $caption;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $title = null,
        Sequence $caption
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->caption = $caption;
    }

    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getCaption() : Sequence
    {
        return $this->caption;
    }
}
