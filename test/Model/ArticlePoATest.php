<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\ArticlePoA;
use test\eLife\ApiSdk\Builder;

final class ArticlePoATest extends ArticleTest
{
    public function setUp()
    {
        $this->builder = Builder::for(ArticlePoA::class);
    }
}
