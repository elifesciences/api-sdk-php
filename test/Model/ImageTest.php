<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\HasAttribution;
use eLife\ApiSdk\Model\Image;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class ImageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_alt_text()
    {
        $image = Builder::for(Image::class)
            ->withAltText('foo')
            ->__invoke();

        $this->assertSame('foo', $image->getAltText());
    }

    /**
     * @test
     */
    public function it_has_a_uri()
    {
        $image = Builder::for(Image::class)
            ->withUri('http://www.example.com/example.jpg')
            ->__invoke();

        $this->assertSame('http://www.example.com/example.jpg', $image->getUri());
    }

    /**
     * @test
     */
    public function it_may_have_attribution()
    {
        $with = Builder::for(Image::class)
            ->withAttribution($attribution = new ArraySequence(['attribution']))
            ->__invoke();
        $withOut = Builder::for(Image::class)
            ->withAttribution(new EmptySequence())
            ->__invoke();

        $this->assertInstanceOf(HasAttribution::class, $with);
        $this->assertEquals($attribution, $with->getAttribution());
        $this->assertEmpty($withOut->getAttribution());
    }

    /**
     * @test
     */
    public function it_has_a_source()
    {
        $image = Builder::for(Image::class)
            ->withSource($source = new File(
                'image/jpeg',
                'https://iiif.elifesciences.org/example.jpg/full/full/0/default.jpg',
                'example.jpg'
            ))
            ->__invoke();

        $this->assertEquals($source, $image->getSource());
    }

    /**
     * @test
     */
    public function it_has_a_width_and_height()
    {
        $image = Builder::for(Image::class)
            ->withWidth(100)
            ->withHeight(200)
            ->__invoke();

        $this->assertSame(100, $image->getWidth());
        $this->assertSame(200, $image->getHeight());
    }

    /**
     * @test
     */
    public function it_has_a_focal_point()
    {
        $image = Builder::for(Image::class)
            ->withFocalPointX(25)
            ->withFocalPointY(75)
            ->__invoke();

        $this->assertSame(25, $image->getFocalPointX());
        $this->assertSame(75, $image->getFocalPointY());
    }
}
