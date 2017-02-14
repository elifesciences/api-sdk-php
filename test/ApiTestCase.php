<?php

namespace test\eLife\ApiSdk;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\MockMiddleware;
use DateTimeImmutable;
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
use eLife\ApiClient\ApiClient\SearchClient;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\HttpClient;
use eLife\ApiClient\HttpClient\Guzzle6HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiValidator\MessageValidator\JsonMessageValidator;
use eLife\ApiValidator\SchemaFinder\PuliSchemaFinder;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use LogicException;
use Webmozart\Json\JsonDecoder;

abstract class ApiTestCase extends TestCase
{
    use PuliAwareTestCase;

    /** @var InMemoryStorageAdapter */
    private $storage;

    /** @var HttpClient */
    private $httpClient;

    /**
     * @after
     */
    final public function resetMocks()
    {
        $this->httpClient = null;
    }

    final protected function getHttpClient() : HttpClient
    {
        if (null === $this->httpClient) {
            $storage = new InMemoryStorageAdapter();
            $validator = new JsonMessageValidator(
                new PuliSchemaFinder(self::$puli),
                new JsonDecoder()
            );

            $this->storage = new ValidatingStorageAdapter($storage, $validator);

            $stack = HandlerStack::create();
            $stack->push(new MockMiddleware($this->storage, 'replay'));

            $this->httpClient = new Guzzle6HttpClient(new Client([
                'base_uri' => 'http://api.elifesciences.org',
                'handler' => $stack,
            ]));
        }

        return $this->httpClient;
    }

    final protected function mockNotFound(string $uri, array $headers)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/'.$uri,
                $headers
            ),
            new Response(
                404,
                ['Content-Type' => 'application/problem+json'],
                json_encode([
                    'title' => 'Not found',
                ])
            )
        );
    }

    final protected function mockAnnualReportListCall(int $page, int $perPage, int $total, $descendingOrder = true)
    {
        $annualReports = array_map(function (int $year) {
            return $this->createAnnualReportJson($year + 2011);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/annual-reports?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $annualReports,
                ])
            )
        );
    }

    final protected function mockAnnualReportCall(int $year)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/annual-reports/'.$year,
                ['Accept' => new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT, 1)],
                json_encode($this->createAnnualReportJson($year))
            )
        );
    }

    final protected function mockArticleListCall(
        int $page,
        int $perPage,
        int $total,
        bool $descendingOrder = true,
        array $subjects = [],
        bool $vor = false
    ) {
        $articles = array_map(function (int $id) use ($vor) {
            if ($vor) {
                return $this->createArticleVoRJson('article'.$id, true);
            }

            return $this->createArticlePoAJson('article'.$id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $subjectsQuery = implode('', array_map(function (string $subjectId) {
            return '&subject[]='.$subjectId;
        }, $subjects));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').$subjectsQuery,
                ['Accept' => new MediaType(ArticlesClient::TYPE_ARTICLE_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(ArticlesClient::TYPE_ARTICLE_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $articles,
                ])
            )
        );
    }

    final protected function mockArticleHistoryCall($numberOrId, bool $complete = false)
    {
        if (is_integer($numberOrId)) {
            $id = "article{$numberOrId}";
        } else {
            $id = (string) $numberOrId;
        }

        $response = new Response(
            200,
            ['Content-Type' => new MediaType(ArticlesClient::TYPE_ARTICLE_HISTORY, 1)],
            json_encode($this->createArticleHistoryJson($id, $complete))
        );

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/'.$id.'/versions',
                [
                    'Accept' => [
                        new MediaType(ArticlesClient::TYPE_ARTICLE_HISTORY, 1),
                    ],
                ]
            ),
            $response
        );
    }

    final protected function mockRelatedArticlesCall($numberOrId, bool $complete = false)
    {
        if (is_integer($numberOrId)) {
            $id = "article{$numberOrId}";
        } else {
            $id = (string) $numberOrId;
        }

        $response = new Response(
            200,
            ['Content-Type' => new MediaType(ArticlesClient::TYPE_ARTICLE_RELATED, 1)],
            json_encode($this->createRelatedArticlesJson($id, $complete))
        );

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/'.$id.'/related',
                [
                    'Accept' => [
                        new MediaType(ArticlesClient::TYPE_ARTICLE_RELATED, 1),
                    ],
                ]
            ),
            $response
        );
    }

    final protected function mockArticleCall($numberOrId, bool $complete = false, bool $vor = false, int $version = null)
    {
        if (is_integer($numberOrId)) {
            $id = "article{$numberOrId}";
        } else {
            $id = (string) $numberOrId;
        }
        if ($vor) {
            $response = new Response(
                200,
                ['Content-Type' => new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, 1)],
                json_encode($this->createArticleVoRJson($id, false, $complete, $version ?? 1))
            );
        } else {
            $response = new Response(
                200,
                ['Content-Type' => new MediaType(ArticlesClient::TYPE_ARTICLE_POA, 1)],
                json_encode($this->createArticlePoAJson($id, false, $complete, $version ?? 1))
            );
        }

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/'.$id.($version ? '/versions/'.$version : ''),
                [
                    'Accept' => [
                        new MediaType(ArticlesClient::TYPE_ARTICLE_POA, 1),
                        new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, 1),
                    ],
                ]
            ),
            $response
        );
    }

    final protected function mockArticleListCallWithAnInvalidArticle(
        int $page = 1,
        int $perPage = 100,
        int $total = 200
    ) {
        $articles = array_map(function (int $id) {
            $body = $this->createArticlePoAJson('article'.$id, true);
            if ($id == 42) {
                $body = [
                    '-invalid' => true,
                    'published' => '2016-12-13T00:00:00Z',
                    'status' => 'vor',
                    'version' => 1,
                    'versionDate' => '2016-12-13T00:00:00Z',
                ];
            }

            return $body;
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles?page='.$page.'&per-page='.$perPage.'&order=desc',
                ['Accept' => new MediaType(ArticlesClient::TYPE_ARTICLE_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(ArticlesClient::TYPE_ARTICLE_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $articles,
                ])
            )
        );
    }

    final protected function mockBlogArticleListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true,
        array $subjects = []
    ) {
        $blogArticles = array_map(function (int $id) {
            return $this->createBlogArticleJson('blog-article-'.$id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $subjectsQuery = implode('', array_map(function (string $subjectId) {
            return '&subject[]='.$subjectId;
        }, $subjects));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/blog-articles?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').$subjectsQuery,
                ['Accept' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $blogArticles,
                ])
            )
        );
    }

    final protected function mockBlogArticleCall($numberOrId, bool $complete = false)
    {
        if (is_integer($numberOrId)) {
            $id = "blog-article-{$numberOrId}";
        } else {
            $id = (string) $numberOrId;
        }
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/blog-articles/'.$id,
                ['Accept' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE, 1)],
                json_encode($this->createBlogArticleJson($id, false, $complete))
            )
        );
    }

    final protected function mockCommunityListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true,
        array $subjects = []
    ) {
        $availableModels = [
            'blog-article' => 'createBlogArticleJson',
            'collection' => 'createCollectionJson',
            'event' => 'createEventJson',
            'interview' => 'createInterviewJson',
            'research-article' => 'createArticleVoRJson',
            'replication-study' => 'createArticlePoAJson',
            // for simplicity, avoiding contents without an id
            //'labs-experiment' => ['createLabsExperimentJson', 'int'],
            //'podcast-episode' => ['createPodcastEpisodeJson', 'int'],
        ];
        $blogArticles = array_map(function (int $id) use ($availableModels) {
            $modelNames = array_keys($availableModels);
            $zeroBasedId = $id - 1;
            $modelName = $modelNames[$zeroBasedId % count($availableModels)];
            $model = $availableModels[$modelName];

            return array_merge(
                ['type' => $modelName],
                $this->{$model}('model-'.$id, true)
            );
        }, $this->generateIdList($page, $perPage, $total));

        $subjectsQuery = implode('', array_map(function (string $subjectId) {
            return '&subject[]='.$subjectId;
        }, $subjects));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/community?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').$subjectsQuery,
                ['Accept' => new MediaType(CommunityClient::TYPE_COMMUNITY_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(CommunityClient::TYPE_COMMUNITY_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $blogArticles,
                ])
            )
        );
    }

    final protected function mockCoverListCall(
        int $page,
        int $perPage,
        int $total,
        bool $descendingOrder = true,
        string $sort = 'date',
        DateTimeImmutable $startDate = null,
        DateTimeImmutable $endDate = null
    ) {
        $covers = array_map(function (int $id) {
            return $this->createCoverJson($id, $this->createArticleVoRJson($id));
        }, $this->generateIdList($page, $perPage, $total));

        $startsQuery = $startDate ? '&start-date='.$startDate->format('Y-m-d') : '';
        $endsQuery = $endDate ? '&end-date='.$endDate->format('Y-m-d') : '';

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/covers?page='.$page.'&per-page='.$perPage.'&sort='.$sort.'&order='.($descendingOrder ? 'desc' : 'asc').$startsQuery.$endsQuery,
                ['Accept' => new MediaType(CoversClient::TYPE_COVERS_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(CoversClient::TYPE_COVERS_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $covers,
                ])
            )
        );
    }

    final protected function mockCurrentCoverListCall(int $total)
    {
        $covers = array_map(function (int $id) {
            return $this->createCoverJson($id, $this->createArticleVoRJson($id));
        }, range($total, 1));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/covers/current',
                ['Accept' => new MediaType(CoversClient::TYPE_COVERS_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(CoversClient::TYPE_COVERS_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $covers,
                ])
            )
        );
    }

    final protected function mockEventListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true,
        string $type = 'all'
    ) {
        $events = array_map(function (int $id) {
            return $this->createEventJson($id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/events?page='.$page.'&per-page='.$perPage.'&type='.$type.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => new MediaType(EventsClient::TYPE_EVENT_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(EventsClient::TYPE_EVENT_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $events,
                ])
            )
        );
    }

    final protected function mockEventCall(int $number, bool $complete = false)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/events/event'.$number,
                ['Accept' => new MediaType(EventsClient::TYPE_EVENT, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(EventsClient::TYPE_EVENT, 1)],
                json_encode($this->createEventJson($number, false, $complete))
            )
        );
    }

    final protected function mockHighlightsCall(string $id, int $total, bool $complete = false)
    {
        $highlights = array_map(function (int $id) use ($complete) {
            return $this->createHighlightJson($id, ['type' => 'interview'] + $this->createInterviewJson($id, true, $complete), $complete);
        }, $this->generateIdList(1, $total, $total));

        $this->storage->save(
            new Request(
                'GET',
                "http://api.elifesciences.org/highlights/$id",
                ['Accept' => new MediaType(HighlightsClient::TYPE_HIGHLIGHTS, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(HighlightsClient::TYPE_HIGHLIGHTS, 1)],
                json_encode($highlights)
            )
        );
    }

    final protected function mockInterviewListCall(int $page, int $perPage, int $total, $descendingOrder = true)
    {
        $interviews = array_map(function (int $id) {
            return $this->createInterviewJson('interview'.$id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/interviews?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => new MediaType(InterviewsClient::TYPE_INTERVIEW_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(InterviewsClient::TYPE_INTERVIEW_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $interviews,
                ])
            )
        );
    }

    /**
     * @param string|int $numberOrId
     */
    final protected function mockInterviewCall($numberOrId, bool $complete = false)
    {
        if (is_integer($numberOrId)) {
            $id = "interview{$numberOrId}";
        } else {
            $id = (string) $numberOrId;
        }
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/interviews/'.$id,
                ['Accept' => new MediaType(InterviewsClient::TYPE_INTERVIEW, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(InterviewsClient::TYPE_INTERVIEW, 1)],
                json_encode($this->createInterviewJson($id, false, $complete))
            )
        );
    }

    final protected function mockLabsExperimentListCall(int $page, int $perPage, int $total, $descendingOrder = true)
    {
        $labsExperiments = array_map(function (int $id) {
            return $this->createLabsExperimentJson($id);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-experiments?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => new MediaType(LabsClient::TYPE_EXPERIMENT_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(LabsClient::TYPE_EXPERIMENT_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $labsExperiments,
                ])
            )
        );
    }

    final protected function mockLabsExperimentCall(int $number, bool $complete = false)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-experiments/'.$number,
                ['Accept' => new MediaType(LabsClient::TYPE_EXPERIMENT, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(LabsClient::TYPE_EXPERIMENT, 1)],
                json_encode($this->createLabsExperimentJson($number, false, $complete))
            )
        );
    }

    final protected function mockMediumArticleListCall(int $page, int $perPage, int $total, $descendingOrder = true)
    {
        $articles = array_map(function (int $id) {
            return $this->createMediumArticleJson($id);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/medium-articles?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => new MediaType(MediumClient::TYPE_MEDIUM_ARTICLE_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(MediumClient::TYPE_MEDIUM_ARTICLE_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $articles,
                ])
            )
        );
    }

    final protected function mockMetricCitationsCall(string $type, string $id)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/metrics/'.$type.'/'.$id.'/citations',
                ['Accept' => new MediaType(MetricsClient::TYPE_METRIC_CITATIONS, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(MetricsClient::TYPE_METRIC_CITATIONS, 1)],
                json_encode([
                    [
                        'service' => 'Service',
                        'uri' => 'http://www.example.com/',
                        'citations' => (int) $id,
                    ],
                ])
            )
        );
    }

    final protected function mockMetricDownloadsCall(string $type, string $id)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/metrics/'.$type.'/'.$id.'/downloads?by=month&page=1&per-page=20&order=desc',
                ['Accept' => new MediaType(MetricsClient::TYPE_METRIC_TIME_PERIOD, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(MetricsClient::TYPE_METRIC_TIME_PERIOD, 1)],
                json_encode([
                    'totalPeriods' => 1,
                    'totalValue' => (int) $id,
                    'periods' => [
                        [
                            'period' => '2016-01-01',
                            'value' => (int) $id,
                        ],
                    ],
                ])
            )
        );
    }

    final protected function mockMetricPageViewsCall(string $type, string $id)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/metrics/'.$type.'/'.$id.'/page-views?by=month&page=1&per-page=20&order=desc',
                ['Accept' => new MediaType(MetricsClient::TYPE_METRIC_TIME_PERIOD, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(MetricsClient::TYPE_METRIC_TIME_PERIOD, 1)],
                json_encode([
                    'totalPeriods' => 1,
                    'totalValue' => (int) $id,
                    'periods' => [
                        [
                            'period' => '2016-01-01',
                            'value' => (int) $id,
                        ],
                    ],
                ])
            )
        );
    }

    final protected function mockPersonListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true,
        array $subjects = [],
        string $type = null
    ) {
        $people = array_map(function (int $id) {
            return $this->createPersonJson('person'.$id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $subjectsQuery = implode('', array_map(function (string $subjectId) {
            return '&subject[]='.$subjectId;
        }, $subjects));

        if ($type) {
            $typeQuery = '&type='.$type;
        } else {
            $typeQuery = '';
        }

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/people?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').$subjectsQuery.$typeQuery,
                ['Accept' => new MediaType(PeopleClient::TYPE_PERSON_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(PeopleClient::TYPE_PERSON_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $people,
                ])
            )
        );
    }

    /**
     * @param string|int $numberOrId
     */
    final protected function mockPersonCall($numberOrId, bool $complete = false, bool $isSnippet = false)
    {
        if (is_integer($numberOrId)) {
            $id = "person{$numberOrId}";
        } else {
            $id = (string) $numberOrId;
        }
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/people/'.$id,
                ['Accept' => new MediaType(PeopleClient::TYPE_PERSON, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(PeopleClient::TYPE_PERSON, 1)],
                json_encode($this->createPersonJson($id, $isSnippet, $complete))
            )
        );
    }

    final protected function mockPodcastEpisodeListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true,
        array $subjects = []
    ) {
        $podcastEpisodes = array_map(function (int $id) {
            return $this->createPodcastEpisodeJson($id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $subjectsQuery = implode('', array_map(function (string $subjectId) {
            return '&subject[]='.$subjectId;
        }, $subjects));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/podcast-episodes?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').$subjectsQuery,
                ['Accept' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $podcastEpisodes,
                ])
            )
        );
    }

    final protected function mockPodcastEpisodeCall(int $number, bool $complete = false)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/podcast-episodes/'.$number,
                ['Accept' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(PodcastClient::TYPE_PODCAST_EPISODE, 1)],
                json_encode($this->createPodcastEpisodeJson($number, false, $complete))
            )
        );
    }

    final protected function mockCollectionListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true,
        array $subjects = []
    ) {
        $collections = array_map(function (int $id) {
            return $this->createCollectionJson($id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $subjectsQuery = implode('', array_map(function (string $subjectId) {
            return '&subject[]='.$subjectId;
        }, $subjects));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/collections?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').$subjectsQuery,
                ['Accept' => new MediaType(CollectionsClient::TYPE_COLLECTION_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(CollectionsClient::TYPE_COLLECTION_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $collections,
                ])
            )
        );
    }

    final protected function mockCollectionCall(string $id, bool $complete = true)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/collections/'.$id,
                ['Accept' => new MediaType(CollectionsClient::TYPE_COLLECTION, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(CollectionsClient::TYPE_COLLECTION, 1)],
                json_encode($this->createCollectionJson($id, false, $complete))
            )
        );
    }

    final protected function mockSearchCall(
        int $page = 1,
        int $perPage = 100,
        int $total = 100,
        string $query = '',
        $descendingOrder = true,
        array $subjects = [],
        array $types = [],
        $sort = 'relevance',
        DateTimeImmutable $startDate = null,
        DateTimeImmutable $endDate = null
    ) {
        $results = array_map(function (int $id) {
            return $this->createSearchResultJson($id);
        }, $this->generateIdList($page, $perPage, $total));

        $subjectsQuery = implode('', array_map(function (string $subjectId) {
            return '&subject[]='.$subjectId;
        }, $subjects));

        $typesQuery = implode('', array_map(function (string $type) {
            return '&type[]='.$type;
        }, $types));

        $startsQuery = $startDate ? '&start-date='.$startDate->format('Y-m-d') : '';
        $endsQuery = $endDate ? '&end-date='.$endDate->format('Y-m-d') : '';

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/search?for='.$query.'&page='.$page.'&per-page='.$perPage.'&sort='.$sort.'&order='.($descendingOrder ? 'desc' : 'asc').$subjectsQuery.$typesQuery.$startsQuery.$endsQuery,
                ['Accept' => new MediaType(SearchClient::TYPE_SEARCH, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(SearchClient::TYPE_SEARCH, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $results,
                    'subjects' => [
                        [
                            'id' => 'subject1',
                            'name' => 'Subject 1',
                            'results' => $firstSubjectResult = round($total / 2),
                        ],
                        [
                            'id' => 'subject2',
                            'name' => 'Subject 2',
                            'results' => $total - $firstSubjectResult,
                        ],
                    ],
                    'types' => [
                        'correction' => $firstTypeResult = round($total / 3),
                        'editorial' => $secondTypeResult = round($total / 3),
                        'feature' => $total - $firstTypeResult - $secondTypeResult,
                        'insight' => 0,
                        'research-advance' => 0,
                        'research-article' => 0,
                        'research-exchange' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-experiment' => 0,
                        'podcast-episode' => 0,
                    ],
                ])
            )
        );
    }

    final protected function mockSubjectListCall(int $page, int $perPage, int $total, $descendingOrder = true)
    {
        $subjects = array_map(function (int $id) {
            return $this->createSubjectJson('subject'.$id);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(SubjectsClient::TYPE_SUBJECT_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $subjects,
                ])
            )
        );
    }

    final protected function mockSubjectCall($numberOrId)
    {
        if (is_integer($numberOrId)) {
            $id = "subject{$numberOrId}";
        } else {
            $id = (string) $numberOrId;
        }
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects/'.$id,
                ['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(SubjectsClient::TYPE_SUBJECT, 1)],
                json_encode($this->createSubjectJson($id))
            )
        );
    }

    private function generateIdList(int $page, int $perPage, int $total) : array
    {
        $firstId = ($page * $perPage) - $perPage + 1;
        if ($firstId > $total) {
            throw new LogicException('Page should not exist');
        }

        $lastId = $firstId + $perPage - 1;
        if ($lastId > $total) {
            $lastId = $total;
        }

        return range($firstId, $lastId);
    }

    final private function createAnnualReportJson(int $year)
    {
        return [
            'year' => $year,
            'uri' => 'http://www.example.com/annual-reports/'.$year,
            'title' => 'Annual report '.$year.' title',
            'impactStatement' => 'Annual report '.$year.' impact statement',
            'image' => [
                'alt' => '',
                'sizes' => [
                    '16:9' => [
                        '250' => 'https://placehold.it/250x141',
                        '500' => 'https://placehold.it/500x281',
                    ],
                    '1:1' => [
                        '70' => 'https://placehold.it/70x70',
                        '140' => 'https://placehold.it/140x140',
                    ],
                ],
            ],
        ];
    }

    private function createArticleHistoryJson(string $id, bool $complete = false) : array
    {
        $articleHistory = [
            'received' => '2014-01-01',
            'accepted' => '2014-02-01',
            'versions' => [
                $this->createArticlePoAJson($id, true, $complete, 1),
                $this->createArticleVoRJson($id, true, $complete, 2),
            ],
        ];

        if (!$complete) {
            unset($articleHistory['received']);
            unset($articleHistory['accepted']);
            unset($articleHistory['versions'][1]);
        }

        return $articleHistory;
    }

    private function createRelatedArticlesJson(string $id, bool $complete = false) : array
    {
        return [
            $this->createArticlePoAJson($id.'related1', true, $complete, 1),
            $this->createArticleVoRJson($id.'related1', true, $complete, 2),
            $this->createExternalArticleJson($complete),
        ];
    }

    private function createArticlePoAJson(string $id, bool $isSnippet = false, bool $complete = false, int $version = 1) : array
    {
        $article = [
            'status' => 'poa',
            'stage' => 'published',
            'id' => $id,
            'version' => $version,
            'type' => 'research-article',
            'doi' => '10.7554/eLife.'.$id,
            'title' => 'Article '.$id.' title',
            'titlePrefix' => 'Article '.$id.' title prefix',
            'published' => '2000-01-01T00:00:00Z',
            'versionDate' => '1999-12-31T00:00:00Z',
            'statusDate' => '1999-12-31T00:00:00Z',
            'volume' => 1,
            'issue' => 1,
            'elocationId' => 'e'.$id,
            'pdf' => 'http://www.example.com/',
            'subjects' => [$this->createSubjectJson('1', true)],
            'researchOrganisms' => ['Article '.$id.' research organism'],
            'copyright' => [
                'license' => 'CC-BY-4.0',
                'holder' => 'Author et al',
                'statement' => 'Statement',
            ],
            'authorLine' => 'Author et al',
            'authors' => [
                [
                    'type' => 'person',
                    'name' => [
                        'preferred' => 'Author',
                        'index' => 'Author',
                    ],
                ],
            ],
            'reviewers' => [
                [
                    'name' => [
                        'preferred' => 'Reviewer',
                        'index' => 'Reviewer',
                    ],
                    'role' => 'Role',
                ],
            ],
            'abstract' => [
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Article '.$id.' abstract text',
                    ],
                ],
            ],
            'funding' => [
                'awards' => [
                    [
                        'id' => 'award',
                        'source' => [
                            'funderId' => '10.13039/501100001659',
                            'name' => [
                                'Funder',
                            ],
                        ],
                        'awardId' => 'awardId',
                        'recipients' => [
                            [
                                'type' => 'person',
                                'name' => [
                                    'preferred' => 'Author',
                                    'index' => 'Author',
                                ],
                            ],
                        ],
                    ],
                ],
                'statement' => 'Funding statement',
            ],
            'dataSets' => [
                'generated' => [
                    [
                        'id' => 'id',
                        'date' => '2000-01-02',
                        'authors' => [
                            [
                                'type' => 'person',
                                'name' => [
                                    'preferred' => 'preferred name',
                                    'index' => 'index name',
                                ],
                            ],
                        ],
                        'title' => 'title',
                        'uri' => 'https://doi.org/10.1000/182',
                        'authorsEtAl' => true,
                        'dataId' => 'data id',
                        'details' => 'details',
                        'doi' => '10.1000/182',
                    ],
                ],
                'used' => [
                    [
                        'id' => 'id',
                        'date' => '2000',
                        'authors' => [
                            [
                                'type' => 'person',
                                'name' => [
                                    'preferred' => 'preferred name',
                                    'index' => 'index name',
                                ],
                            ],
                        ],
                        'title' => 'title',
                        'uri' => 'http://www.example.com/',
                    ],
                ],
            ],
            'additionalFiles' => [
                [
                    'id' => 'file1',
                    'title' => 'Additional file 1',
                    'mediaType' => 'image/jpeg',
                    'uri' => 'https://placehold.it/900x450',
                    'filename' => 'image.jpeg',
                ],
            ],
        ];

        if (!$complete) {
            $article['stage'] = 'preview';
            unset($article['titlePrefix']);
            unset($article['published']);
            unset($article['versionDate']);
            unset($article['statusDate']);
            unset($article['issue']);
            unset($article['pdf']);
            unset($article['subjects']);
            unset($article['researchOrganisms']);
            unset($article['reviewers']);
            unset($article['abstract']);
            unset($article['funding']);
            unset($article['dataSets']);
            unset($article['additionalFiles']);
        }

        if ($isSnippet) {
            unset($article['issue']);
            unset($article['copyright']);
            unset($article['authors']);
            unset($article['reviewers']);
            unset($article['abstract']);
            unset($article['funding']);
            unset($article['dataSets']);
            unset($article['additionalFiles']);
        }

        return $article;
    }

    private function createArticleVoRJson(string $id, bool $isSnippet = false, bool $complete = false, int $version = 1) : array
    {
        $article = $this->createArticlePoAJson($id, $isSnippet, $complete, $version);

        $article['status'] = 'vor';

        if (false === empty($article['abstract'])) {
            $article['abstract']['doi'] = '10.7554/eLife.'.$id.'abstract';
        }

        $article += [
            'impactStatement' => 'Article '.$id.' impact statement',
            'image' => [
                'banner' => [
                    'alt' => '',
                    'sizes' => [
                        '2:1' => [
                            '900' => 'https://placehold.it/900x450',
                            '1800' => 'https://placehold.it/1800x900',
                        ],
                    ],
                ],
                'thumbnail' => [
                    'alt' => '',
                    'sizes' => [
                        '16:9' => [
                            '250' => 'https://placehold.it/250x141',
                            '500' => 'https://placehold.it/500x281',
                        ],
                        '1:1' => [
                            '70' => 'https://placehold.it/70x70',
                            '140' => 'https://placehold.it/140x140',
                        ],
                    ],
                ],
            ],
            'keywords' => ['Article '.$id.' keyword'],
            'digest' => [
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Article '.$id.' digest',
                    ],
                ],
                'doi' => '10.7554/eLife.'.$id.'digest',
            ],
            'body' => [
                [
                    'type' => 'section',
                    'title' => 'Article '.$id.' section title',
                    'id' => 'article'.$id.'section',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Article '.$id.' text',
                        ],
                    ],
                ],
            ],
            'appendices' => [
                [
                    'id' => 'app1',
                    'title' => 'Appendix 1',
                    'content' => [
                        [
                            'type' => 'section',
                            'id' => 'app1-1',
                            'title' => 'Appendix 1 title',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'text' => 'Appendix 1 text',
                                ],
                            ],
                        ],
                    ],
                    'doi' => '10.7554/eLife.09560.app1',
                ],
            ],
            'references' => [
                [
                    'id' => 'ref1',
                    'type' => 'book',
                    'date' => '2000',
                    'authors' => [
                        [
                            'type' => 'person',
                            'name' => [
                                'preferred' => 'preferred name',
                                'index' => 'index name',
                            ],
                        ],
                    ],
                    'bookTitle' => 'book title',
                    'publisher' => [
                        'name' => ['publisher'],
                    ],
                ],
            ],
            'acknowledgements' => [
                [
                    'type' => 'paragraph',
                    'text' => 'acknowledgements',
                ],
            ],
            'ethics' => [
                [
                    'type' => 'paragraph',
                    'text' => 'ethics',
                ],
            ],
            'decisionLetter' => [
                'doi' => '10.7554/eLife.'.$id.'decisionLetter',
                'description' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Article '.$id.' decision letter description',
                    ],
                ],
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Article '.$id.' decision letter text',
                    ],
                ],
            ],
            'authorResponse' => [
                'doi' => '10.7554/eLife.'.$id.'authorResponse',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Article '.$id.' author response text',
                    ],
                ],
            ],
        ];

        if (!$complete) {
            unset($article['impactStatement']);
            unset($article['image']);
            unset($article['keywords']);
            unset($article['digest']);
            unset($article['appendices']);
            unset($article['references']);
            unset($article['acknowledgements']);
            unset($article['ethics']);
            unset($article['decisionLetter']);
            unset($article['authorResponse']);
        }

        if ($isSnippet) {
            if (isset($article['image'])) {
                unset($article['image']['banner']);
            }
            unset($article['keywords']);
            unset($article['digest']);
            unset($article['body']);
            unset($article['appendices']);
            unset($article['references']);
            unset($article['acknowledgements']);
            unset($article['ethics']);
            unset($article['decisionLetter']);
            unset($article['authorResponse']);
        }

        return $article;
    }

    private function createExternalArticleJson(string $id) : array
    {
        return [
            'type' => 'external-article',
            'articleTitle' => "External article $id title",
            'journal' => [
                'name' => [
                    "External article $id journal",
                ],
            ],
            'authorLine' => 'Author et all',
            'uri' => "https://doi.org/10.1016/external.$id",
        ];
    }

    private function createBlogArticleJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $blogArticle = [
            'id' => $id,
            'title' => 'Blog article '.$id.' title',
            'published' => '2000-01-01T00:00:00Z',
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Blog article '.$id.' text',
                ],
            ],
        ];

        if ($complete) {
            $blogArticle['impactStatement'] = 'Blog article '.$id.' impact statement';
            $blogArticle['subjects'][] = $this->createSubjectJson(1, true);
        }

        if ($isSnippet) {
            unset($blogArticle['content']);
        }

        return $blogArticle;
    }

    private function createCoverJson(int $number, array $item) : array
    {
        return [
            'title' => 'Cover '.$number.' title',
            'image' => [
                'alt' => '',
                'sizes' => [
                    '2:1' => [
                        '900' => 'https://placehold.it/900x450',
                        '1800' => 'https://placehold.it/1800x900',
                    ],
                ],
            ],
            'item' => $item,
        ];
    }

    private function createEventJson($number, bool $isSnippet = false, bool $complete = false) : array
    {
        if (is_int($number)) {
            $id = 'event'.$number;
        } else {
            $id = $number;
        }

        $event = [
            'id' => $id,
            'title' => 'Event '.$number.' title',
            'impactStatement' => 'Event '.$number.' impact statement',
            'starts' => '2000-01-01T00:00:00Z',
            'ends' => '2100-01-01T00:00:00Z',
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Event '.$number.' text',
                ],
            ],
        ];

        if ($complete) {
            $event['timezone'] = 'Europe/London';
            $event['venue'] = ['name' => ['venue']];
        }

        if ($isSnippet) {
            unset($event['content']);
            unset($event['venue']);
        }

        return $event;
    }

    private function createHighlightJson(int $number, array $item, bool $complete = false) : array
    {
        $highlight = [
            'title' => 'Highlight '.$number.' title',
            'authorLine' => 'Author et al',
            'image' => [
                'alt' => '',
                'sizes' => [
                    '16:9' => [
                        '250' => 'https://placehold.it/250x141',
                        '500' => 'https://placehold.it/500x281',
                    ],
                    '1:1' => [
                        '70' => 'https://placehold.it/70x70',
                        '140' => 'https://placehold.it/140x140',
                    ],
                ],
            ],
            'item' => $item,
        ];

        if (!$complete) {
            unset($highlight['authorLine']);
            unset($highlight['image']);
        }

        return $highlight;
    }

    private function createInterviewJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $interview = [
            'id' => $id,
            'interviewee' => [
                'name' => [
                    'preferred' => 'preferred name',
                    'index' => 'index name',
                ],
                'orcid' => '0000-0002-1825-0097',
                'cv' => [
                    [
                        'date' => 'date',
                        'text' => 'text',
                    ],
                ],
            ],
            'title' => 'Interview '.$id.' title',
            'impactStatement' => 'Interview '.$id.' impact statement',
            'published' => '2000-01-01T00:00:00Z',
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Interview '.$id.' text',
                ],
            ],
        ];

        if ($isSnippet) {
            unset($interview['content']);
            unset($interview['interviewee']['cv']);
        }

        if (!$complete) {
            unset($interview['impactStatement']);
            unset($interview['interviewee']['cv']);
        }

        return $interview;
    }

    private function createLabsExperimentJson(int $number, bool $isSnippet = false, bool $complete = false) : array
    {
        $labsExperiment = [
            'number' => $number,
            'title' => 'Labs experiment '.$number.' title',
            'impactStatement' => 'Labs experiment '.$number.' impact statement',
            'published' => '2000-01-01T00:00:00Z',
            'image' => [
                'banner' => [
                    'alt' => '',
                    'sizes' => [
                        '2:1' => [
                            '900' => 'https://placehold.it/900x450',
                            '1800' => 'https://placehold.it/1800x900',
                        ],
                    ],
                ],
                'thumbnail' => [
                    'alt' => '',
                    'sizes' => [
                        '16:9' => [
                            '250' => 'https://placehold.it/250x141',
                            '500' => 'https://placehold.it/500x281',
                        ],
                        '1:1' => [
                            '70' => 'https://placehold.it/70x70',
                            '140' => 'https://placehold.it/140x140',
                        ],
                    ],
                ],
            ],
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Labs experiment '.$number.' text',
                ],
            ],
        ];

        if ($isSnippet) {
            unset($labsExperiment['content']);
            unset($labsExperiment['image']['banner']);
        }

        if (!$complete) {
            unset($labsExperiment['impactStatement']);
        }

        return $labsExperiment;
    }

    final private function createMediumArticleJson(int $number)
    {
        return [
            'uri' => 'http://www.example.com/mediumArticle'.$number,
            'title' => 'Medium article '.$number.' title',
            'impactStatement' => 'Subject '.$number.' impact statement',
            'published' => '2000-01-01T00:00:00Z',
            'image' => [
                'alt' => '',
                'sizes' => [
                    '16:9' => [
                        '250' => 'https://placehold.it/250x141',
                        '500' => 'https://placehold.it/500x281',
                    ],
                    '1:1' => [
                        '70' => 'https://placehold.it/70x70',
                        '140' => 'https://placehold.it/140x140',
                    ],
                ],
            ],
        ];
    }

    private function createPersonJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $person = [
            'id' => $id,
            'type' => 'senior-editor',
            'name' => [
                'preferred' => $id.' preferred',
                'index' => $id.' index',
            ],
            'orcid' => '0000-0002-1825-0097',
            'research' => [
                'expertises' => [
                    [
                        'id' => 'subject1',
                        'name' => 'Subject 1 name',
                    ],
                ],
                'focuses' => [
                    'Focus',
                ],
                'organisms' => [
                    'Organism',
                ],
            ],
            'profile' => [
                [
                    'type' => 'paragraph',
                    'text' => $id.' profile text',
                ],
            ],
            'competingInterests' => $id.' competing interests',
            'image' => [
                'alt' => '',
                'sizes' => [
                    '16:9' => [
                        '250' => 'https://placehold.it/250x141',
                        '500' => 'https://placehold.it/500x281',
                    ],
                    '1:1' => [
                        '70' => 'https://placehold.it/70x70',
                        '140' => 'https://placehold.it/140x140',
                    ],
                ],
            ],
        ];

        if (!$complete) {
            unset($person['orcid']);
            unset($person['research']);
            unset($person['profile']);
            unset($person['competingInterests']);
            unset($person['image']);
        }

        if ($isSnippet) {
            unset($person['research']);
            unset($person['profile']);
            unset($person['competingInterests']);
        }

        return $person;
    }

    private function createPodcastEpisodeJson(int $number, bool $isSnippet = false, bool $complete = false) : array
    {
        $podcastEpisode = [
            'number' => $number,
            'title' => 'Podcast episode '.$number.' title',
            'impactStatement' => 'Podcast episode '.$number.' impact statement',
            'published' => '2000-01-01T00:00:00Z',
            'image' => [
                'banner' => [
                    'alt' => '',
                    'sizes' => [
                        '2:1' => [
                            '900' => 'https://placehold.it/900x450',
                            '1800' => 'https://placehold.it/1800x900',
                        ],
                    ],
                ],
                'thumbnail' => [
                    'alt' => '',
                    'sizes' => [
                        '16:9' => [
                            '250' => 'https://placehold.it/250x141',
                            '500' => 'https://placehold.it/500x281',
                        ],
                        '1:1' => [
                            '70' => 'https://placehold.it/70x70',
                            '140' => 'https://placehold.it/140x140',
                        ],
                    ],
                ],
            ],
            'sources' => [
                [
                    'mediaType' => 'audio/mpeg',
                    'uri' => 'https://www.example.com/episode.mp3',
                ],
            ],
            'subjects' => [$this->createSubjectJson(1, true)],
            'chapters' => [
                [
                    'number' => 1,
                    'title' => 'Chapter title',
                    'time' => 0,
                    'impactStatement' => 'Chapter impact statement',
                    'content' => [$this->createArticlePoAJson('1', true, $complete)],
                ],
            ],
        ];

        if (!$complete) {
            unset($podcastEpisode['impactStatement']);
            unset($podcastEpisode['subjects']);
            unset($podcastEpisode['chapters'][0]['impactStatement']);
            unset($podcastEpisode['chapters'][0]['content']);
        }

        if ($isSnippet) {
            unset($podcastEpisode['image']['banner']);
            unset($podcastEpisode['chapters']);
        }

        return $podcastEpisode;
    }

    private function createCollectionJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $collection = [
            'id' => $id,
            'title' => ucfirst($id),
            'subTitle' => ucfirst($id).' subtitle',
            'impactStatement' => ucfirst($id).' impact statement',
            'updated' => '2000-01-01T00:00:00Z',
            'image' => [
                'banner' => [
                    'alt' => '',
                    'sizes' => [
                        '2:1' => [
                            '900' => 'https://placehold.it/900x450',
                            '1800' => 'https://placehold.it/1800x900',
                        ],
                    ],
                ],
                'thumbnail' => [
                    'alt' => '',
                    'sizes' => [
                        '16:9' => [
                            '250' => 'https://placehold.it/250x141',
                            '500' => 'https://placehold.it/500x281',
                        ],
                        '1:1' => [
                            '70' => 'https://placehold.it/70x70',
                            '140' => 'https://placehold.it/140x140',
                        ],
                    ],
                ],
            ],
            'selectedCurator' => [
                'id' => 'pjha',
                'type' => 'senior-editor',
                'name' => [
                    'preferred' => 'Prabhat Jha',
                    'index' => 'Jha, Prabhat',
                ],
                'etAl' => true,
            ],
            'curators' => [
                [
                    'id' => 'bcooper',
                    'type' => 'reviewing-editor',
                    'name' => [
                        'preferred' => 'Ben Cooper',
                        'index' => 'Cooper, Ben',
                    ],
                ],
                [
                    'id' => 'pjha',
                    'type' => 'senior-editor',
                    'name' => [
                        'preferred' => 'Prabhat Jha',
                        'index' => 'Jha, Prabhat',
                    ],
                ],
            ],
            'content' => [
                [
                    'type' => 'blog-article',
                    'id' => '359325',
                    'title' => 'Media coverage: Slime can see',
                    'impactStatement' => 'In their research paper – Cyanobacteria use micro-optics to sense light direction – Schuergers et al. reveal how bacterial cells act as the equivalent of a microscopic eyeball or the world’s oldest and smallest camera eye, allowing them to ‘see’.',
                    'published' => '2016-07-08T08:33:25Z',
                    'subjects' => [
                        [
                            'id' => 'biophysics-structural-biology',
                            'name' => 'Biophysics and Structural Biology',
                        ],
                    ],
                ],
            ],
            'relatedContent' => [
                [
                    'type' => 'research-article',
                    'status' => 'poa',
                    'stage' => 'published',
                    'id' => '14107',
                    'version' => 1,
                    'doi' => '10.7554/eLife.14107',
                    'authorLine' => 'Yongjian Huang et al',
                    'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
                    'published' => '2016-03-28T00:00:00Z',
                    'versionDate' => '2016-03-28T00:00:00Z',
                    'statusDate' => '2016-03-28T00:00:00Z',
                    'volume' => 5,
                    'elocationId' => 'e14107',
                ],
            ],
            'podcastEpisodes' => [
                $podcastEpisode = [
                    'number' => 29,
                    'title' => 'April/May 2016',
                    'published' => '2016-05-27T13:19:42Z',
                    'image' => [
                        'thumbnail' => [
                            'alt' => '',
                            'sizes' => [
                                '16:9' => [
                                    '250' => 'https://placehold.it/250x141',
                                    '500' => 'https://placehold.it/500x281',
                                ],
                                '1:1' => [
                                    '70' => 'https://placehold.it/70x70',
                                    '140' => 'https://placehold.it/140x140',
                                ],
                            ],
                        ],
                    ],
                    'sources' => [
                        [
                            'mediaType' => 'audio/mpeg',
                            'uri' => 'https://nakeddiscovery.com/scripts/mp3s/audio/eLife_Podcast_16.05.mp3',
                        ],
                    ],
                ],
            ],
            'subjects' => [$this->createSubjectJson(1, true)],
        ];

        if (!$complete) {
            unset($collection['impactStatement']);
            unset($collection['selectedCurator']['etAl']);
            unset($collection['subTitle']);
            unset($collection['relatedContent']);
            unset($collection['podcastEpisodes']);
            unset($collection['subjects']);
        }

        if ($isSnippet) {
            unset($collection['image']['banner']);
            unset($collection['subTitle']);
            unset($collection['curators']);
            unset($collection['content']);
            unset($collection['relatedContent']);
            unset($collection['podcastEpisodes']);
        }

        return $collection;
    }

    private function createSearchResultJson(string $id) : array
    {
        $allowedModelFactories = [
            'createArticlePoAJson' => 'research-article',
            'createArticleVoRJson' => 'research-article',
            'createBlogArticleJson' => 'blog-article',
            'createCollectionJson' => 'collection',
            'createInterviewJson' => 'interview',
            'createLabsExperimentJson' => 'labs-experiment',
            'createPodcastEpisodeJson' => 'podcast-episode',
        ];
        $index = (((int) $id) - 1) % count($allowedModelFactories);
        $selectedModelFactory = array_keys($allowedModelFactories)[$index];
        $type = array_values($allowedModelFactories)[$index];

        return array_merge(
            $this->$selectedModelFactory($id, $isSnippet = true),
            ['type' => $type]
        );
    }

    final private function createSubjectJson(string $id, bool $isSnippet = false) : array
    {
        $subject = [
            'id' => $id,
            'name' => 'Subject '.$id.' name',
            'impactStatement' => 'Subject '.$id.' impact statement',
            'image' => [
                'banner' => [
                    'alt' => '',
                    'sizes' => [
                        '2:1' => [
                            '900' => 'https://placehold.it/900x450',
                            '1800' => 'https://placehold.it/1800x900',
                        ],
                    ],
                ],
                'thumbnail' => [
                    'alt' => '',
                    'sizes' => [
                        '16:9' => [
                            '250' => 'https://placehold.it/250x141',
                            '500' => 'https://placehold.it/500x281',
                        ],
                        '1:1' => [
                            '70' => 'https://placehold.it/70x70',
                            '140' => 'https://placehold.it/140x140',
                        ],
                    ],
                ],
            ],
        ];

        if ($isSnippet) {
            unset($subject['impactStatement']);
            unset($subject['image']);
        }

        return $subject;
    }
}
