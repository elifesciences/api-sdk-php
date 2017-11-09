<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\Profile;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\Image;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class ProfileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $profile = new Profile(Builder::for(Image::class)->__invoke(), new EmptySequence());

        $this->assertInstanceOf(Block::class, $profile);
    }

    /**
     * @test
     */
    public function it_has_an_image()
    {
        $profile = new Profile($image = Builder::for(Image::class)->__invoke(), new EmptySequence());

        $this->assertEquals($image, $profile->getImage());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = new ArraySequence([new Paragraph('content')]);
        $profile = new Profile(Builder::for(Image::class)->__invoke(), $content);

        $this->assertInstanceOf(HasContent::class, $profile);
        $this->assertEquals($content, $profile->getContent());
    }
}
