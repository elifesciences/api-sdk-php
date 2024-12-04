<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\ElifeAssessment;
use eLife\ApiSdk\Model\HasCurationLabels;
use eLife\ApiSdk\Model\HasDoi;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasPdf;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasReviewedDate;
use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\ApiSdk\Model\Subject;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class ReviewedPreprintTest extends TestCase
{
    private $builder;

    public function setUp(): void
    {
        $this->builder = Builder::for(ReviewedPreprint::class);
    }

    /**
     * @test
     */
    public function it_is_a_model()
    {
        $reviewedPreprint = $this->builder->__invoke();

        $this->assertInstanceOf(Model::class, $reviewedPreprint);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $reviewedPreprint = $this->builder
            ->withId('14107')
            ->__invoke();

        $this->assertInstanceOf(HasId::class, $reviewedPreprint);
        $this->assertSame('14107', $reviewedPreprint->getId());
    }

    /**
     * @test
     */
    final public function it_has_a_stage()
    {
        $reviewedPreprint = $this->builder
            ->withStage(ReviewedPreprint::STAGE_PREVIEW)
            ->__invoke();

        $this->assertSame(ReviewedPreprint::STAGE_PREVIEW, $reviewedPreprint->getStage());
    }

    /**
     * @test
     */
    final public function it_may_have_a_doi()
    {
        $with = $this->builder
            ->withDoi('10.7554/eLife.14107')
            ->__invoke();
        $withOut = $this->builder
            ->withDoi(null)
            ->__invoke();

        $this->assertInstanceOf(HasDoi::class, $with);
        $this->assertSame('10.7554/eLife.14107', $with->getDoi());
        $this->assertNull($withOut->getDoi());
    }

    /**
     * @test
     */
    final public function it_may_have_an_author_line()
    {
        $with = $this->builder
            ->withAuthorLine('Lee R Berger, John Hawks ... Bernhard Zipfel')
            ->__invoke();
        $withOut = $this->builder
            ->withAuthorLine(null)
            ->__invoke();

        $this->assertSame('Lee R Berger, John Hawks ... Bernhard Zipfel', $with->getAuthorLine());
        $this->assertNull($withOut->getAuthorLine());
    }

    /**
     * @test
     */
    final public function it_may_have_a_title_prefix()
    {
        $with = $this->builder
            ->withTitlePrefix('title prefix')
            ->__invoke();
        $withOut = $this->builder
            ->withTitlePrefix(null)
            ->__invoke();

        $this->assertSame('title prefix', $with->getTitlePrefix());
        $this->assertNull($withOut->getTitlePrefix());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $reviewedPreprint = $this->builder
            ->withTitle('title')
            ->__invoke();

        $this->assertSame('title', $reviewedPreprint->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_a_published_date()
    {
        $with = $this->builder
            ->withPublished($publishedDate = new DateTimeImmutable('now', new DateTimeZone('Z')))
            ->__invoke();
        $withOut = $this->builder
            ->withPublished(null)
            ->__invoke();

        $this->assertInstanceOf(HasPublishedDate::class, $with);
        $this->assertEquals($publishedDate, $with->getPublishedDate());
        $this->assertNull($withOut->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_a_status_date()
    {
        $with = $this->builder
            ->withStatusDate($statusDate = new DateTimeImmutable('now', new DateTimeZone('Z')))
            ->__invoke();
        $withOut = $this->builder
            ->withStatusDate(null)
            ->__invoke();

        $this->assertEquals($statusDate, $with->getStatusDate());
        $this->assertNull($withOut->getStatusDate());
    }

    /**
     * @test
     */
    public function it_may_have_a_reviewed_date()
    {
        $with = $this->builder
            ->withReviewedDate($reviewedDate = new DateTimeImmutable('now', new DateTimeZone('Z')))
            ->__invoke();
        $withOut = $this->builder
            ->withReviewedDate(null)
            ->__invoke();

        $this->assertInstanceOf(HasReviewedDate::class, $with);
        $this->assertEquals($reviewedDate, $with->getReviewedDate());
        $this->assertNull($withOut->getReviewedDate());
    }

    /**
     * @test
     */
    public function it_may_have_a_version_date()
    {
        $with = $this->builder
            ->withVersionDate($reviewedDate = new DateTimeImmutable('now', new DateTimeZone('Z')))
            ->__invoke();
        $withOut = $this->builder
            ->withVersionDate(null)
            ->__invoke();

        $this->assertEquals($reviewedDate, $with->getVersionDate());
        $this->assertNull($withOut->getVersionDate());
    }

    /**
     * @test
     */
    public function it_has_a_status()
    {
        $reviewedPreprint = $this->builder
            ->withStatus('status')
            ->__invoke();

        $this->assertSame('status', $reviewedPreprint->getStatus());
    }

    /**
     * @test
     */
    final public function it_may_have_a_volume()
    {
        $with = $this->builder
            ->withVolume(5)
            ->__invoke();
        $withOut = $this->builder
            ->withVolume(null)
            ->__invoke();

        $this->assertSame(5, $with->getVolume());
        $this->assertNull($withOut->getVolume());
    }

    /**
     * @test
     */
    final public function it_may_have_a_version()
    {
        $with = $this->builder
            ->withVersion(2)
            ->__invoke();
        $withOut = $this->builder
            ->withVersion(null)
            ->__invoke();

        $this->assertSame(2, $with->getVersion());
        $this->assertNull($withOut->getVersion());
    }

    /**
     * @test
     */
    final public function it_may_have_an_elocation_id()
    {
        $with = $this->builder
            ->withElocationId('e14107')
            ->__invoke();
        $withOut = $this->builder
            ->withElocationId(null)
            ->__invoke();

        $this->assertSame('e14107', $with->getElocationId());
        $this->assertNull($withOut->getElocationId());
    }

    /**
     * @test
     */
    final public function it_may_have_a_pdf()
    {
        $with = $this->builder
            ->withPdf('http://www.example.com/pdf')
            ->__invoke();
        $withOut = $this->builder
            ->withPdf(null)
            ->__invoke();

        $this->assertInstanceOf(HasPdf::class, $with);
        $this->assertSame('http://www.example.com/pdf', $with->getPdf());
        $this->assertNull($withOut->getPdf());
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
    final public function it_may_have_curation_labels()
    {
        $with = $this->builder
            ->withCurationLabels(['curation label'])
            ->__invoke();
        $withOut = $this->builder
            ->withCurationLabels([])
            ->__invoke();

        $this->assertInstanceOf(HasCurationLabels::class, $with);
        $this->assertSame(['curation label'], $with->getCurationLabels());
        $this->assertEmpty($withOut->getCurationLabels());
    }

    /**
     * @test
     */
    public function it_may_have_a_thumbnail()
    {
        $with = $this->builder
            ->withThumbnail($thumbnail = Builder::for(Image::class)->sample('thumbnail'))
            ->__invoke();
        $withOut = $this->builder
            ->withThumbnail(null)
            ->__invoke();

        $this->assertInstanceOf(HasThumbnail::class, $with);
        $this->assertEquals($thumbnail, $with->getThumbnail());
        $this->assertNull($withOut->getThumbnail());
    }

    /**
     * @test
     */
    final public function it_may_have_index_content()
    {
        $with = $this->builder
            ->withPromiseOfIndexContent('index content')
            ->__invoke();
        $withOut = $this->builder
            ->withPromiseOfIndexContent(null)
            ->__invoke();

        $this->assertSame('index content', $with->getIndexContent());
        $this->assertNull($withOut->getIndexContent());
    }

    /**
     * @test
     */
    final public function it_may_have_elife_assessment()
    {
        $elifeAssessment = Builder::dummy(ElifeAssessment::class);
        $with = $this->builder
             ->withElifeAssessment($elifeAssessment)
             ->__invoke();
        $withOut = $this->builder
            ->withElifeAssessment(null)
            ->__invoke();

        $this->assertSame($elifeAssessment, $with->getElifeAssessment());
        $this->assertNull($withOut->getElifeAssessment());
    }
}
