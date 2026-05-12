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
use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Promise\Create;
use PHPUnit\Framework\TestCase;
use test\eLife\ApiSdk\Builder;

final class InterviewTest extends TestCase
{
    #[Test]
    public function it_has_an_identifier()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(Create::rejectionFor('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, Create::rejectionFor('No social image'),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasIdentifier::class, $interview);
        $this->assertEquals(Identifier::interview('id'), $interview->getIdentifier());
    }

    #[Test]
    public function it_has_an_id()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(Create::rejectionFor('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, Create::rejectionFor('No social image'),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasId::class, $interview);
        $this->assertSame('id', $interview->getId());
    }

    #[Test]
    public function it_has_an_interviewee()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(Create::rejectionFor('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, Create::rejectionFor('No social image'),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );

        $this->assertEquals($interviewee, $interview->getInterviewee());
    }

    #[Test]
    public function it_has_a_title()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(Create::rejectionFor('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, Create::rejectionFor('No social image'),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );

        $this->assertSame('title', $interview->getTitle());
    }

    #[Test]
    public function it_may_have_an_impact_statement()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $intervieweeWith = new Interviewee($person,
            new PromiseSequence(Create::rejectionFor('Full interviewee should not be unwrapped')));
        $intervieweeWithOut = new Interviewee($person,
            new PromiseSequence(Create::rejectionFor('Full interviewee should not be unwrapped')));

        $with = new Interview('id', $intervieweeWith, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, 'impact statement', null, Create::rejectionFor('No social image'),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );
        $withOut = new Interview('id', $intervieweeWithOut, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, Create::rejectionFor('No social image'),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasImpactStatement::class, $with);
        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    #[Test]
    public function it_may_have_a_thumbnail()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(Create::rejectionFor('Full interviewee should not be unwrapped')));

        $with = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, $image = Builder::for(Image::class)->sample('thumbnail'), Create::rejectionFor('No social image'),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );
        $withOut = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, Create::rejectionFor('No social image'),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasThumbnail::class, $with);
        $this->assertEquals($image, $with->getThumbnail());
        $this->assertNull($withOut->getThumbnail());
    }

    #[Test]
    public function it_may_have_a_social_image()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(Create::rejectionFor('Full interviewee should not be unwrapped')));

        $with = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, Create::promiseFor($image = Builder::for(Image::class)->sample('social')),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );
        $withOut = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, Create::promiseFor(null),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasSocialImage::class, $with);
        $this->assertEquals($image, $with->getSocialImage());
        $this->assertNull($withOut->getSocialImage());
    }

    #[Test]
    public function it_has_a_published_date()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(Create::rejectionFor('Full interviewee should not be unwrapped')));
        $interview = new Interview('id', $interviewee, 'title', $date = new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, Create::rejectionFor('No social image'),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasPublishedDate::class, $interview);
        $this->assertEquals($date, $interview->getPublishedDate());
    }

    #[Test]
    public function it_may_have_an_updated_date()
    {
        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(Create::rejectionFor('Full interviewee should not be unwrapped')));
        $with = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), $date = new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, Create::rejectionFor('No social image'),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );
        $withOut = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, Create::rejectionFor('No social image'),
            new PromiseSequence(Create::rejectionFor('Full interview should not be unwrapped'))
        );

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($date, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    #[Test]
    public function it_has_content()
    {
        $content = [new Block\Paragraph('foo')];

        $person = new PersonDetails('preferred name', 'index name');
        $interviewee = new Interviewee($person,
            new PromiseSequence(Create::rejectionFor('Full interviewee should not be unwrapped')));

        $interview = new Interview('id', $interviewee, 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, null, Create::rejectionFor('No social image'),
            new ArraySequence($content)
        );

        $this->assertInstanceOf(HasContent::class, $interview);
        $this->assertEquals($content, $interview->getContent()->toArray());
    }
}
