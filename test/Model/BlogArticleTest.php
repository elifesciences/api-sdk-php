<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection;
use eLife\ApiSdk\Collection\ArrayCollection;
use eLife\ApiSdk\Collection\PromiseCollection;
use eLife\ApiSdk\Model\Block\Image as ImageBlock;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\YouTube;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Subject;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\rejection_for;

final class BlogArticleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            new PromiseCollection(rejection_for('Full blog article should not be unwrapped')),
            new PromiseCollection(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertSame('id', $blogArticle->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            new PromiseCollection(rejection_for('Full blog article should not be unwrapped')),
            new PromiseCollection(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertSame('title', $blogArticle->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new BlogArticle('id', 'title', new DateTimeImmutable(), 'impact statement',
            new PromiseCollection(rejection_for('Full blog article should not be unwrapped')),
            new PromiseCollection(rejection_for('Subjects should not be unwrapped'))
        );
        $withOut = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            new PromiseCollection(rejection_for('Full blog article should not be unwrapped')),
            new PromiseCollection(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertSame('impact statement', $with->getImpactStatement());
        $this->assertNull($withOut->getImpactStatement());
    }

    /**
     * @test
     */
    public function it_has_a_published_date()
    {
        $blogArticle = new BlogArticle('id', 'title', $date = new DateTimeImmutable(), null,
            new PromiseCollection(rejection_for('Full blog article should not be unwrapped')),
            new PromiseCollection(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertEquals($date, $blogArticle->getPublishedDate());
    }

    /**
     * @test
     * @dataProvider subjectsProvider
     */
    public function it_may_have_subjects(Collection $subjects = null, bool $hasSubjects, array $expected)
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            new PromiseCollection(rejection_for('Full blog article should not be unwrapped')), $subjects
        );

        $this->assertSame($hasSubjects, $blogArticle->hasSubjects());
        $this->assertEquals($expected, $blogArticle->getSubjects()->toArray());
    }

    public function subjectsProvider() : array
    {
        $image = new Image('', [new ImageSize('2:1', [900 => 'https://placehold.it/900x450'])]);

        $subjects = [
            new Subject('subject1', 'Subject 1', null, $image),
            new Subject('subject2', 'Subject 2', null, $image),
        ];

        return [
            'none' => [
                null,
                false,
                [],
            ],
            'collection' => [
                new ArrayCollection($subjects),
                true,
                $subjects,
            ],
        ];
    }

    /**
     * @test
     */
    public function it_does_not_unwrap_subjects_when_checking_if_it_has_any()
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            new PromiseCollection(rejection_for('Full blog article should not be unwrapped')),
            new PromiseCollection(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertTrue($blogArticle->hasSubjects());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $content = [
            new Paragraph('foo'),
            new ImageBlock('http://www.example.com/image.jpg', 'foo'),
            new ImageBlock('http://www.example.com/image.jpg', '', 'bar'),
            new YouTube('foo', 300, 200),
        ];

        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null, new ArrayCollection($content),
            new PromiseCollection(rejection_for('Subjects should not be unwrapped'))
        );

        $this->assertEquals($content, $blogArticle->getContent()->toArray());
    }
}
