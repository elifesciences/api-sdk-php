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
use eLife\ApiSdk\Model\HasThumbnail;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Interviewee;
use eLife\ApiSdk\Model\PersonDetails;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class InterviewTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_an_identifier()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasIdentifier::class, $interview);
        $this->assertEquals(Identifier::interview('id'), $interview->getIdentifier());
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasId::class, $interview);
        $this->assertSame('id', $interview->getId());
    }

    /**
     * @test
     */
    public function it_has_an_interviewee()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertEquals($interviewee, $interview->getInterviewee());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertSame('title', $interview->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $intervieweeWith = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $intervieweeWithOut = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));

        $with = new Interview('id', $intervieweeWith, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, 'impact statement', null, rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );
        $withOut = new Interview('id', $intervieweeWithOut, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_may_have_a_thumbnail()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));

        $with = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, $image = Builder::for(Image::class)->sample('thumbnail'), rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );
        $withOut = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasThumbnail::class, $with);
        $this->assertEquals($image, $with->getThumbnail());
        $this->assertNull($withOut->getThumbnail());
    }

    /**
     * @test
     */
    public function it_may_have_a_social_image()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));

        $with = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, promise_for($image = Builder::for(Image::class)->sample('social')),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );
        $withOut = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, promise_for(null),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasSocialImage::class, $with);
        $this->assertEquals($image, $with->getSocialImage());
        $this->assertNull($withOut->getSocialImage());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', $date = new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasPublishedDate::class, $interview);
        $this->assertEquals($date, $interview->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_updated_date()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));
        $with = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), $date = new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );
        $withOut = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, rejection_for('No social image'),
            new PromiseSequence(rejection_for('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($date, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [new Block\Paragraph('foo')];

        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(rejection_for('Full interviewee should not be unwrapped')));

        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, rejection_for('No social image'),
            new ArraySequence($content)
        );

        $this->assertInstanceOf(HasContent::class, $interview);
        $this->assertEquals($content, $interview->getContent()->toArray());
    }
}
