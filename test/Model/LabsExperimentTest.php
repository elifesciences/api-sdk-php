<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\HasBanner;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\LabsExperiment;
use eLife\ApiSdk\Model\Model;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class LabsExperimentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $labsExperiment = new LabsExperiment(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        $this->assertInstanceOf(Model::class, $labsExperiment);
    }

    /**
     * @test
     */
    public function it_has_a_number()
    {
        $labsExperiment = new LabsExperiment(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        $this->assertSame(1, $labsExperiment->getNumber());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $labsExperiment = new LabsExperiment(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        $this->assertSame('title', $labsExperiment->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new LabsExperiment(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, 'impact statement',
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );
        $withOut = new LabsExperiment(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
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
        $labsExperiment = new LabsExperiment(1, 'title', $date = new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        $this->assertInstanceOf(HasPublishedDate::class, $labsExperiment);
        $this->assertEquals($date, $labsExperiment->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_updated_date()
    {
        $with = new LabsExperiment(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), $updated = new DateTimeImmutable('now', new DateTimeZone('Z')), 'impact statement',
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );
        $withOut = new LabsExperiment(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($updated, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    /**
     * @test
     */
    public function it_has_a_banner()
    {
        $labsExperiment = new LabsExperiment(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            promise_for($image = new Image('', [900 => 'https://placehold.it/900x450'])),
            $image, new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        $this->assertInstanceOf(HasBanner::class, $labsExperiment);
        $this->assertEquals($image, $labsExperiment->getBanner());
    }

    /**
     * @test
     */
    public function it_has_a_thumbnail()
    {
        $labsExperiment = new LabsExperiment(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            rejection_for('No banner'), $image = new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        $this->assertInstanceOf(HasThumbnail::class, $labsExperiment);
        $this->assertEquals($image, $labsExperiment->getThumbnail());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [new Block\Paragraph('foo')];

        $labsExperiment = new LabsExperiment(1, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            rejection_for('No banner'), new Image('', [900 => 'https://placehold.it/900x450']), new ArraySequence($content)
        );

        $this->assertInstanceOf(HasContent::class, $labsExperiment);
        $this->assertEquals($content, $labsExperiment->getContent()->toArray());
    }
}
