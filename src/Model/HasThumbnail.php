<?php

namespace eLife\ApiSdk\Model;

interface HasThumbnail
{
    /**
     * @return Image|null
     */
    public function getThumbnail();
}
