<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\LabsPost;
use eLife\ApiSdk\Model\Model;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;
use function GuzzleHttp\Promise\rejection_for;

final class LabsPostTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $labsPost = new LabsPost(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertInstanceOf(Model::class, $labsPost);
    }

    /**
     * @test
     */
    public function it_has_a_number()
    {
        $labsPost = new LabsPost(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'),
            new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertSame(1, $labsPost->getNumber());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $labsPost = new LabsPost(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertSame('title', $labsPost->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new LabsPost(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, 'impact statement',
            Builder::for(Image::class)->sample('thumbnail'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );
        $withOut = new LabsPost(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $labsPost = new LabsPost(1, 'title', $date = new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertInstanceOf(HasPublishedDate::class, $labsPost);
        $this->assertEquals($date, $labsPost->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_updated_date()
    {
        $with = new LabsPost(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), $updated = new DateTimeImmutable('now', new DateTimeZone('Z')), 'impact statement',
            Builder::for(Image::class)->sample('thumbnail'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );
        $withOut = new LabsPost(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($updated, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    /**
     * @test
     */
    public function it_has_a_thumbnail()
    {
        $labsPost = new LabsPost(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            $image = Builder::for(Image::class)->sample('thumbnail'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertInstanceOf(HasThumbnail::class, $labsPost);
        $this->assertEquals($image, $labsPost->getThumbnail());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [new Block\Paragraph('foo')];

        $labsPost = new LabsPost(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), new ArraySequence($content)
        );

        $this->assertInstanceOf(HasContent::class, $labsPost);
        $this->assertEquals($content, $labsPost->getContent()->toArray());
    }
}
