<?php

namespace test\eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Model\Block\Image as ImageBlock;
use eLife\ApiSdk\Model\Block\Paragraph;
use eLife\ApiSdk\Model\Block\YouTube;
use eLife\ApiSdk\Model\BlogArticle;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ImageSize;
use eLife\ApiSdk\Model\Subject;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit_Framework_TestCase;
use function GuzzleHttp\Promise\promise_for;
use function GuzzleHttp\Promise\rejection_for;

final class BlogArticleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_has_an_id()
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            rejection_for('Full blog article should not be unwrapped'),
            rejection_for('Subjects should not be unwrapped')
        );

        $this->assertSame('id', $blogArticle->getId());
    }

    /**
     * @test
     */
    public function it_has_a_title()
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            rejection_for('Full blog article should not be unwrapped'),
            rejection_for('Subjects should not be unwrapped')
        );

        $this->assertSame('title', $blogArticle->getTitle());
    }

    /**
     * @test
     */
    public function it_may_have_an_impact_statement()
    {
        $with = new BlogArticle('id', 'title', new DateTimeImmutable(), 'impact statement',
            rejection_for('Full blog article should not be unwrapped'),
            rejection_for('Subjects should not be unwrapped')
        );
        $withOut = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            rejection_for('Full blog article should not be unwrapped'),
            rejection_for('Subjects should not be unwrapped')
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
            rejection_for('Full blog article should not be unwrapped'),
            rejection_for('Subjects should not be unwrapped')
        );

        $this->assertEquals($date, $blogArticle->getPublishedDate());
    }

    /**
     * @test
     * @dataProvider subjectsProvider
     */
    public function it_may_have_subjects(PromiseInterface $subjects = null, bool $hasSubjects, array $expected)
    {
        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null,
            rejection_for('Full blog article should not be unwrapped'), $subjects
        );

        $this->assertSame($hasSubjects, $blogArticle->hasSubjects());
        $this->assertEquals($expected, $blogArticle->getSubjects());
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
            'promise' => [
                promise_for($subjects),
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
            rejection_for('Full blog article should not be unwrapped'),
            rejection_for('Subjects should not be unwrapped')
        );

        $this->assertTrue($blogArticle->hasSubjects());
    }

    /**
     * @test
     */
    public function it_has_content()
    {
        $contentJson = [
            [
                'type' => 'paragraph',
                'text' => 'foo',
            ],
            [
                'type' => 'image',
                'uri' => 'http://www.example.com/image.jpg',
                'alt' => 'foo',
            ],
            [
                'type' => 'image',
                'uri' => 'http://www.example.com/image.jpg',
                'alt' => '',
                'caption' => 'bar',
            ],
            [
                'type' => 'youtube',
                'id' => 'foo',
                'width' => 300,
                'height' => 200,
            ],
        ];

        $contentBlocks = [
            new Paragraph('foo'),
            new ImageBlock('http://www.example.com/image.jpg', 'foo'),
            new ImageBlock('http://www.example.com/image.jpg', '', 'bar'),
            new YouTube('foo', 300, 200),
        ];

        $blogArticle = new BlogArticle('id', 'title', new DateTimeImmutable(), null, promise_for($contentJson),
            rejection_for('Subjects should not be unwrapped')
        );

        $this->assertEquals($contentBlocks, $blogArticle->getContent());
    }
}
