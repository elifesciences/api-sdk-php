<?php

namespace test\eLife\ApiSdk\Model;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\HasBanner;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasIdentifier;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;

final class SubjectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $subject = new Subject('id', 'name', rejection_for('Impact statement should not be unwrapped'),
            new PromiseSequence(rejection_for('Aims and scope should not be unwrapped')), rejection_for('No banner'),
            rejection_for('Image should not be unwrapped'));

        $this->assertInstanceOf(Model::class, $subject);
    }

    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $subject = new Subject('id', 'name', rejection_for('Impact statement should not be unwrapped'),
            new PromiseSequence(rejection_for('Aims and scope should not be unwrapped')), rejection_for('No banner'),
            rejection_for('Image should not be unwrapped'));

        $this->assertInstanceOf(HasIdentifier::class, $subject);
        $this->assertEquals(Identifier::subject('id'), $subject->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $subject = new Subject('id', 'name', rejection_for('Impact statement should not be unwrapped'),
            new PromiseSequence(rejection_for('Aims and scope should not be unwrapped')), rejection_for('No banner'),
            rejection_for('Image should not be unwrapped'));

        $this->assertInstanceOf(HasId::class, $subject);
        $this->assertSame('id', $subject->getId());
    }

    /**
     * @test
     */
    public function it_has_a_name()
    {
        $subject = new Subject('id', 'name', rejection_for('Impact statement should not be unwrapped'),
            new PromiseSequence(rejection_for('Aims and scope should not be unwrapped')), rejection_for('No banner'),
            rejection_for('Image should not be unwrapped'));

        $this->assertSame('name', $subject->getName());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new Subject('id', 'name', promise_for('impact statement'),
            new PromiseSequence(rejection_for('Aims and scope should not be unwrapped')), rejection_for('No banner'),
            rejection_for('Image should not be unwrapped'));
        $withOut = new Subject('id', 'name', promise_for(null),
            new PromiseSequence(rejection_for('Aims and scope should not be unwrapped')), rejection_for('No banner'),
            rejection_for('Image should not be unwrapped'));

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_may_have_an_aims_and_scope()
    {
        $with = new Subject('id', 'name', rejection_for('Impact statement should not be unwrapped'),
            $aimsAndScope = new ArraySequence([new Paragraph('Aims and scope')]), rejection_for('No banner'),
            rejection_for('Image should not be unwrapped'));
        $withOut = new Subject('id', 'name', rejection_for('Impact statement should not be unwrapped'),
            new EmptySequence(), rejection_for('No banner'),
            rejection_for('Image should not be unwrapped'));

        $this->assertEquals($aimsAndScope, $with->getAimsAndScope());
        $this->assertCount(0, $withOut->getAimsAndScope());
    }

    /**
     * @test
     */
    public function it_has_a_banner()
    {
        $subject = new Subject('id', 'name', rejection_for('Impact statement should not be unwrapped'),
            new PromiseSequence(rejection_for('Aims and scope should not be unwrapped')),
            promise_for($image = Builder::for(Image::class)->sample('thumbnail')),
            rejection_for('No thumbnail'));

        $this->assertInstanceOf(HasBanner::class, $subject);
        $this->assertEquals($image, $subject->getBanner());
    }

    /**
     * @test
     */
    public function it_has_a_thumbnail()
    {
        $subject = new Subject('id', 'name', rejection_for('Impact statement should not be unwrapped'),
            new PromiseSequence(rejection_for('Aims and scope should not be unwrapped')), rejection_for('No banner'),
            promise_for($image = Builder::for(Image::class)->sample('thumbnail')));

        $this->assertInstanceOf(HasThumbnail::class, $subject);
        $this->assertEquals($image, $subject->getThumbnail());
    }
}
