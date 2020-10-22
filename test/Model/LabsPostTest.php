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
use eLife\ApiSdk\Model\HasSocialImage;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\LabsPost;
use eLife\ApiSdk\Model\Model;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class LabsPostTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $labsPost = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), promise_for(Builder::for(Image::class)->sample('social')), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertInstanceOf(Model::class, $labsPost);
    }

    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $labsPost = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'),
            rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertEquals(Identifier::labsPost('80000001'), $labsPost->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_a_id()
    {
        $labsPost = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'),
            rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertSame('80000001', $labsPost->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $labsPost = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertSame('title', $labsPost->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, 'impact statement',
            Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );
        $withOut = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
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
        $labsPost = new LabsPost('80000001', 'title', $date = new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertInstanceOf(HasPublishedDate::class, $labsPost);
        $this->assertEquals($date, $labsPost->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_updated_date()
    {
        $with = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), $updated = new DateTimeImmutable('now', new DateTimeZone('Z')), 'impact statement',
            Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );
        $withOut = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
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
        $labsPost = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            $image = Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertInstanceOf(HasThumbnail::class, $labsPost);
        $this->assertEquals($image, $labsPost->getThumbnail());
    }

    /**
     * @test
     */
    public function it_may_have_a_social_image()
    {
        $with = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), promise_for($image = Builder::for(Image::class)->sample('thumbnail')), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );
        $withOut = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), promise_for(null), new PromiseSequence(rejection_for('Full Labs post should not be unwrapped'))
        );

        $this->assertInstanceOf(HasSocialImage::class, $with);
        $this->assertEquals($image, $with->getThumbnail());
        $this->assertNull($withOut->getSocialImage());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [new Block\Paragraph('foo')];

        $labsPost = new LabsPost('80000001', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'), new ArraySequence($content)
        );

        $this->assertInstanceOf(HasContent::class, $labsPost);
        $this->assertEquals($content, $labsPost->getContent()->toArray());
    }
}
