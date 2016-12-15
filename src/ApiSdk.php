<?php

namespace eLife\ApiSdk;

use eLife\ApiClient\ApiClient\AnnualReportsClient;
use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiClient\ApiClient\CoversClient;
use eLife\ApiClient\ApiClient\EventsClient;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\ApiClient\MediumClient;
use eLife\ApiClient\ApiClient\PeopleClient;
use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiClient\ApiClient\SearchClient;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\HttpClient;
use eLife\ApiSdk\Client\AnnualReports;
use eLife\ApiSdk\Client\Articles;
use eLife\ApiSdk\Client\BlogArticles;
use eLife\ApiSdk\Client\Collections;
use eLife\ApiSdk\Client\Covers;
use eLife\ApiSdk\Client\Events;
use eLife\ApiSdk\Client\Interviews;
use eLife\ApiSdk\Client\LabsExperiments;
use eLife\ApiSdk\Client\MediumArticles;
use eLife\ApiSdk\Client\People;
use eLife\ApiSdk\Client\PodcastEpisodes;
use eLife\ApiSdk\Client\Search;
use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Serializer\AddressNormalizer;
use eLife\ApiSdk\Serializer\AnnualReportNormalizer;
use eLife\ApiSdk\Serializer\AppendixNormalizer;
use eLife\ApiSdk\Serializer\ArticlePoANormalizer;
use eLife\ApiSdk\Serializer\ArticleVoRNormalizer;
use eLife\ApiSdk\Serializer\Block;
use eLife\ApiSdk\Serializer\BlogArticleNormalizer;
use eLife\ApiSdk\Serializer\CollectionNormalizer;
use eLife\ApiSdk\Serializer\CoverNormalizer;
use eLife\ApiSdk\Serializer\DataSetNormalizer;
use eLife\ApiSdk\Serializer\EventNormalizer;
use eLife\ApiSdk\Serializer\FileNormalizer;
use eLife\ApiSdk\Serializer\GroupAuthorNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\InterviewNormalizer;
use eLife\ApiSdk\Serializer\LabsExperimentNormalizer;
use eLife\ApiSdk\Serializer\MediumArticleNormalizer;
use eLife\ApiSdk\Serializer\OnBehalfOfAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonAuthorNormalizer;
use eLife\ApiSdk\Serializer\PersonDetailsNormalizer;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use eLife\ApiSdk\Serializer\PlaceNormalizer;
use eLife\ApiSdk\Serializer\PodcastEpisodeNormalizer;
use eLife\ApiSdk\Serializer\Reference;
use eLife\ApiSdk\Serializer\ReviewerNormalizer;
use eLife\ApiSdk\Serializer\SearchSubjectsNormalizer;
use eLife\ApiSdk\Serializer\SubjectNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

final class ApiSdk
{
    const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';

    private $httpClient;
    private $articlesClient;
    private $blogClient;
    private $coversClient;
    private $eventsClient;
    private $interviewsClient;
    private $labsClient;
    private $peopleClient;
    private $podcastClient;
    private $collectionsClient;
    private $searchClient;
    private $subjectsClient;
    private $serializer;
    private $annualReports;
    private $articles;
    private $blogArticles;
    private $covers;
    private $events;
    private $interviews;
    private $labsExperiments;
    private $mediumArticles;
    private $people;
    private $podcastEpisodes;
    private $collections;
    private $search;
    private $subjects;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->articlesClient = new ArticlesClient($this->httpClient);
        $this->blogClient = new BlogClient($this->httpClient);
        $this->collectionsClient = new CollectionsClient($this->httpClient);
        $this->coversClient = new CoversClient($this->httpClient);
        $this->eventsClient = new EventsClient($this->httpClient);
        $this->interviewsClient = new InterviewsClient($this->httpClient);
        $this->labsClient = new LabsClient($this->httpClient);
        $this->peopleClient = new PeopleClient($this->httpClient);
        $this->podcastClient = new PodcastClient($this->httpClient);
        $this->searchClient = new SearchClient($this->httpClient);
        $this->subjectsClient = new SubjectsClient($this->httpClient);

        $this->serializer = new Serializer([
            new AddressNormalizer(),
            new AnnualReportNormalizer(),
            new AppendixNormalizer(),
            new ArticlePoANormalizer($this->articlesClient),
            new ArticleVoRNormalizer($this->articlesClient),
            new BlogArticleNormalizer($this->blogClient),
            new CollectionNormalizer($this->collectionsClient),
            new CoverNormalizer(),
            new DataSetNormalizer(),
            new EventNormalizer($this->eventsClient),
            new FileNormalizer(),
            new GroupAuthorNormalizer(),
            new ImageNormalizer(),
            new InterviewNormalizer($this->interviewsClient),
            new LabsExperimentNormalizer($this->labsClient),
            new MediumArticleNormalizer(),
            new OnBehalfOfAuthorNormalizer(),
            new PersonAuthorNormalizer(),
            new PersonDetailsNormalizer(),
            new PersonNormalizer($this->peopleClient),
            new PlaceNormalizer(),
            new PodcastEpisodeNormalizer($this->podcastClient),
            new ReviewerNormalizer(),
            new SearchSubjectsNormalizer(),
            new SubjectNormalizer($this->subjectsClient),
            new Block\BoxNormalizer(),
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

    public function interviews() : Interviews
    {
        if (empty($this->interviews)) {
            $this->interviews = new Interviews($this->interviewsClient, $this->serializer);
        }

        return $this->interviews;
    }

    public function labsExperiments() : LabsExperiments
    {
        if (empty($this->labsExperiments)) {
            $this->labsExperiments = new LabsExperiments($this->labsClient, $this->serializer);
        }

        return $this->labsExperiments;
    }

    public function mediumArticles() : MediumArticles
    {
        if (empty($this->mediumArticles)) {
            $this->mediumArticles = new MediumArticles(new MediumClient($this->httpClient), $this->serializer);
        }

        return $this->mediumArticles;
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

    public function collections() : Collections
    {
        if (empty($this->collections)) {
            $this->collections = new Collections($this->collectionsClient, $this->serializer);
        }

        return $this->collections;
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
