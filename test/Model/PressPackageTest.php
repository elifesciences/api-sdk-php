<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\ArticlePoA;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\MediaContact;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\PersonDetails;
use eLife\ApiSdk\Model\PressPackage;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\ApiSdk\Model\Subject;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class PressPackageTest extends TestCase
{
    private $builder;

    public function setUp()
    {
        $this->builder = Builder::for(PressPackage::class);
    }

    /**
     * @test
     */
    public function it_is_a_model()
    {
        $package = $this->builder
            ->__invoke();

        $this->assertInstanceOf(Model::class, $package);
    }

    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $package = $this->builder
            ->withId('id')
            ->__invoke();

        $this->assertInstanceOf(HasIdentifier::class, $package);
        $this->assertEquals(Identifier::pressPackage('id'), $package->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $package = $this->builder
            ->withTitle('title')
            ->__invoke();

        $this->assertSame('title', $package->getTitle());
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $package = $this->builder
            ->withId('id')
            ->__invoke();

        $this->assertInstanceOf(HasId::class, $package);
        $this->assertSame('id', $package->getId());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $package = $this->builder
            ->withPublished($date = new DateTimeImmutable())
            ->__invoke();

        $this->assertInstanceOf(HasPublishedDate::class, $package);
        $this->assertEquals($date, $package->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_updated_date()
    {
        $with = $this->builder
            ->withUpdated($date = new DateTimeImmutable())
            ->__invoke();
        $withOut = $this->builder
            ->withUpdated(null)
            ->__invoke();

        $this->assertInstanceOf(HasPublishedDate::class, $with);
        $this->assertEquals($date, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = $this->builder
            ->withImpactStatement('impact statement')
            ->__invoke();
        $withOut = $this->builder
            ->withImpactStatement(null)
            ->__invoke();

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_may_have_a_social_image()
    {
        $with = $this->builder
            ->withPromiseOfSocialImage($image = Builder::for(Image::class)->sample('social'))
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfSocialImage(null)
            ->__invoke();

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame($image, $with->getSocialImage());
        $this->assertNull($withOut->getSocialImage());
    }

    /**
     * @test
     */
    public function it_may_have_subjects()
    {
        $with = $this->builder
            ->withSubjects(new ArraySequence($subjects = [Builder::dummy(Subject::class)]))
            ->__invoke();
        $withOut = $this->builder
            ->withSubjects(new EmptySequence())
            ->__invoke();

        $this->assertInstanceOf(HasSubjects::class, $with);
        $this->assertEquals($subjects, $with->getSubjects()->toArray());
        $this->assertEmpty($withOut->getSubjects());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $package = $this->builder
            ->withContent(new ArraySequence($content = [new Block\Paragraph('foo')]))
            ->__invoke();

        $this->assertInstanceOf(HasContent::class, $package);
        $this->assertEquals($content, $package->getContent()->toArray());
    }

    /**
     * @test
     */
    public function it_has_related_content()
    {
        $with = $this->builder
            ->withRelatedContent(new ArraySequence($relatedContent = [
                Builder::dummy(ReviewedPreprint::class),
                Builder::dummy(ArticlePoA::class),
            ]))
            ->__invoke();
        $withOut = $this->builder
            ->withRelatedContent(new EmptySequence())
            ->__invoke();

        $this->assertEquals($relatedContent, $with->getRelatedContent()->toArray());
        $this->assertEmpty($withOut->getRelatedContent()->toArray());
    }

    /**
     * @test
     */
    public function it_has_media_contacts()
    {
        $with = $this->builder
            ->withMediaContacts(new ArraySequence($mediaContacts = [new MediaContact(new PersonDetails('Person', 'Person'))]))
            ->__invoke();
        $withOut = $this->builder
            ->withMediaContacts(new EmptySequence())
            ->__invoke();

        $this->assertEquals($mediaContacts, $with->getMediaContacts()->toArray());
        $this->assertEmpty($withOut->getMediaContacts()->toArray());
    }

    /**
     * @test
     */
    public function it_has_about()
    {
        $with = $this->builder
            ->withAbout(new ArraySequence($about = [new Block\Paragraph('foo')]))
            ->__invoke();
        $withOut = $this->builder
            ->withAbout(new EmptySequence())
            ->__invoke();

        $this->assertEquals($about, $with->getAbout()->toArray());
        $this->assertEmpty($withOut->getAbout()->toArray());
    }
}
