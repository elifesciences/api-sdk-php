<?php

namespace eLife\ApiSdk\Client;

trait InvalidateDataIfDifferent
{
    abstract protected function invalidateDataIfDifferent(string $field, self $another);
}
