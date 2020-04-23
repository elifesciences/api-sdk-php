<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Tweet;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\HasId;
use PHPUnit_Framework_TestCase;

final class TweetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $tweet = new Tweet('foo', new Date(2000), [new Paragraph('tweet')], 'accountId', 'accountLabel');

        $this->assertInstanceOf(Block::class, $tweet);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $tweet = new Tweet('foo', new Date(2000), [new Paragraph('tweet')], 'accountId', 'accountLabel');

        $this->assertInstanceOf(HasId::class, $tweet);
        $this->assertSame('foo', $tweet->getId());
    }

    /**
     * @test
     */
    public function it_has_a_date()
    {
        $tweet = new Tweet('foo', new Date(2020, 4, 23), [new Paragraph('tweet')], 'accountId', 'accountLabel');

        $this->assertEquals(new Date(2020, 4, 23), $tweet->getDate());
    }

    /**
     * @test
     */
    public function it_has_text()
    {
        $tweet = new Tweet('foo', new Date(2000), [new Paragraph('tweet')], 'accountId', 'accountLabel');

        $this->assertEquals([new Paragraph('tweet')], $tweet->getText());
    }

    /**
     * @test
     */
    public function it_has_an_account_id()
    {
        $tweet = new Tweet('foo',  new Date(2000), [new Paragraph('tweet')], 'accountId', 'accountLabel');

        $this->assertEquals('accountId', $tweet->getAccountId());
    }

    /**
     * @test
     */
    public function it_has_an_account_label()
    {
        $tweet = new Tweet('foo',  new Date(2000), [new Paragraph('tweet')], 'accountId', 'accountLabel');

        $this->assertEquals('accountLabel', $tweet->getAccountLabel());
    }

    /**
     * @test
     */
    public function it_may_be_a_conversation()
    {
        $true = new Tweet('foo',  new Date(2000), [new Paragraph('tweet')], 'accountId', 'accountLabel', true);
        $false = new Tweet('foo',  new Date(2000), [new Paragraph('tweet')], 'accountId', 'accountLabel');

        $this->assertTrue($true->isConversation());
        $this->assertFalse($false->isConversation());
    }

    /**
     * @test
     */
    public function it_may_contain_a_media_card()
    {
        $true = new Tweet('foo',  new Date(2000), [new Paragraph('tweet')], 'accountId', 'accountLabel', false, true);
        $false = new Tweet('foo',  new Date(2000), [new Paragraph('tweet')], 'accountId', 'accountLabel', false);

        $this->assertTrue($true->isMediaCard());
        $this->assertFalse($false->isMediaCard());
    }
}
