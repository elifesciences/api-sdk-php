<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Model\File;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ReviewedPreprint;
use PHPUnit_Framework_TestCase;
use DateTimeImmutable;

final class ReviewedPreprintTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    private $reviewedPreprint;
    private $emptyReviewedPreprint;

    public function setUp()
    {
        $this->reviewedPreprint = new ReviewedPreprint(
            'id',
            'title',
            'status',
            'stage',
            'indexContent',
            'doi',
            'authorLine',
            'titlePrefix',
            new DateTimeImmutable('2016-09-16T12:34:56Z'),
            new DateTimeImmutable('2016-09-16T12:34:56Z'),
            new DateTimeImmutable('2016-09-16T12:34:56Z'),
            1,
            'elocationId',
            'pdf',
            new ArraySequence(["subject"]),
            new ArraySequence(["curation-label"]),
            new Image('altText', 'uri', new EmptySequence(), new File('', '', ''), '1', '2', '3', '4')
        );
        $this->emptyReviewedPreprint = new ReviewedPreprint('id', 'title', 'status', 'stage');
    }

    public function it_is_a_model()
    {
        $reviewedPreprint = new ReviewedPreprint('id', 'title', 'status', 'stage');
        $this->assertInstanceOf(ReviewedPreprint::class, $reviewedPreprint);
    }

    /**
     * @test
     */
    public function it_have_id()
    {
        $this->assertEquals('id', $this->reviewedPreprint->getId());
    }

    /**
     * @test
     */
    public function it_may_have_title()
    {
        $this->assertEquals('title', $this->reviewedPreprint->getTitle());
    }

    /**
     * @test
     */
    public function it_have_status()
    {
        $this->assertEquals('status', $this->reviewedPreprint->getStatus());
    }

    /**
     * @test
     */
    public function it_have_stage()
    {
        $this->assertEquals('stage', $this->reviewedPreprint->getStage());
    }

    /**
     * @test
     */
    public function it_may_have_index_content()
    {
        $this->assertEquals('indexContent', $this->reviewedPreprint->getIndexContent());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getIndexContent());
    }

    /**
     * @test
     */
    public function it_may_have_doi()
    {
        $this->assertEquals('doi', $this->reviewedPreprint->getDoi());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getDoi());
    }

    /**
     * @test
     */
    public function it_may_have_author_line()
    {
        $this->assertEquals('authorLine', $this->reviewedPreprint->getAuthorLine());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getAuthorLine());
    }

    /**
     * @test
     */
    public function it_may_have_title_prefix()
    {
        $this->assertEquals('titlePrefix', $this->reviewedPreprint->getTitlePrefix());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getTitlePrefix());
    }

    /**
     * @test
     */
    public function it_may_have_published()
    {
        $this->assertEquals(new DateTimeImmutable('2016-09-16T12:34:56Z'), $this->reviewedPreprint->getPublishedDate());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_reviewed_date()
    {
        $this->assertEquals(new DateTimeImmutable('2016-09-16T12:34:56Z'), $this->reviewedPreprint->getReviewedDate());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getReviewedDate());
    }

    /**
     * @test
     */
    public function it_may_have_status_date()
    {
        $this->assertEquals(new DateTimeImmutable('2016-09-16T12:34:56Z'), $this->reviewedPreprint->getStatusDate());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getStatusDate());
    }

    /**
     * @test
     */
    public function it_may_have_volume()
    {
        $this->assertEquals(1, $this->reviewedPreprint->getVolume());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getVolume());
    }

    /**
     * @test
     */
    public function it_may_have_elocation_id()
    {
        $this->assertEquals('elocationId', $this->reviewedPreprint->getElocationId());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getElocationId());
    }

    /**
     * @test
     */
    public function it_may_have_pdf()
    {
        $this->assertEquals('pdf', $this->reviewedPreprint->getPdf());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getPdf());
    }

    /**
     * @test
     */
    public function it_may_have_subjects()
    {
        $this->assertEquals(['subject'], $this->reviewedPreprint->getSubjects()->toArray());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getSubjects());
    }

    /**
     * @test
     */
    public function it_may_have_curation_labels()
    {
        $this->assertEquals(['curation-label'], $this->reviewedPreprint->getCurationLabels()->toArray());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getCurationLabels());
    }

    /**
     * @test
     */
    public function it_may_have_image()
    {
        $this->assertEquals('altText', $this->reviewedPreprint->getImage()->getAltText());
        $this->assertEquals('uri', $this->reviewedPreprint->getImage()->getUri());
        $this->assertEquals('1', $this->reviewedPreprint->getImage()->getWidth());
        $this->assertEquals('2', $this->reviewedPreprint->getImage()->getHeight());
        $this->assertEquals('3', $this->reviewedPreprint->getImage()->getFocalPointX());
        $this->assertEquals('4', $this->reviewedPreprint->getImage()->getFocalPointY());
        $this->assertEquals(null, $this->emptyReviewedPreprint->getImage());
    }
}
