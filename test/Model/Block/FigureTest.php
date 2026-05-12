<?php

namespace test\eLife\ApiSdk\Model\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Figure;
use eLife\ApiSdk\Model\Block\FigureAsset;
use eLife\ApiSdk\Model\Block\Image;
use eLife\ApiSdk\Model\Image as ImageFile;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class FigureTest extends TestCase
{
    #[Test]
    public function it_is_a_block()
    {
        $assets = new ArraySequence([
            new FigureAsset(null, 'label', new EmptySequence(), new Image(null, null, new EmptySequence(), Builder::for(ImageFile::class)->__invoke())),
        ]);

        $figure = new Figure(...$assets);

        $this->assertInstanceOf(Block::class, $figure);
    }

    #[Test]
    public function it_has_assets()
    {
        $assets = new ArraySequence([
            new FigureAsset(null, 'label', new EmptySequence(), new Image(null, null, new EmptySequence(), Builder::for(ImageFile::class)->__invoke())),
        ]);

        $figure = new Figure(...$assets);

        $this->assertEquals($assets, $figure->getAssets());
    }
}
