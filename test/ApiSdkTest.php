<?php

namespace test\eLife\ApiSdk;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\AnnualReports;
use eLife\ApiSdk\Client\BlogArticles;
use eLife\ApiSdk\Client\Events;
use eLife\ApiSdk\Client\Interviews;
use eLife\ApiSdk\Client\LabsExperiments;
use eLife\ApiSdk\Client\MediumArticles;
use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\BlogArticle;

final class ApiSdkTest extends ApiTestCase
{
    /**
     * @var ApiSdk
     */
    private $apiSdk;

    /**
     * @before
     */
    protected function setUpBlogArticles()
    {
        $this->apiSdk = new ApiSdk($this->getHttpClient());
    }

    /**
     * @test
     */
    public function it_creates_annual_reports()
    {
        $this->assertInstanceOf(AnnualReports::class, $this->apiSdk->annualReports());

        $this->mockAnnualReportCall(2012);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->annualReports()->get(2012)->wait());
    }

    /**
     * @test
     */
    public function it_creates_blog_articles()
    {
        $this->assertInstanceOf(BlogArticles::class, $this->apiSdk->blogArticles());

        $this->mockBlogArticleCall(7, true);
        $this->mockSubjectCall(1);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->blogArticles()->get('blogArticle7')->wait());
    }

    /**
     * @test
     */
    public function it_creates_events()
    {
        $this->assertInstanceOf(Events::class, $this->apiSdk->events());

        /*$this->mockBlogArticleCall(7, true);
        $this->mockSubjectCall(1);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->blogArticles()->get('blogArticle7')->wait());*/
    }

    /**
     * @test
     */
    public function it_creates_interviews()
    {
        $this->assertInstanceOf(Interviews::class, $this->apiSdk->interviews());

        $this->mockInterviewCall(1);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->interviews()->get('interview1')->wait());
    }

    /**
     * @test
     */
    public function it_creates_labs_experiments()
    {
        $this->assertInstanceOf(LabsExperiments::class, $this->apiSdk->labsExperiments());

        $this->mockLabsExperimentCall(1);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->labsExperiments()->get(1)->wait());
    }

    /**
     * @test
     */
    public function it_creates_medium_articles()
    {
        $this->assertInstanceOf(MediumArticles::class, $this->apiSdk->mediumArticles());

        $this->mockMediumArticleListCall(1, 1, 1);
        $this->mockMediumArticleListCall(1, 100, 1);

        foreach ($this->apiSdk->mediumArticles() as $mediumArticle) {
            $this->apiSdk->getSerializer()->normalize($mediumArticle);
        }
    }

    /**
     * @test
     */
    public function it_creates_subjects()
    {
        $this->assertInstanceOf(Subjects::class, $this->apiSdk->subjects());

        $this->mockSubjectCall(1);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->subjects()->get('subject1')->wait());
    }

    /**
     * @test
     */
    public function it_support_encoding()
    {
        $this->apiSdk->getSerializer()->supportsEncoding('json');
    }

    /**
     * @test
     */
    public function it_support_decoding()
    {
        $this->apiSdk->getSerializer()->supportsDecoding('json');
    }

    /**
     * @test
     * @dataProvider denormalizeBlocksProvider
     */
    public function it_can_denormalize_blocks(string $block)
    {
        $this->apiSdk->getSerializer()->supportsDenormalization([], $block);
    }

    /**
     * @test
     */
    public function is_can_serialize_and_deserialize_blog_articles()
    {
        $serializer = $this->apiSdk->getSerializer();
        // Mock a call that is apparently made? Maybe this should be a part of the JSON serialization.
        // Certainly in search we will want to create out own Subjects Client -
        // See: https://github.com/elifesciences/search/pull/13/files#diff-baab24df189be238c73edb1fdd3c90d3R1
        $this->mockSubjectCall(1);
        // Plain JSON object representation of resource.
        $json = '{"content":[{"type":"paragraph","text":"text"}],"id":"id","impactStatement":"impact statement","published":"2016-09-26T16:40:31+01:00","subjects":["subject1"],"title":"title"}';
        // Double the conversion.
        $deserialized = $serializer->deserialize($json, BlogArticle::class, 'json');
        // Can check contents of `$deserialized` here, but it the code creating is should
        // be covered by the normalize functions.
        $serialized = $serializer->serialize($deserialized, 'json');
        // Check that no information was lost.
        // The body and subjects were lost previously due to the ArrayCollection being
        // passed through to getSubjects() on the subjects aware.
        // I'm not sure why this didn't break the tests since on the method below
        // you are creating an ArrayCollection and normalizing it, but it is not
        // throwing.
        $this->assertJsonStringEqualsJsonString($json, $serialized);
    }

    public function denormalizeBlocksProvider() : array
    {
        return [
            [Block\Box::class],
            [Block\File::class],
            [Block\Image::class],
            [Block\Listing::class],
            [Block\MathML::class],
            [Block\Paragraph::class],
            [Block\Question::class],
            [Block\Quote::class],
            [Block\Section::class],
            [Block\Table::class],
            [Block\Video::class],
            [Block\YouTube::class],
        ];
    }
}
