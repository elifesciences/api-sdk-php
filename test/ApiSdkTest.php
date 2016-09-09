<?php

namespace test\eLife\ApiSdk;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\BlogArticles;
use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Model\Block;

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
    public function it_creates_subjects()
    {
        $this->assertInstanceOf(Subjects::class, $this->apiSdk->subjects());

        $this->mockSubjectCall(1);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->subjects()->get('subject1')->wait());
    }

    /**
     * @test
     * @dataProvider denormalizeBlocksProvider
     */
    public function it_can_denormalize_blocks(string $block)
    {
        $this->apiSdk->getSerializer()->supportsDenormalization([], $block);
    }

    public function denormalizeBlocksProvider() : array
    {
        return [
            [Block\Image::class],
            [Block\Paragraph::class],
            [Block\YouTube::class],
        ];
    }
}
