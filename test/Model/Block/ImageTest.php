<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Image;
use eLife\ApiSdk\Model\Block\ImageFile;
use eLife\ApiSdk\Model\Image as ImageModel;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class ImageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $primary = new ImageFile(null, null, null, null, new EmptySequence(), Builder::for(ImageModel::class)->__invoke());
        $image = new Image($primary);

        $this->assertInstanceOf(Block::class, $image);
    }

    /**
     * @test
     */
    public function it_has_an_image()
    {
        $primary = new ImageFile(null, null, null, null, new EmptySequence(), Builder::for(ImageModel::class)->__invoke());
        $image = new Image($primary);

        $this->assertEquals($primary, $image->getImage());
    }

    /**
     * @test
     */
    public function it_may_have_supplements()
    {
        $primary = new ImageFile(null, null, null, 'primary', new EmptySequence(), Builder::for(ImageModel::class)->__invoke());
        $supplements = [
            new ImageFile(null, null, null, 'supplement 1', new EmptySequence(), Builder::for(ImageModel::class)->__invoke()),
            new ImageFile(null, null, null, 'supplement 2', new EmptySequence(), Builder::for(ImageModel::class)->__invoke()),
        ];
        $with = new Image(...array_merge([$primary], $supplements));
        $withOut = new Image($primary);

        $this->assertEquals($supplements, $with->getSupplements());
        $this->assertEmpty($withOut->getSupplements());
    }
}
