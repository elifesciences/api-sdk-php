<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\LabsExperiment;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\rejection_for;

final class LabsExperimentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_a_number()
    {
        $labsExperiment = new LabsExperiment(1, 'title', new DateTimeImmutable(), null,
            new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        $this->assertSame(1, $labsExperiment->getNumber());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $labsExperiment = new LabsExperiment(1, 'title', new DateTimeImmutable(), null,
            new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        $this->assertSame('title', $labsExperiment->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new LabsExperiment(1, 'title', new DateTimeImmutable(), 'impact statement',
            new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );
        $withOut = new LabsExperiment(1, 'title', new DateTimeImmutable(), null,
            new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $labsExperiment = new LabsExperiment(1, 'title', $date = new DateTimeImmutable(), null,
            new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        $this->assertEquals($date, $labsExperiment->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_has_an_image()
    {
        $labsExperiment = new LabsExperiment(1, 'title', new DateTimeImmutable(), null,
            $image = new Image('', [900 => 'https://placehold.it/900x450']),
            new PromiseSequence(rejection_for('Full Labs experiment should not be unwrapped'))
        );

        $this->assertEquals($image, $labsExperiment->getImage());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [new Block\Paragraph('foo')];

        $labsExperiment = new LabsExperiment(1, 'title', new DateTimeImmutable(), null,
            new Image('', [900 => 'https://placehold.it/900x450']), new ArraySequence($content)
        );

        $this->assertEquals($content, $labsExperiment->getContent()->toArray());
    }
}
