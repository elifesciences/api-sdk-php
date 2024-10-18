<?php

namespace test\eLife\ApiSdk;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\Annotations;
use eLife\ApiSdk\Client\AnnualReports;
use eLife\ApiSdk\Client\Articles;
use eLife\ApiSdk\Client\Bioprotocols;
use eLife\ApiSdk\Client\BlogArticles;
use eLife\ApiSdk\Client\Collections;
use eLife\ApiSdk\Client\Community;
use eLife\ApiSdk\Client\Covers;
use eLife\ApiSdk\Client\Digests;
use eLife\ApiSdk\Client\Events;
use eLife\ApiSdk\Client\Highlights;
use eLife\ApiSdk\Client\Interviews;
use eLife\ApiSdk\Client\JobAdverts;
use eLife\ApiSdk\Client\LabsPosts;
use eLife\ApiSdk\Client\Metrics;
use eLife\ApiSdk\Client\People;
use eLife\ApiSdk\Client\PodcastEpisodes;
use eLife\ApiSdk\Client\PressPackages;
use eLife\ApiSdk\Client\Profiles;
use eLife\ApiSdk\Client\PromotionalCollections;
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
    public function it_creates_annotations()
    {
        $this->assertInstanceOf(Annotations::class, $this->apiSdk->annotations());

        $this->mockAnnotationListCall('foo', 1, 1, 1);
        $this->mockAnnotationListCall('foo', 1, 100, 1);

        $this->apiSdk->annotations()->list('foo')->toArray();
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
    public function it_creates_bioprotocols()
    {
        $this->assertInstanceOf(Bioprotocols::class, $this->apiSdk->bioprotocols());

        $this->mockBioprotocolsCall('article', '09560');

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->bioprotocols()->list(Identifier::article('09560'))->wait());
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

        $this->assertIsArray(
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
    public function it_creates_digests()
    {
        $this->assertInstanceOf(Digests::class, $this->apiSdk->digests());

        $this->mockDigestCall('1');

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->digests()->get('1')->wait());
    }

    /**
     * @test
     */
    public function it_creates_events()
    {
        $this->assertInstanceOf(Events::class, $this->apiSdk->events());

        $this->mockEventCall('event7', true);

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
    public function it_creates_job_adverts()
    {
        $this->assertInstanceOf(JobAdverts::class, $this->apiSdk->jobAdverts());

        $this->mockJobAdvertCall('job-advert7', true);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->jobAdverts()->get('job-advert7')->wait());
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
    public function it_creates_profiles()
    {
        $this->assertInstanceOf(Profiles::class, $this->apiSdk->profiles());

        $this->mockProfileCall(1);

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->profiles()->get('profile1')->wait());
    }

    /**
     * @test
     */
    public function it_creates_promotional_collections()
    {
        $this->assertInstanceOf(PromotionalCollections::class, $this->apiSdk->promotionalCollections());

        $this->mockPromotionalCollectionCall('1');

        $this->apiSdk->getSerializer()->normalize($this->apiSdk->promotionalCollections()->get('1')->wait());
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
            Block\Figshare::class,
            Block\Figure::class,
            Block\GoogleMap::class,
            Block\Image::class,
            Block\Listing::class,
            Block\MathML::class,
            Block\Paragraph::class,
            Block\Profile::class,
            Block\Question::class,
            Block\Quote::class,
            Block\Section::class,
            Block\Table::class,
            Block\Tweet::class,
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
