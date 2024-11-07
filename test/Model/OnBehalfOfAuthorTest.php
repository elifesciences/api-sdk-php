<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\OnBehalfOfAuthor;
use PHPUnit\Framework\TestCase;

final class OnBehalfOfAuthorTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_an_author_entry()
    {
        $author = new OnBehalfOfAuthor('on behalf of An Organisation');

        $this->assertInstanceOf(AuthorEntry::class, $author);
    }

    /**
     * @test
     */
    public function it_is_a_string()
    {
        $author = new OnBehalfOfAuthor('on behalf of An Organisation');

        $this->assertSame('on behalf of An Organisation', $author->toString());
    }
}
