<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Image;
use eLife\ApiSdk\Model\Block\ImageFile;
use PHPUnit_Framework_TestCase;

final class ImageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_block()
    {
        $primary = new ImageFile(null, null, null, null, [], '', 'http://www.example.com/image.jpg');
        $image = new Image($primary);

        $this->assertInstanceOf(Block::class, $image);
    }

    /**
     * @test
     */
    public function it_has_an_image()
    {
        $primary = new ImageFile(null, null, null, null, [], '', 'http://www.example.com/image.jpg');
        $image = new Image($primary);

        $this->assertEquals($primary, $image->getImage());
    }

    /**
     * @test
     */
    public function it_may_have_supplements()
    {
        $primary = new ImageFile(null, null, null, 'primary', [], '', 'http://www.example.com/image.jpg');
        $supplements = [
            new ImageFile(null, null, null, 'supplement 1', [], '', 'http://www.example.com/image.jpg'),
            new ImageFile(null, null, null, 'supplement 2', [], '', 'http://www.example.com/image.jpg'),
        ];
        $with = new Image(...array_merge([$primary], $supplements));
        $withOut = new Image($primary);

        $this->assertEquals($supplements, $with->getSupplements());
        $this->assertEmpty($withOut->getSupplements());
    }
}
