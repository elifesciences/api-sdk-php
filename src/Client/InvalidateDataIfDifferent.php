<?php

namespace eLife\ApiSdk\Client;

trait InvalidateDataIfDifferent
{
    protected abstract function invalidateDataIfDifferent(string $field, self $another);
}
