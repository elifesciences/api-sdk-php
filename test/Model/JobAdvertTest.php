<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasSocialImage;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\JobAdvert;
use eLife\ApiSdk\Model\Model;
use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Promise\Create;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class JobAdvertTest extends TestCase
{
    #[Test]
    public function it_is_a_model()
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', Create::promiseFor(Builder::for(Image::class)->sample('social')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
            new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(Model::class, $jobAdvert);
    }

    #[Test]
    public function it_has_an_identifier()
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', Create::rejectionFor('No social image'), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
        new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(HasIdentifier::class, $jobAdvert);
        $this->assertEquals(Identifier::jobAdvert('id'), $jobAdvert->getIdentifier());
    }

    #[Test]
    public function it_has_an_id()
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', Create::rejectionFor('No social image'), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
        new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(HasId::class, $jobAdvert);
        $this->assertSame('id', $jobAdvert->getId());
    }

    #[Test]
    public function it_has_a_title()
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', Create::rejectionFor('No social image'), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
        new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));

        $this->assertSame('title', $jobAdvert->getTitle());
    }

    #[Test]
    public function it_may_have_an_impact_statement()
    {
        $with = new JobAdvert('id', 'title', 'impact statement', Create::rejectionFor('No social image'), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
          new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));
        $withOut = new JobAdvert('id', 'title', null, Create::rejectionFor('No social image'), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
          new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    #[Test]
    public function it_may_have_a_social_image()
    {
        $with = new JobAdvert('id', 'title', null, Create::promiseFor($socialImage = Builder::for(Image::class)->sample('social')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
            new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));
        $withOut = new JobAdvert('id', 'title', null, Create::promiseFor(null), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
            new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(HasSocialImage::class, $with);
        $this->assertSame($socialImage, $with->getSocialImage());
        $this->assertNull($withOut->getSocialImage());
    }

    #[Test]
    public function it_has_a_published_date()
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', Create::rejectionFor('No social image'), $published = new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
          new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(HasPublishedDate::class, $jobAdvert);
        $this->assertEquals($published, $jobAdvert->getPublishedDate());
    }

    #[Test]
    public function it_may_have_an_updated_date()
    {
        $with = new JobAdvert('id', 'title', 'impact statement', Create::rejectionFor('No social image'), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), $updated = new DateTimeImmutable('now', new DateTimeZone('Z')),
          new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));
        $withOut = new JobAdvert('id', 'title', 'impact statement', Create::rejectionFor('No social image'), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')), null,
          new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($updated, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    #[Test]
    public function it_has_a_closing_date()
    {
        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', Create::rejectionFor('No social image'), new DateTimeImmutable('now', new DateTimeZone('Z')), $closing = new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
        new PromiseSequence(Create::rejectionFor('Job advert content should not be unwrapped')));

        $this->assertEquals($closing, $jobAdvert->getClosingDate());
    }

    #[Test]
    public function it_has_content()
    {
        $content = [new Block\Paragraph('foo')];

        $jobAdvert = new JobAdvert('id', 'title', 'impact statement', Create::rejectionFor('No social image'), new DateTimeImmutable('now', new DateTimeZone('Z')), new DateTimeImmutable('now', $closing = new DateTimeZone('Z')), new DateTimeImmutable('now', new DateTimeZone('Z')),
        new ArraySequence($content));

        $this->assertInstanceOf(HasContent::class, $jobAdvert);
        $this->assertEquals($content, $jobAdvert->getContent()->toArray());
    }
}
