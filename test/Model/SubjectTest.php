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
use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Promise\Create;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class SubjectTest extends TestCase
{
    #[Test]
    public function it_is_a_model()
    {
        $subject = new Subject('id', 'name', Create::rejectionFor('Impact statement should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Aims and scope should not be unwrapped')), Create::rejectionFor('No banner'),
            Create::rejectionFor('Image should not be unwrapped'));

        $this->assertInstanceOf(Model::class, $subject);
    }

    #[Test]
    public function it_has_an_identifier()
    {
        $subject = new Subject('id', 'name', Create::rejectionFor('Impact statement should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Aims and scope should not be unwrapped')), Create::rejectionFor('No banner'),
            Create::rejectionFor('Image should not be unwrapped'));

        $this->assertInstanceOf(HasIdentifier::class, $subject);
        $this->assertEquals(Identifier::subject('id'), $subject->getIdentifier());
    }

    #[Test]
    public function it_has_an_id()
    {
        $subject = new Subject('id', 'name', Create::rejectionFor('Impact statement should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Aims and scope should not be unwrapped')), Create::rejectionFor('No banner'),
            Create::rejectionFor('Image should not be unwrapped'));

        $this->assertInstanceOf(HasId::class, $subject);
        $this->assertSame('id', $subject->getId());
    }

    #[Test]
    public function it_has_a_name()
    {
        $subject = new Subject('id', 'name', Create::rejectionFor('Impact statement should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Aims and scope should not be unwrapped')), Create::rejectionFor('No banner'),
            Create::rejectionFor('Image should not be unwrapped'));

        $this->assertSame('name', $subject->getName());
    }

    #[Test]
    public function it_may_have_an_impact_statement()
    {
        $with = new Subject('id', 'name', Create::promiseFor('impact statement'),
            new PromiseSequence(Create::rejectionFor('Aims and scope should not be unwrapped')), Create::rejectionFor('No banner'),
            Create::rejectionFor('Image should not be unwrapped'));
        $withOut = new Subject('id', 'name', Create::promiseFor(null),
            new PromiseSequence(Create::rejectionFor('Aims and scope should not be unwrapped')), Create::rejectionFor('No banner'),
            Create::rejectionFor('Image should not be unwrapped'));

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    #[Test]
    public function it_may_have_an_aims_and_scope()
    {
        $with = new Subject('id', 'name', Create::rejectionFor('Impact statement should not be unwrapped'),
            $aimsAndScope = new ArraySequence([new Paragraph('Aims and scope')]), Create::rejectionFor('No banner'),
            Create::rejectionFor('Image should not be unwrapped'));
        $withOut = new Subject('id', 'name', Create::rejectionFor('Impact statement should not be unwrapped'),
            new EmptySequence(), Create::rejectionFor('No banner'),
            Create::rejectionFor('Image should not be unwrapped'));

        $this->assertEquals($aimsAndScope, $with->getAimsAndScope());
        $this->assertCount(0, $withOut->getAimsAndScope());
    }

    #[Test]
    public function it_has_a_banner()
    {
        $subject = new Subject('id', 'name', Create::rejectionFor('Impact statement should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Aims and scope should not be unwrapped')),
            Create::promiseFor($image = Builder::for(Image::class)->sample('thumbnail')),
            Create::rejectionFor('No thumbnail'));

        $this->assertInstanceOf(HasBanner::class, $subject);
        $this->assertEquals($image, $subject->getBanner());
    }

    #[Test]
    public function it_has_a_thumbnail()
    {
        $subject = new Subject('id', 'name', Create::rejectionFor('Impact statement should not be unwrapped'),
            new PromiseSequence(Create::rejectionFor('Aims and scope should not be unwrapped')), Create::rejectionFor('No banner'),
            Create::promiseFor($image = Builder::for(Image::class)->sample('thumbnail')));

        $this->assertInstanceOf(HasThumbnail::class, $subject);
        $this->assertEquals($image, $subject->getThumbnail());
    }
}
