<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\OnBehalfOfAuthor;
use PHPUnit_Framework_TestCase;

final class OnBehalfOfAuthorTest extends PHPUnit_Framework_TestCase
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
    public function it_is_on_behalf_of()
    {
        $author = new OnBehalfOfAuthor('on behalf of An Organisation');

        $this->assertSame('on behalf of An Organisation', $author->getOnBehalfOf());
    }
}
