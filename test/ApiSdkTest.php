<?php

namespace test\eLife\ApiSdk;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\AnnualReports;
use eLife\ApiSdk\Client\Articles;
use eLife\ApiSdk\Client\BlogArticles;
use eLife\ApiSdk\Client\Collections;
use eLife\ApiSdk\Client\Community;
use eLife\ApiSdk\Client\Covers;
use eLife\ApiSdk\Client\Events;
use eLife\ApiSdk\Client\Highlights;
use eLife\ApiSdk\Client\Interviews;
use eLife\ApiSdk\Client\LabsPosts;
use eLife\ApiSdk\Client\MediumArticles;
use eLife\ApiSdk\Client\Metrics;
use eLife\ApiSdk\Client\People;
use eLife\ApiSdk\Client\PodcastEpisodes;
use eLife\ApiSdk\Client\PressPackages;
use eLife\ApiSdk\Client\Recommendations;
use eLife\ApiSdk\Client\Search;
use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Identifier;
use eLife\ApiSdk\Model\Reference;
use Traversable;

final class ApiSdkTest extends ApiTestCase
{
    /**
     * @var ApiSdk
     */
    private $apiSdk;

    /**
     * @before
     */
    protected function setUpApiSdk()
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
    public function it_creates_articles()
    {
        $this->assertInstanceOf(Articles::class, $this->apiSdk->articles());

        $this->mockArticleCall(7, true, true);
        $this->mockSubjectCall(1);
        $this->mockArticleHistoryCall(7, true);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->articles()->get('article7')->wait());
        $this->apiSdk->getSerializer()->normalize($this->apiSdk->articles()->getHistory('article7')->wait());
    }

    /**
     * @test
     */
    public function it_creates_blog_articles()
    {
        $this->assertInstanceOf(BlogArticles::class, $this->apiSdk->blogArticles());

        $this->mockBlogArticleCall(7, true);
        $this->mockSubjectCall(1);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->blogArticles()->get('blog-article-7')->wait());
    }

    /**
     * @test
     */
    public function it_creates_collections()
    {
        $this->assertInstanceOf(Collections::class, $this->apiSdk->collections());

        $this->mockCollectionCall('1');

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->collections()->get('1')->wait());
    }

    /**
     * @test
     */
    public function it_creates_community()
    {
        $this->assertInstanceOf(Community::class, $this->apiSdk->community());

        $this->mockCommunityListCall(1, 1, 1);
        $this->mockBlogArticleCall('model-1', true);

        $this->assertInternalType(
            'array',
            $this->apiSdk->getSerializer()->normalize($this->apiSdk->community()[0])
        );
    }

    /**
     * @test
     */
    public function it_creates_covers()
    {
        $this->assertInstanceOf(Covers::class, $this->apiSdk->covers());

        $this->mockCoverListCall(1, 1, 10);
        $this->mockCoverListCall(1, 100, 10);

        foreach ($this->apiSdk->covers() as $cover) {
            $this->apiSdk->getSerializer()->normalize($cover);
        }
    }

    /**
     * @test
     */
    public function it_creates_events()
    {
        $this->assertInstanceOf(Events::class, $this->apiSdk->events());

        $this->mockEventCall(7, true);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->events()->get('event7')->wait());
    }

    /**
     * @test
     */
    public function it_creates_highlights()
    {
        $this->assertInstanceOf(Highlights::class, $this->apiSdk->highlights());

        $this->mockHighlightsCall('foo', 1, 1, 1);
        $this->mockHighlightsCall('foo', 1, 100, 1);

        $this->apiSdk->highlights()->get('foo')->toArray();
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
    public function it_creates_labs_posts()
    {
        $this->assertInstanceOf(LabsPosts::class, $this->apiSdk->labsPosts());

        $this->mockLabsPostCall(1);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->labsPosts()->get(1)->wait());
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
    public function it_creates_metrics()
    {
        $this->assertInstanceOf(Metrics::class, $this->apiSdk->metrics());

        $this->mockMetricPageViewsCall('article', '09560');

        $this->apiSdk->metrics()->totalPageViews(Identifier::article('09560'))->wait();
    }

    /**
     * @test
     */
    public function it_creates_people()
    {
        $this->assertInstanceOf(People::class, $this->apiSdk->people());

        $this->mockPersonCall(1);
        $this->mockSubjectCall(1);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->people()->get('person1')->wait());
    }

    /**
     * @test
     */
    public function it_creates_podcast_episodes()
    {
        $this->assertInstanceOf(PodcastEpisodes::class, $this->apiSdk->podcastEpisodes());

        $this->mockPodcastEpisodeCall(1);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->podcastEpisodes()->get(1)->wait());
    }

    /**
     * @test
     */
    public function it_creates_press_packages()
    {
        $this->assertInstanceOf(PressPackages::class, $this->apiSdk->pressPackages());

        $this->mockPressPackageCall(7);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->pressPackages()->get('press-package-7')->wait());
    }

    /**
     * @test
     */
    public function it_creates_recommendations()
    {
        $this->assertInstanceOf(Recommendations::class, $this->apiSdk->recommendations());

        $this->mockRecommendationsCall('article', '12345', 1, 1, 10);
        $this->mockRecommendationsCall('article', '12345', 1, 100, 10);

        $this->assertCount(10, $this->apiSdk->recommendations()->list(Identifier::article('12345'))->toArray());
    }

    /**
     * @test
     */
    public function it_creates_searches()
    {
        $this->assertInstanceOf(Search::class, $this->apiSdk->search());

        $this->mockSearchCall(1, 1, 10);
        $this->mockSearchCall(1, 100, 10);

        $this->assertCount(10, $this->apiSdk->search()->toArray());
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
        $this->assertTrue($this->apiSdk->getSerializer()->supportsEncoding('json'));
    }

    /**
     * @test
     */
    public function it_support_decoding()
    {
        $this->assertTrue($this->apiSdk->getSerializer()->supportsDecoding('json'));
    }

    /**
     * @test
     * @dataProvider denormalizeBlocksProvider
     */
    public function it_can_denormalize_blocks(string $block)
    {
        $this->assertTrue($this->apiSdk->getSerializer()->supportsDenormalization([], $block));
    }

    public function denormalizeBlocksProvider() : Traversable
    {
        return $this->classNameProvider(
            Block\Box::class,
            Block\Button::class,
            Block\Code::class,
            Block\Excerpt::class,
            Block\Figure::class,
            Block\Image::class,
            Block\Listing::class,
            Block\MathML::class,
            Block\Paragraph::class,
            Block\Question::class,
            Block\Quote::class,
            Block\Section::class,
            Block\Table::class,
            Block\Video::class,
            Block\YouTube::class
        );
    }

    /**
     * @test
     * @dataProvider denormalizeReferencesProvider
     */
    public function it_can_denormalize_references(string $reference)
    {
        $this->assertTrue($this->apiSdk->getSerializer()->supportsDenormalization([], $reference));
    }

    public function denormalizeReferencesProvider() : Traversable
    {
        return $this->classNameProvider(
            Reference\BookChapterReference::class,
            Reference\BookReference::class,
            Reference\ClinicalTrialReference::class,
            Reference\ConferenceProceedingReference::class,
            Reference\DataReference::class,
            Reference\JournalReference::class,
            Reference\PatentReference::class,
            Reference\PeriodicalReference::class,
            Reference\PreprintReference::class,
            Reference\ReportReference::class,
            Reference\SoftwareReference::class,
            Reference\ThesisReference::class,
            Reference\UnknownReference::class,
            Reference\WebReference::class
        );
    }
}
