<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Digest;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use function GuzzleHttp\Promise\rejection_for;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class DigestTest extends TestCase
{
    private $builder;

    public function setUp()
    {
        $this->builder = Builder::for(Digest::class);
    }

    /**
     * @test
     */
    public function it_is_a_model()
    {
        $digest = $this->builder->__invoke();

        $this->assertInstanceOf(Model::class, $digest);
    }

    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $digest = $this->builder
            ->withId('id')
            ->__invoke();

        $this->assertInstanceOf(HasIdentifier::class, $digest);
        $this->assertEquals(Identifier::digest('id'), $digest->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $digest = $this->builder
            ->withId('id')
            ->__invoke();

        $this->assertInstanceOf(HasId::class, $digest);
        $this->assertSame('id', $digest->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $digest = $this->builder
            ->withTitle('title')
            ->__invoke();

        $this->assertSame('title', $digest->getTitle());
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
    public function it_has_a_stage()
    {
        $digest = $this->builder
            ->withStage('published')
            ->__invoke();

        $this->assertInstanceOf(Digest::class, $digest);
        $this->assertSame('published', $digest->getStage());
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
    public function it_may_have_an_updated_date()
    {
        $with = $this->builder
            ->withUpdated($updatedDate = new DateTimeImmutable('now', new DateTimeZone('Z')))
            ->__invoke();
        $withOut = $this->builder
            ->withUpdated(null)
            ->__invoke();

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($updatedDate, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    /**
     * @test
     */
    public function it_has_a_thumbnail()
    {
        $digest = $this->builder
            ->withThumbnail($image = Builder::for(Image::class)->sample('thumbnail'))
            ->__invoke();

        $this->assertInstanceOf(HasThumbnail::class, $digest);
        $this->assertEquals($image, $digest->getThumbnail());
    }

    /**
     * @test
     * @dataProvider subjectsProvider
     */
    public function it_may_have_subjects(Sequence $subjects = null, array $expected)
    {
        $digest = $this->builder
            ->withSubjects($subjects)
            ->__invoke();

        $this->assertInstanceOf(HasSubjects::class, $digest);
        $this->assertEquals($expected, $digest->getSubjects()->toArray());
    }

    public function subjectsProvider() : array
    {
        $subjects = [
            new Subject('subject1', 'Subject 1', rejection_for('Subject impact statement should not be unwrapped'),
                new PromiseSequence(rejection_for('Subject aims and scope should not be unwrapped')),
                rejection_for('No banner'), rejection_for('Subject image should not be unwrapped')),
            new Subject('subject2', 'Subject 2', rejection_for('Subject impact statement should not be unwrapped'),
                new PromiseSequence(rejection_for('Subject aims and scope should not be unwrapped')),
                rejection_for('No banner'), rejection_for('Subject image should not be unwrapped')),
        ];

        return [
            'none' => [
                new EmptySequence(),
                [],
            ],
            'collection' => [
                new ArraySequence($subjects),
                $subjects,
            ],
        ];
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $digest = $this->builder
            ->withContent($content = new ArraySequence([
                new Paragraph('summary'),
            ]))
            ->__invoke();

        $this->assertInstanceOf(HasContent::class, $digest);
        $this->assertEquals($content, $digest->getContent());
    }
}
