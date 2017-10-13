<?php

namespace eLife\ApiSdk;

use eLife\ApiClient\ApiClient\AnnualReportsClient;
use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiClient\ApiClient\CommunityClient;
use eLife\ApiClient\ApiClient\CoversClient;
use eLife\ApiClient\ApiClient\EventsClient;
use eLife\ApiClient\ApiClient\HighlightsClient;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\ApiClient\MediumClient;
use eLife\ApiClient\ApiClient\MetricsClient;
use eLife\ApiClient\ApiClient\PeopleClient;
use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiClient\ApiClient\PressPackagesClient;
use eLife\ApiClient\ApiClient\ProfilesClient;
use eLife\ApiClient\ApiClient\RecommendationsClient;
use eLife\ApiClient\ApiClient\SearchClient;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\HttpClient;
use eLife\ApiClient\HttpClient\UserAgentPrependingHttpClient;
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
use eLife\ApiSdk\Client\Profiles;
use eLife\ApiSdk\Client\Recommendations;
use eLife\ApiSdk\Client\Search;
use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Serializer\AddressNormalizer;
use eLife\ApiSdk\Serializer\AnnualReportNormalizer;
use eLife\ApiSdk\Serializer\AppendixNormalizer;
use eLife\ApiSdk\Serializer\ArticleHistoryNormalizer;
use eLife\ApiSdk\Serializer\ArticlePoANormalizer;
use eLife\ApiSdk\Serializer\ArticleVoRNormalizer;
use eLife\ApiSdk\Serializer\AssetFileNormalizer;
use eLife\ApiSdk\Serializer\Block;
use eLife\ApiSdk\Serializer\BlogArticleNormalizer;
use eLife\ApiSdk\Serializer\CollectionNormalizer;
use eLife\ApiSdk\Serializer\CoverNormalizer;
use eLife\ApiSdk\Serializer\DataSetNormalizer;
use eLife\ApiSdk\Serializer\EventNormalizer;
use eLife\ApiSdk\Serializer\ExternalArticleNormalizer;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\GroupAuthorNormalizer;
use eLife\ApiSdk\Serializer\HighlightNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\InterviewNormalizer;
use eLife\ApiSdk\Serializer\LabsPostNormalizer;
use eLife\ApiSdk\Serializer\MediaContactNormalizer;
use eLife\ApiSdk\Serializer\MediumArticleNormalizer;
use eLife\ApiSdk\Serializer\NormalizerAwareSerializer;
use eLife\ApiSdk\Serializer\OnBehalfOfAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\PodcastEpisodeChapterModelNormalizer;
use eLife\ApiSdk\Serializer\PodcastEpisodeNormalizer;
use eLife\ApiSdk\Serializer\PressPackageNormalizer;
use eLife\ApiSdk\Serializer\ProfileNormalizer;
use eLife\ApiSdk\Serializer\Reference;
use eLife\ApiSdk\Serializer\ReviewerNormalizer;
use eLife\ApiSdk\Serializer\SearchSubjectsNormalizer;
use eLife\ApiSdk\Serializer\SubjectNormalizer;
use PackageVersions\Versions;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

final class ApiSdk
{
    const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    private $version;
    private $httpClient;
    private $articlesClient;
    private $blogClient;
    private $collectionsClient;
    private $communityClient;
    private $coversClient;
    private $eventsClient;
    private $highlightsClient;
    private $interviewsClient;
    private $labsClient;
    private $metricsClient;
    private $peopleClient;
    private $podcastClient;
    private $pressPackagesClient;
    private $profilesClient;
    private $recommendationsClient;
    private $searchClient;
    private $subjectsClient;
    private $serializer;
    private $annualReports;
    private $articles;
    private $blogArticles;
    private $community;
    private $covers;
    private $events;
    private $highlights;
    private $interviews;
    private $labsPosts;
    private $mediumArticles;
    private $metrics;
    private $people;
    private $podcastEpisodes;
    private $pressPackages;
    private $profiles;
    private $collections;
    private $recommendations;
    private $search;
    private $subjects;

    public function __construct(HttpClient $httpClient)
    {
        $originalVersion = Versions::getVersion('elife/api-sdk');
        list($version, $reference) = explode('@', $originalVersion);
        if (false !== strpos($version, 'dev')) {
            if (40 === strlen($reference)) {
                $version = implode('@', [$version, substr($reference, 0, 7)]);
            } else {
                $version = $originalVersion;
            }
        }

        $this->version = $version;
        $this->httpClient = new UserAgentPrependingHttpClient($httpClient, 'eLifeApiSdk/'.$this->version);
        $this->articlesClient = new ArticlesClient($this->httpClient);
        $this->blogClient = new BlogClient($this->httpClient);
        $this->collectionsClient = new CollectionsClient($this->httpClient);
        $this->communityClient = new CommunityClient($this->httpClient);
        $this->coversClient = new CoversClient($this->httpClient);
        $this->eventsClient = new EventsClient($this->httpClient);
        $this->highlightsClient = new HighlightsClient($this->httpClient);
        $this->interviewsClient = new InterviewsClient($this->httpClient);
        $this->labsClient = new LabsClient($this->httpClient);
        $this->metricsClient = new MetricsClient($this->httpClient);
        $this->peopleClient = new PeopleClient($this->httpClient);
        $this->podcastClient = new PodcastClient($this->httpClient);
        $this->pressPackagesClient = new PressPackagesClient($this->httpClient);
        $this->profilesClient = new ProfilesClient($this->httpClient);
        $this->recommendationsClient = new RecommendationsClient($this->httpClient);
        $this->searchClient = new SearchClient($this->httpClient);
        $this->subjectsClient = new SubjectsClient($this->httpClient);

        $this->serializer = new NormalizerAwareSerializer([
            new AddressNormalizer(),
            new AnnualReportNormalizer(),
            new AppendixNormalizer(),
            new ArticleHistoryNormalizer(),
            new ArticlePoANormalizer($this->articlesClient),
            new ArticleVoRNormalizer($this->articlesClient),
            new AssetFileNormalizer(),
            new BlogArticleNormalizer($this->blogClient),
            new CollectionNormalizer($this->collectionsClient),
            new CoverNormalizer(),
            new DataSetNormalizer(),
            new EventNormalizer($this->eventsClient),
            new ExternalArticleNormalizer(),
            new FileNormalizer(),
            new GroupAuthorNormalizer(),
            new HighlightNormalizer(),
            new ImageNormalizer(),
            new InterviewNormalizer($this->interviewsClient),
            new LabsPostNormalizer($this->labsClient),
            new MediaContactNormalizer(),
            new MediumArticleNormalizer(),
            new OnBehalfOfAuthorNormalizer(),
            new PersonAuthorNormalizer(),
            new PersonDetailsNormalizer(),
            new PersonNormalizer($this->peopleClient),
            new PlaceNormalizer(),
            new PodcastEpisodeChapterModelNormalizer(),
            new PodcastEpisodeNormalizer($this->podcastClient),
            new PressPackageNormalizer($this->pressPackagesClient),
            new ProfileNormalizer($this->profilesClient),
            new ReviewerNormalizer(),
            new SearchSubjectsNormalizer(),
            new SubjectNormalizer($this->subjectsClient),
            new Block\BoxNormalizer(),
            new Block\ButtonNormalizer(),
            new Block\CodeNormalizer(),
            new Block\ExcerptNormalizer(),
            new Block\FigureNormalizer(),
            new Block\ImageNormalizer(),
            new Block\ListingNormalizer(),
            new Block\MathMLNormalizer(),
            new Block\ParagraphNormalizer(),
            new Block\QuestionNormalizer(),
            new Block\QuoteNormalizer(),
            new Block\SectionNormalizer(),
            new Block\TableNormalizer(),
            new Block\VideoNormalizer(),
            new Block\YouTubeNormalizer(),
            new Reference\BookReferenceNormalizer(),
            new Reference\BookChapterReferenceNormalizer(),
            new Reference\ClinicalTrialReferenceNormalizer(),
            new Reference\ConferenceProceedingReferenceNormalizer(),
            new Reference\DataReferenceNormalizer(),
            new Reference\JournalReferenceNormalizer(),
            new Reference\PatentReferenceNormalizer(),
            new Reference\PeriodicalReferenceNormalizer(),
            new Reference\PreprintReferenceNormalizer(),
            new Reference\ReferencePagesNormalizer(),
            new Reference\ReportReferenceNormalizer(),
            new Reference\SoftwareReferenceNormalizer(),
            new Reference\ThesisReferenceNormalizer(),
            new Reference\UnknownReferenceNormalizer(),
            new Reference\WebReferenceNormalizer(),
        ], [new JsonEncoder()]);
    }

    public function getVersion() : string
    {
        return $this->version;
    }

    public function annualReports() : AnnualReports
    {
        if (empty($this->annualReports)) {
            $this->annualReports = new AnnualReports(new AnnualReportsClient($this->httpClient), $this->serializer);
        }

        return $this->annualReports;
    }

    public function articles() : Articles
    {
        if (empty($this->articles)) {
            $this->articles = new Articles($this->articlesClient, $this->serializer);
        }

        return $this->articles;
    }

    public function blogArticles() : BlogArticles
    {
        if (empty($this->blogArticles)) {
            $this->blogArticles = new BlogArticles($this->blogClient, $this->serializer);
        }

        return $this->blogArticles;
    }

    public function community() : Community
    {
        if (empty($this->community)) {
            $this->community = new Community($this->communityClient, $this->serializer);
        }

        return $this->community;
    }

    public function covers() : Covers
    {
        if (empty($this->covers)) {
            $this->covers = new Covers($this->coversClient, $this->serializer);
        }

        return $this->covers;
    }

    public function events() : Events
    {
        if (empty($this->events)) {
            $this->events = new Events($this->eventsClient, $this->serializer);
        }

        return $this->events;
    }

    public function highlights() : Highlights
    {
        if (empty($this->highlights)) {
            $this->highlights = new Highlights($this->highlightsClient, $this->serializer);
        }

        return $this->highlights;
    }

    public function interviews() : Interviews
    {
        if (empty($this->interviews)) {
            $this->interviews = new Interviews($this->interviewsClient, $this->serializer);
        }

        return $this->interviews;
    }

    public function labsPosts() : LabsPosts
    {
        if (empty($this->labsPosts)) {
            $this->labsPosts = new LabsPosts($this->labsClient, $this->serializer);
        }

        return $this->labsPosts;
    }

    public function mediumArticles() : MediumArticles
    {
        if (empty($this->mediumArticles)) {
            $this->mediumArticles = new MediumArticles(new MediumClient($this->httpClient), $this->serializer);
        }

        return $this->mediumArticles;
    }

    public function metrics() : Metrics
    {
        if (empty($this->metrics)) {
            $this->metrics = new Metrics($this->metricsClient);
        }

        return $this->metrics;
    }

    public function people() : People
    {
        if (empty($this->people)) {
            $this->people = new People($this->peopleClient, $this->serializer);
        }

        return $this->people;
    }

    public function podcastEpisodes() : PodcastEpisodes
    {
        if (empty($this->podcastEpisodes)) {
            $this->podcastEpisodes = new PodcastEpisodes($this->podcastClient, $this->serializer);
        }

        return $this->podcastEpisodes;
    }

    public function pressPackages() : PressPackages
    {
        if (empty($this->pressPackages)) {
            $this->pressPackages = new PressPackages($this->pressPackagesClient, $this->serializer);
        }

        return $this->pressPackages;
    }

    public function profiles() : Profiles
    {
        if (empty($this->profiles)) {
            $this->profiles = new Profiles($this->profilesClient, $this->serializer);
        }

        return $this->profiles;
    }

    public function collections() : Collections
    {
        if (empty($this->collections)) {
            $this->collections = new Collections($this->collectionsClient, $this->serializer);
        }

        return $this->collections;
    }

    public function recommendations() : Recommendations
    {
        if (empty($this->recommendations)) {
            $this->recommendations = new Recommendations($this->recommendationsClient, $this->serializer);
        }

        return $this->recommendations;
    }

    public function subjects() : Subjects
    {
        if (empty($this->subjects)) {
            $this->subjects = new Subjects($this->subjectsClient, $this->serializer);
        }

        return $this->subjects;
    }

    public function search() : Search
    {
        if (empty($this->search)) {
            $this->search = new Search($this->searchClient, $this->serializer);
        }

        return $this->search;
    }

    public function getSerializer() : Serializer
    {
        return $this->serializer;
    }
}
