<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use DateTimeZone;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Collection\EmptySequence;
use eLife\ApiSdk\Collection\PromiseSequence;
use eLife\ApiSdk\Collection\Sequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\HasContent;
use eLife\ApiSdk\Model\HasId;
use eLife\ApiSdk\Model\HasImpactStatement;
use eLife\ApiSdk\Model\HasPublishedDate;
use eLife\ApiSdk\Model\HasSubjects;
use eLife\ApiSdk\Model\HasUpdatedDate;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use test\eLife\ApiSdk\Builder;
use function GuzzleHttp\Promise\rejection_for;

final class BlogArticleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_model()
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertInstanceOf(Model::class, $blogArticle);
    }

    /**
     * @test
     */
    public function it_has_an_id()
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertInstanceOf(HasId::class, $blogArticle);
        $this->assertSame('id', $blogArticle->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertSame('title', $blogArticle->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new BlogArticle('id', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, 'impact statement',
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );
        $withOut = new BlogArticle('id', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
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
        $blogArticle = new BlogArticle('id', 'title', $date = new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertInstanceOf(HasPublishedDate::class, $blogArticle);
        $this->assertEquals($date, $blogArticle->getPublishedDate());
    }

    /**
     * @test
     */
    public function it_may_have_an_updated_date()
    {
        $with = new BlogArticle('id', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), $date = new DateTimeImmutable('now', new DateTimeZone('Z')), null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );
        $withOut = new BlogArticle('id', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertInstanceOf(HasUpdatedDate::class, $with);
        $this->assertEquals($date, $with->getUpdatedDate());
        $this->assertNull($withOut->getUpdatedDate());
    }

    /**
     * @test
     * @dataProvider subjectsProvider
     */
    public function it_may_have_subjects(Sequence $subjects = null, array $expected)
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null,
            new PromiseSequence(rejection_for('Full blog article should not be unwrapped')), $subjects
        );

        $this->assertInstanceOf(HasSubjects::class, $blogArticle);
        $this->assertEquals($expected, $blogArticle->getSubjects()->toArray());
    }

    public function subjectsProvider() : array
    {
        $subjects = [
            new Subject('subject1', 'Subject 1', rejection_for('Subject impact statement should not be unwrapped'),
                rejection_for('No banner'), rejection_for('Subject image should not be unwrapped')),
            new Subject('subject2', 'Subject 2', rejection_for('Subject impact statement should not be unwrapped'),
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
        $content = [
            new Block\Paragraph('foo'),
            new Block\Image(null, null, new EmptySequence(), new EmptySequence(), Builder::for(Image::class)->__invoke(), [], []),
            new Block\YouTube('foo', 300, 200),
        ];

        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable('now', new DateTimeZone('Z')), null, null, new ArraySequence($content),
            new PromiseSequence(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertInstanceOf(HasContent::class, $blogArticle);
        $this->assertEquals($content, $blogArticle->getContent()->toArray());
    }
}
