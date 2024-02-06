<?php

namespace test\eLife\ApiSdk;

use ComposerLocator;
use Csa\GuzzleHttp\Middleware\Cache\MockMiddleware;
use DateTimeImmutable;
use eLife\ApiClient\ApiClient\AnnotationsClient;
use eLife\ApiClient\ApiClient\AnnualReportsClient;
use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiClient\ApiClient\BioprotocolClient;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiClient\ApiClient\CommunityClient;
use eLife\ApiClient\ApiClient\CoversClient;
use eLife\ApiClient\ApiClient\DigestsClient;
use eLife\ApiClient\ApiClient\EventsClient;
use eLife\ApiClient\ApiClient\HighlightsClient;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiClient\ApiClient\JobAdvertsClient;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\ApiClient\MetricsClient;
use eLife\ApiClient\ApiClient\PeopleClient;
use eLife\ApiClient\ApiClient\PodcastClient;
use eLife\ApiClient\ApiClient\PressPackagesClient;
use eLife\ApiClient\ApiClient\ProfilesClient;
use eLife\ApiClient\ApiClient\PromotionalCollectionsClient;
use eLife\ApiClient\ApiClient\RecommendationsClient;
use eLife\ApiClient\ApiClient\ReviewedPreprintsClient;
use eLife\ApiClient\ApiClient\SearchClient;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\HttpClient;
use eLife\ApiClient\HttpClient\Guzzle6HttpClient;
use eLife\ApiClient\MediaType;
use eLife\ApiValidator\MessageValidator\JsonMessageValidator;
use eLife\ApiValidator\SchemaFinder\PathBasedSchemaFinder;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use JsonSchema\Validator;
use LogicException;

abstract class ApiTestCase extends TestCase
{
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
                new PathBasedSchemaFinder(ComposerLocator::getPath('elife/api').'/dist/model'),
                new Validator()
            );

            $this->storage = new ValidatingStorageAdapter($storage, $validator);
            $this->addMockMiddleware();
        }

        return $this->httpClient;
    }

    private function addMockMiddleware()
    {
        $stack = HandlerStack::create();
        $stack->push(new MockMiddleware($this->storage, 'replay'));

        $this->httpClient = new Guzzle6HttpClient(new Client([
            'base_uri' => 'http://api.elifesciences.org',
            'handler' => $stack,
        ]));
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

    final protected function mockAnnotationListCall(
        string $by,
        int $page,
        int $perPage,
        int $total,
        bool $descendingOrder = true,
        string $useDate = 'updated',
        string $access = 'public'
    ) {
        $annotations = array_map(function (int $id) {
            return $this->createAnnotationJson('annotation-'.$id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/annotations?by='.$by.'&page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').'&use-date='.$useDate.'&access='.$access,
                ['Accept' => (string) new MediaType(AnnotationsClient::TYPE_ANNOTATION_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(AnnotationsClient::TYPE_ANNOTATION_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $annotations,
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
                ['Accept' => (string) new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT_LIST, 2)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT_LIST, 2)],
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
                ['Accept' => (string) new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT, 2)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(AnnualReportsClient::TYPE_ANNUAL_REPORT, 2)],
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
                ['Accept' => (string) new MediaType(ArticlesClient::TYPE_ARTICLE_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(ArticlesClient::TYPE_ARTICLE_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $articles,
                ])
            )
        );
    }

    final protected function mockReviewedPreprintListCall(
        int $page,
        int $perPage,
        int $total,
        bool $descendingOrder = true,
        string $sort = 'date',
        string $useDate = 'default',
        DateTimeImmutable $startDate = null,
        DateTimeImmutable $endDate = null
    ) {
        $reviewedPreprints = array_map(function (int $id) {
            return $this->createReviewedPreprintJson($id, true);
        }, $this->generateIdList($page, $perPage, $total));
        $startsQuery = $startDate ? '&start-date='.$startDate->format('Y-m-d') : '';
        $endsQuery = $endDate ? '&end-date='.$endDate->format('Y-m-d') : '';
var_dump(            'http://api.elifesciences.org/reviewed-preprints?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').'&use-date='.$useDate.$startsQuery.$endsQuery);
        $request = new Request(
            'GET',
            'http://api.elifesciences.org/reviewed-preprints?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').'&use-date='.$useDate.$startsQuery.$endsQuery,
            ['Accept' => (string) new MediaType(ReviewedPreprintsClient::TYPE_REVIEWED_PREPRINT_LIST, 1)]
        );
        $this->storage->save(
            $request,
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(ReviewedPreprintsClient::TYPE_REVIEWED_PREPRINT_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $reviewedPreprints,
                ])
            )
        );
    }

    final protected function mockReviewedPreprintCall($numberOrId, bool $complete = false, bool $isSnippet = false)
    {
        if (is_integer($numberOrId)) {
            $id = "reviewed-preprint-{$numberOrId}";
        } else {
            $id = (string) $numberOrId;
        }
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/reviewed-preprints/'.$id,
                ['Accept' => (string) new MediaType(ReviewedPreprintsClient::TYPE_REVIEWED_PREPRINT, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(ReviewedPreprintsClient::TYPE_REVIEWED_PREPRINT, 1)],
                json_encode($this->createReviewedPreprintJson($id, $isSnippet, $complete))
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
            ['Content-Type' => (string) new MediaType(ArticlesClient::TYPE_ARTICLE_HISTORY, 2)],
            json_encode($this->createArticleHistoryJson($id, $complete))
        );

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/'.$id.'/versions',
                [
                    'Accept' => [
                        (string) new MediaType(ArticlesClient::TYPE_ARTICLE_HISTORY, 2),
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
            ['Content-Type' => (string) new MediaType(ArticlesClient::TYPE_ARTICLE_RELATED, 2)],
            json_encode($this->createRelatedArticlesJson($id, $complete))
        );

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/'.$id.'/related',
                [
                    'Accept' => [
                        (string) new MediaType(ArticlesClient::TYPE_ARTICLE_RELATED, 2),
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
                ['Content-Type' => (string) new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, 6)],
                json_encode($this->createArticleVoRJson($id, false, $complete, $version ?? 1))
            );
        } else {
            $response = new Response(
                200,
                ['Content-Type' => (string) new MediaType(ArticlesClient::TYPE_ARTICLE_POA, 3)],
                json_encode($this->createArticlePoAJson($id, false, $complete, $version ?? 1))
            );
        }

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/'.$id.($version ? '/versions/'.$version : ''),
                [
                    'Accept' => implode(', ', [
                        new MediaType(ArticlesClient::TYPE_ARTICLE_POA, 3),
                        new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, 7),
                    ]),
                ]
            ),
            $response
        );
    }

    final protected function mockBioprotocolsCall(string $type, string $id)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/bioprotocol/'.$type.'/'.$id,
                ['Accept' => (string) new MediaType(BioprotocolClient::TYPE_BIOPROTOCOL, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(BioprotocolClient::TYPE_BIOPROTOCOL, 1)],
                json_encode($this->createBioprotocolJson())
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
                ['Accept' => (string) new MediaType(BlogClient::TYPE_BLOG_ARTICLE_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(BlogClient::TYPE_BLOG_ARTICLE_LIST, 1)],
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
                ['Accept' => (string) new MediaType(BlogClient::TYPE_BLOG_ARTICLE, 2)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(BlogClient::TYPE_BLOG_ARTICLE, 2)],
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
            //'labs-post' => ['createLabsPostJson', 'int'],
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
                ['Accept' => (string) new MediaType(CommunityClient::TYPE_COMMUNITY_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(CommunityClient::TYPE_COMMUNITY_LIST, 1)],
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
        string $useDate = 'default',
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
                'http://api.elifesciences.org/covers?page='.$page.'&per-page='.$perPage.'&sort='.$sort.'&order='.($descendingOrder ? 'desc' : 'asc').'&use-date='.$useDate.$startsQuery.$endsQuery,
                ['Accept' => (string) new MediaType(CoversClient::TYPE_COVERS_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(CoversClient::TYPE_COVERS_LIST, 1)],
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
                ['Accept' => (string) new MediaType(CoversClient::TYPE_COVERS_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(CoversClient::TYPE_COVERS_LIST, 1)],
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
        string $show = 'all'
    ) {
        $events = array_map(function (int $id) {
            return $this->createEventJson($id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/events?page='.$page.'&per-page='.$perPage.'&show='.$show.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => (string) new MediaType(EventsClient::TYPE_EVENT_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(EventsClient::TYPE_EVENT_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $events,
                ])
            )
        );
    }

    /**
     * @param string|int $numberOrId
     */
    final protected function mockEventCall($numberOrId, bool $complete = false, bool $external = false)
    {
        if (is_integer($numberOrId)) {
            $id = "event{$numberOrId}";
        } else {
            $id = (string) $numberOrId;
        }
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/events/'.$id,
                ['Accept' => (string) new MediaType(EventsClient::TYPE_EVENT, 2)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(EventsClient::TYPE_EVENT, 2)],
                json_encode($this->createEventJson($id, false, $complete, $external))
            )
        );
    }

    final protected function mockJobAdvertListCall(
        int $page,
        int $perPage,
        int $total,
        bool $descendingOrder = true,
        string $show = 'all'
    ) {
        $jobAdverts = array_map(function (int $id) {
            return $this->createJobAdvertJson($id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/job-adverts?page='.$page.'&per-page='.$perPage.'&show='.$show.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => (string) new MediaType(JobAdvertsClient::TYPE_JOB_ADVERT_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(JobAdvertsClient::TYPE_JOB_ADVERT_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $jobAdverts,
                ])
            )
        );
    }

    /**
     * @param string|int $numberOrId
     */
    final protected function mockJobAdvertCall($numberOrId, bool $isUpdated = false)
    {
        if (is_integer($numberOrId)) {
            $id = "job-advert{$numberOrId}";
        } else {
            $id = (string) $numberOrId;
        }
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/job-adverts/'.$id,
                ['Accept' => (string) new MediaType(JobAdvertsClient::TYPE_JOB_ADVERT, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(JobAdvertsClient::TYPE_JOB_ADVERT, 1)],
                json_encode($this->createJobAdvertJson($id, false, $isUpdated))
            )
        );
    }

    final protected function mockHighlightsCall(string $id, int $page, int $perPage, int $total, $descendingOrder = true, bool $complete = false)
    {
        $highlights = array_map(function (int $id) use ($complete) {
            return $this->createHighlightJson($id, ['type' => 'interview'] + $this->createInterviewJson($id, true, $complete), $complete);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                "http://api.elifesciences.org/highlights/$id?page=$page&per-page=$perPage&order=".($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => (string) new MediaType(HighlightsClient::TYPE_HIGHLIGHT_LIST, 3)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(HighlightsClient::TYPE_HIGHLIGHT_LIST, 3)],
                json_encode([
                    'total' => $total,
                    'items' => $highlights,
                ])
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
                ['Accept' => (string) new MediaType(InterviewsClient::TYPE_INTERVIEW_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(InterviewsClient::TYPE_INTERVIEW_LIST, 1)],
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
                ['Accept' => (string) new MediaType(InterviewsClient::TYPE_INTERVIEW, 2)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(InterviewsClient::TYPE_INTERVIEW, 2)],
                json_encode($this->createInterviewJson($id, false, $complete))
            )
        );
    }

    final protected function mockLabsPostListCall(int $page, int $perPage, int $total, $descendingOrder = true)
    {
        $labsPosts = array_map(function (string $id) {
            return $this->createLabsPostJson($id);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-posts?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => (string) new MediaType(LabsClient::TYPE_POST_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(LabsClient::TYPE_POST_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $labsPosts,
                ])
            )
        );
    }

    final protected function mockLabsPostCall(string $id, bool $complete = false)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/labs-posts/'.$id,
                ['Accept' => (string) new MediaType(LabsClient::TYPE_POST, 2)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(LabsClient::TYPE_POST, 2)],
                json_encode($this->createLabsPostJson($id, false, $complete))
            )
        );
    }

    final protected function mockMetricCitationsCall(string $type, string $id)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/metrics/'.$type.'/'.$id.'/citations',
                ['Accept' => (string) new MediaType(MetricsClient::TYPE_METRIC_CITATIONS, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(MetricsClient::TYPE_METRIC_CITATIONS, 1)],
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
                ['Accept' => (string) new MediaType(MetricsClient::TYPE_METRIC_TIME_PERIOD, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(MetricsClient::TYPE_METRIC_TIME_PERIOD, 1)],
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
                ['Accept' => (string) new MediaType(MetricsClient::TYPE_METRIC_TIME_PERIOD, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(MetricsClient::TYPE_METRIC_TIME_PERIOD, 1)],
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
        array $types = []
    ) {
        $people = array_map(function (int $id) {
            return $this->createPersonJson('person'.$id);
        }, $this->generateIdList($page, $perPage, $total));

        $subjectsQuery = implode('', array_map(function (string $subjectId) {
            return '&subject[]='.$subjectId;
        }, $subjects));

        $typeQuery = implode('', array_map(function (string $type) {
            return '&type[]='.$type;
        }, $types));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/people?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').$subjectsQuery.$typeQuery,
                ['Accept' => (string) new MediaType(PeopleClient::TYPE_PERSON_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(PeopleClient::TYPE_PERSON_LIST, 1)],
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
                ['Accept' => (string) new MediaType(PeopleClient::TYPE_PERSON, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(PeopleClient::TYPE_PERSON, 1)],
                json_encode($this->createPersonJson($id, $isSnippet, $complete))
            )
        );
    }

    final protected function mockPodcastEpisodeListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true,
        array $containing = []
    ) {
        $podcastEpisodes = array_map(function (int $id) {
            return $this->createPodcastEpisodeJson($id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $containingQuery = implode('', array_map(function (string $item) {
            return '&containing[]='.$item;
        }, $containing));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/podcast-episodes?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').$containingQuery,
                ['Accept' => (string) new MediaType(PodcastClient::TYPE_PODCAST_EPISODE_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(PodcastClient::TYPE_PODCAST_EPISODE_LIST, 1)],
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
                ['Accept' => (string) new MediaType(PodcastClient::TYPE_PODCAST_EPISODE, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(PodcastClient::TYPE_PODCAST_EPISODE, 1)],
                json_encode($this->createPodcastEpisodeJson($number, false, $complete))
            )
        );
    }

    final protected function mockPressPackagesListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true
    ) {
        $pressPackages = array_map(function (int $id) {
            return $this->createPressPackageJson("press-package-$id", true);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/press-packages?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => (string) new MediaType(PressPackagesClient::TYPE_PRESS_PACKAGE_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(PressPackagesClient::TYPE_PRESS_PACKAGE_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $pressPackages,
                ])
            )
        );
    }

    final protected function mockPressPackageCall($numberOrId, bool $complete = false)
    {
        if (is_integer($numberOrId)) {
            $id = "press-package-{$numberOrId}";
        } else {
            $id = (string) $numberOrId;
        }

        $this->storage->save(
            new Request(
                'GET',
                "http://api.elifesciences.org/press-packages/$id",
                ['Accept' => (string) new MediaType(PressPackagesClient::TYPE_PRESS_PACKAGE, 4)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(PressPackagesClient::TYPE_PRESS_PACKAGE, 4)],
                json_encode($this->createPressPackageJson($id, false, $complete))
            )
        );
    }

    final protected function mockProfileListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true
    ) {
        $profiles = array_map(function (int $id) {
            return $this->createProfileJson("profile{$id}");
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/profiles?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => (string) new MediaType(ProfilesClient::TYPE_PROFILE_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(ProfilesClient::TYPE_PROFILE_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $profiles,
                ])
            )
        );
    }

    /**
     * @param string|int $numberOrId
     */
    final protected function mockProfileCall($numberOrId, bool $complete = false, bool $isSnippet = false)
    {
        if (is_integer($numberOrId)) {
            $id = "profile{$numberOrId}";
        } else {
            $id = (string) $numberOrId;
        }
        $this->storage->save(
            new Request(
                'GET',
                "http://api.elifesciences.org/profiles/{$id}",
                ['Accept' => (string) new MediaType(ProfilesClient::TYPE_PROFILE, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(ProfilesClient::TYPE_PROFILE, 1)],
                json_encode($this->createProfileJson($id, $isSnippet, $complete))
            )
        );
    }

    final protected function mockCollectionListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true,
        array $subjects = [],
        array $containing = []
    ) {
        $collections = array_map(function (int $id) {
            return $this->createCollectionJson($id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $subjectsQuery = implode('', array_map(function (string $subjectId) {
            return '&subject[]='.$subjectId;
        }, $subjects));

        $containingQuery = implode('', array_map(function (string $item) {
            return '&containing[]='.$item;
        }, $containing));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/collections?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').$subjectsQuery.$containingQuery,
                ['Accept' => (string) new MediaType(CollectionsClient::TYPE_COLLECTION_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(CollectionsClient::TYPE_COLLECTION_LIST, 1)],
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
                ['Accept' => (string) new MediaType(CollectionsClient::TYPE_COLLECTION, 3)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(CollectionsClient::TYPE_COLLECTION, 3)],
                json_encode($this->createCollectionJson($id, false, $complete))
            )
        );
    }

    final protected function mockDigestListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true
    ) {
        $digests = array_map(function (int $id) {
            return $this->createDigestJson("digest-{$id}", true);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/digests?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => (string) new MediaType(DigestsClient::TYPE_DIGEST_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(DigestsClient::TYPE_DIGEST_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $digests,
                ])
            )
        );
    }

    final protected function mockDigestCall(string $id, bool $complete = true)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/digests/'.$id,
                ['Accept' => (string) new MediaType(DigestsClient::TYPE_DIGEST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(DigestsClient::TYPE_DIGEST, 1)],
                json_encode($this->createDigestJson($id, false, $complete))
            )
        );
    }

    final protected function mockRecommendationsCall(
        string $type,
        $id,
        int $page = 1,
        int $perPage = 100,
        int $total = 100,
        $descendingOrder = true
    ) {
        $recommendations = array_map(function (int $id) {
            return $this->createArticlePoAJson('article'.$id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/recommendations/'.$type.'/'.$id.'?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc'),
                ['Accept' => (string) new MediaType(RecommendationsClient::TYPE_RECOMMENDATIONS, 3)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(RecommendationsClient::TYPE_RECOMMENDATIONS, 3)],
                json_encode([
                    'total' => $total,
                    'items' => $recommendations,
                ])
            )
        );
    }

    final protected function mockPromotionalCollectionListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true,
        array $subjects = [],
        array $containing = []
    ) {
        $promotionalCollections = array_map(function (int $id) {
            return $this->createPromotionalCollectionJson($id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $subjectsQuery = implode('', array_map(function (string $subjectId) {
            return '&subject[]='.$subjectId;
        }, $subjects));

        $containingQuery = implode('', array_map(function (string $item) {
            return '&containing[]='.$item;
        }, $containing));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/promotional-collections?page='.$page.'&per-page='.$perPage.'&order='.($descendingOrder ? 'desc' : 'asc').$subjectsQuery.$containingQuery,
                ['Accept' => (string) new MediaType(PromotionalCollectionsClient::TYPE_PROMOTIONAL_COLLECTION_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(PromotionalCollectionsClient::TYPE_PROMOTIONAL_COLLECTION_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $promotionalCollections,
                ])
            )
        );
    }

    final protected function mockPromotionalCollectionCall(string $id, bool $complete = true)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/promotional-collections/'.$id,
                ['Accept' => (string) new MediaType(PromotionalCollectionsClient::TYPE_PROMOTIONAL_COLLECTION, 2)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(PromotionalCollectionsClient::TYPE_PROMOTIONAL_COLLECTION, 2)],
                json_encode($this->createPromotionalCollectionJson($id, false, $complete))
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
        $useDate = 'default',
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
                'http://api.elifesciences.org/search?for='.$query.'&page='.$page.'&per-page='.$perPage.'&sort='.$sort.'&order='.($descendingOrder ? 'desc' : 'asc').$subjectsQuery.$typesQuery.'&use-date='.$useDate.$startsQuery.$endsQuery,
                ['Accept' => (string) new MediaType(SearchClient::TYPE_SEARCH, 2)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(SearchClient::TYPE_SEARCH, 2)],
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
                        'research-communication' => 0,
                        'retraction' => 0,
                        'registered-report' => 0,
                        'replication-study' => 0,
                        'review-article' => 0,
                        'scientific-correspondence' => 0,
                        'short-report' => 0,
                        'tools-resources' => 0,
                        'blog-article' => 0,
                        'collection' => 0,
                        'interview' => 0,
                        'labs-post' => 0,
                        'podcast-episode' => 0,
                        'reviewed-preprint' => 0,
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
                ['Accept' => (string) new MediaType(SubjectsClient::TYPE_SUBJECT_LIST, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(SubjectsClient::TYPE_SUBJECT_LIST, 1)],
                json_encode([
                    'total' => $total,
                    'items' => $subjects,
                ])
            )
        );
    }

    final protected function mockSubjectCall($numberOrId, bool $complete = false, bool $isSnippet = false)
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
                ['Accept' => (string) new MediaType(SubjectsClient::TYPE_SUBJECT, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => (string) new MediaType(SubjectsClient::TYPE_SUBJECT, 1)],
                json_encode($this->createSubjectJson($id, $isSnippet, $complete))
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

    private function createAnnotationJson(string $id, bool $complete = false)
    {
        $annotation = [
            'id' => $id,
            'access' => 'public',
            'document' => [
                'uri' => 'http://www.example.com/document/',
                'title' => 'Document title',
            ],
            'highlight' => 'Highlighted text',
            'created' => '1999-12-31T00:00:00Z',
        ];

        if ($complete) {
            $annotation['ancestors'] = ['foo'];
            $annotation['updated'] = '2000-01-01T00:00:00Z';
            $annotation['content'] = [
                [
                    'type' => 'paragraph',
                    'text' => 'Annotation '.$id.' text',
                ],
            ];
        }

        return $annotation;
    }

    private function createAnnualReportJson(int $year)
    {
        return [
            'year' => $year,
            'uri' => 'http://www.example.com/annual-reports/'.$year,
            'title' => 'Annual report '.$year.' title',
            'impactStatement' => 'Annual report '.$year.' impact statement',
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
            $this->createReviewedPreprintJson($id, true, $complete, true),
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
            'pdf' => 'http://www.example.com/pdf',
            'xml' => 'http://www.example.com/xml',
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
            'ethics' => [
                [
                    'type' => 'paragraph',
                    'text' => 'ethics',
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
                'availability' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Data availability',
                    ],
                ],
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
                    'label' => 'Additional file 1',
                    'mediaType' => 'image/jpeg',
                    'uri' => 'https://placehold.it/900x450',
                    'filename' => 'image.jpeg',
                ],
            ],
            'image' => [
                'social' => [
                    'uri' => 'https://iiif.elifesciences.org/social.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                        'filename' => 'social.jpg',
                    ],
                    'size' => [
                        'width' => 600,
                        'height' => 600,
                    ],
                ],
                'thumbnail' => [
                    'alt' => '',
                    'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                        'filename' => 'thumbnail.jpg',
                    ],
                    'size' => [
                        'width' => 140,
                        'height' => 140,
                    ],
                ],
            ],
        ];

        if (!$complete) {
            $article['stage'] = 'preview';
            unset($article['image']);
            unset($article['titlePrefix']);
            unset($article['published']);
            unset($article['versionDate']);
            unset($article['statusDate']);
            unset($article['issue']);
            unset($article['pdf']);
            unset($article['xml']);
            unset($article['subjects']);
            unset($article['researchOrganisms']);
            unset($article['reviewers']);
            unset($article['abstract']);
            unset($article['ethics']);
            unset($article['funding']);
            unset($article['dataSets']);
            unset($article['additionalFiles']);
        }

        if ($isSnippet) {
            unset($article['issue']);
            unset($article['xml']);
            unset($article['copyright']);
            unset($article['authors']);
            unset($article['reviewers']);
            unset($article['ethics']);
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
            'image' => ($article['image'] ?? []) + [
                'thumbnail' => [
                    'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                        'filename' => 'thumbnail.jpg',
                    ],
                    'size' => [
                        'width' => 140,
                        'height' => 140,
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
            'editorEvaluation' => [
                'id' => 'editor-evaluation-id',
                'doi' => '10.7554/eLife.'.$id.'editorEvaluation',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Article '.$id.' editor evaluation text',
                    ],
                ],
                'scietyUri' => 'https://editor-evaluation-'.$id.'.com',
            ],
            'decisionLetter' => [
                'id' => 'decision-letter-id',
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
            'elifeAssessment' => [
                'id' => 'elife-assessment-id',
                'doi' => '10.7554/eLife.'.$id.'elifeAssessment',
                'title' => 'eLife assessment',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Article '.$id.' elife assessment text',
                    ],
                ],
                'scietyUri' => 'https://elife-assessment-'.$id.'.com',
            ],
            'recommendationsForAuthors' => [
                'id' => 'recommendations-for-authors-id',
                'doi' => '10.7554/eLife.'.$id.'recommendationsForAuthors',
                'title' => 'Recommendations for authors',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Article '.$id.' recommendations for authors text',
                    ],
                ],
            ],
            'publicReviews' => [
                [
                    'title' => 'Public review 1',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Public review 1 content',
                        ],
                    ],
                ],
            ],
            'authorResponse' => [
                'id' => 'author-response-id',
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
            unset($article['editorEvaluation']);
            unset($article['decisionLetter']);
            unset($article['authorResponse']);
            unset($article['elifeAssessment']);
            unset($article['recommendationsForAuthors']);
            unset($article['publicReviews']);
        }

        if ($isSnippet) {
            unset($article['keywords']);
            unset($article['digest']);
            unset($article['body']);
            unset($article['appendices']);
            unset($article['references']);
            unset($article['acknowledgements']);
            unset($article['ethics']);
            unset($article['editorEvaluation']);
            unset($article['decisionLetter']);
            unset($article['authorResponse']);
            unset($article['elifeAssessment']);
            unset($article['recommendationsForAuthors']);
            unset($article['publicReviews']);
        }

        return $article;
    }

    private function createExternalArticleJson(string $id) : array
    {
        return [
            'type' => 'external-article',
            'articleTitle' => "External article $id title",
            'journal' => "External article $id journal",
            'authorLine' => 'Author et all',
            'uri' => "https://doi.org/10.1016/external.$id",
        ];
    }

    private function createBioprotocolJson() : array
    {
        return [
            'total' => 1,
            'items' => [
                [
                    'sectionId' => 's1-2-3',
                    'title' => 'Section title',
                    'status' => false,
                    'uri' => 'https://example.com/s1-2-3',
                ],
                [
                    'sectionId' => 's2-3-4',
                    'title' => 'Section title',
                    'status' => true,
                    'uri' => 'https://example.com/s2-3-4',
                ],
            ],
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
            $blogArticle['updated'] = '2000-01-01T00:00:00Z';
            $blogArticle['impactStatement'] = 'Blog article '.$id.' impact statement';
            $blogArticle['image'] = [
                'social' => [
                    'uri' => 'https://iiif.elifesciences.org/social.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                        'filename' => 'social.jpg',
                    ],
                    'size' => [
                        'width' => 600,
                        'height' => 600,
                    ],
                ],
            ];
            $blogArticle['subjects'][] = $this->createSubjectJson(1, true);
        }

        if ($isSnippet) {
            unset($blogArticle['image']);
            unset($blogArticle['content']);
        }

        return $blogArticle;
    }

    private function createCoverJson(int $number, array $item) : array
    {
        return [
            'title' => 'Cover '.$number.' title',
            'image' => [
                'uri' => 'https://iiif.elifesciences.org/banner.jpg',
                'alt' => '',
                'source' => [
                    'mediaType' => 'image/jpeg',
                    'uri' => 'https://iiif.elifesciences.org/banner.jpg/full/full/0/default.jpg',
                    'filename' => 'banner.jpg',
                ],
                'size' => [
                    'width' => 1800,
                    'height' => 900,
                ],
            ],
            'item' => $item,
        ];
    }

    private function createEventJson($number, bool $isSnippet = false, bool $complete = false, bool $external = false) : array
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
            'published' => '2000-01-01T00:00:00Z',
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
            $event['updated'] = '2000-01-01T00:00:00Z';
            $event['timezone'] = 'Europe/London';
            $event['image'] = [
                'social' => [
                    'uri' => 'https://iiif.elifesciences.org/social.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                        'filename' => 'social.jpg',
                    ],
                    'size' => [
                        'width' => 600,
                        'height' => 600,
                    ],
                ],
            ];
        }

        if ($external) {
            unset($event['content']);
            $event['uri'] = 'http://www.example.com/';
        }

        if ($isSnippet) {
            unset($event['content']);
        }

        return $event;
    }

    private function createJobAdvertJson($number, bool $isSnippet = false, bool $complete = false) : array
    {
        if (is_int($number)) {
            $id = 'job-advert'.$number;
        } else {
            $id = $number;
        }

        $jobAdvert = [
            'id' => $id,
            'title' => 'Job advert '.$number.' title',
            'published' => '2000-01-01T00:00:00Z',
            'closingDate' => '2000-02-01T00:00:00Z',
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Job advert '.$number.' text',
                ],
            ],
        ];

        if ($isSnippet) {
            unset($jobAdvert['content']);
        }

        if ($complete) {
            $jobAdvert['impactStatement'] = 'Job advert '.$number.' impact statement';
            $jobAdvert['image'] = [
                'social' => [
                    'uri' => 'https://iiif.elifesciences.org/social.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                        'filename' => 'social.jpg',
                    ],
                    'size' => [
                        'width' => 600,
                        'height' => 600,
                    ],
                ],
            ];
            $jobAdvert['updated'] = '2000-01-01T00:00:00Z';
        }

        return $jobAdvert;
    }

    private function createHighlightJson(int $number, array $item, bool $complete = false) : array
    {
        $highlight = [
            'title' => 'Highlight '.$number.' title',
            'image' => [
                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                'alt' => '',
                'source' => [
                    'mediaType' => 'image/jpeg',
                    'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                    'filename' => 'thumbnail.jpg',
                ],
                'size' => [
                    'width' => 140,
                    'height' => 140,
                ],
            ],
            'item' => $item,
        ];

        if (!$complete) {
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
            'image' => [
                'thumbnail' => [
                    'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                        'filename' => 'thumbnail.jpg',
                    ],
                    'size' => [
                        'width' => 140,
                        'height' => 140,
                    ],
                ],
                'social' => [
                    'uri' => 'https://iiif.elifesciences.org/social.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                        'filename' => 'social.jpg',
                    ],
                    'size' => [
                        'width' => 600,
                        'height' => 600,
                    ],
                ],
            ],
            'published' => '2000-01-01T00:00:00Z',
            'updated' => '2000-01-02T00:00:00Z',
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Interview '.$id.' text',
                ],
            ],
        ];

        if ($isSnippet) {
            unset($interview['content']);
            unset($interview['image']['social']);
            unset($interview['interviewee']['cv']);
        }

        if (!$complete) {
            unset($interview['updated']);
            unset($interview['impactStatement']);
            unset($interview['image']['social']);
            unset($interview['interviewee']['cv']);
        }

        return $interview;
    }

    private function createLabsPostJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $labsPost = [
            'id' => $id,
            'title' => 'Labs post '.$id.' title',
            'impactStatement' => 'Labs post '.$id.' impact statement',
            'published' => '2000-01-01T00:00:00Z',
            'updated' => '2000-01-01T00:00:00Z',
            'image' => [
                'thumbnail' => [
                    'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                        'filename' => 'thumbnail.jpg',
                    ],
                    'size' => [
                        'width' => 140,
                        'height' => 140,
                    ],
                ],
                'social' => [
                    'uri' => 'https://iiif.elifesciences.org/social.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                        'filename' => 'social.jpg',
                    ],
                    'size' => [
                        'width' => 600,
                        'height' => 600,
                    ],
                ],
            ],
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Labs post '.$id.' text',
                ],
            ],
        ];

        if ($isSnippet) {
            unset($labsPost['content']);
            unset($labsPost['image']['social']);
        }

        if (!$complete) {
            unset($labsPost['updated']);
            unset($labsPost['impactStatement']);
            unset($labsPost['image']['social']);
        }

        return $labsPost;
    }

    private function createPersonJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $person = [
            'id' => $id,
            'type' => [
                'id' => 'senior-editor',
                'label' => 'Senior Editor',
            ],
            'name' => [
                'preferred' => $id.' preferred',
                'index' => $id.' index',
                'givenNames' => $id.' given',
                'surname' => $id.' surname',
            ],
            'orcid' => '0000-0002-1825-0097',
            'affiliations' => [
                [
                    'name' => ['affiliation'],
                ],
            ],
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
                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                'alt' => '',
                'source' => [
                    'mediaType' => 'image/jpeg',
                    'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                    'filename' => 'thumbnail.jpg',
                ],
                'size' => [
                    'width' => 140,
                    'height' => 140,
                ],
            ],
            'emailAddresses' => [
                [
                    'value' => 'foo@example.com',
                    'access' => 'public',
                ],
                [
                    'value' => 'secret@example.com',
                    'access' => 'restricted',
                ],
            ],
        ];

        if (!$complete) {
            unset($person['name']['givenNames']);
            unset($person['name']['surname']);
            unset($person['orcid']);
            unset($person['affiliations']);
            unset($person['research']);
            unset($person['profile']);
            unset($person['competingInterests']);
            unset($person['image']);
            unset($person['emailAddresses']);
        }

        if ($isSnippet) {
            unset($person['name']['givenNames']);
            unset($person['name']['surname']);
            unset($person['research']);
            unset($person['profile']);
            unset($person['competingInterests']);
            unset($person['emailAddresses']);
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
            'updated' => '2000-01-02T00:00:00Z',
            'image' => [
                'banner' => [
                    'uri' => 'https://iiif.elifesciences.org/banner.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/banner.jpg/full/full/0/default.jpg',
                        'filename' => 'banner.jpg',
                    ],
                    'size' => [
                        'width' => 1800,
                        'height' => 900,
                    ],
                ],
                'thumbnail' => [
                    'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                        'filename' => 'thumbnail.jpg',
                    ],
                    'size' => [
                        'width' => 140,
                        'height' => 140,
                    ],
                ],
                'social' => [
                    'uri' => 'https://iiif.elifesciences.org/social.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                        'filename' => 'social.jpg',
                    ],
                    'size' => [
                        'width' => 600,
                        'height' => 600,
                    ],
                ],
            ],
            'sources' => [
                [
                    'mediaType' => 'audio/mpeg',
                    'uri' => 'https://www.example.com/episode.mp3',
                ],
            ],
            'chapters' => [
                [
                    'number' => 1,
                    'title' => 'Chapter title',
                    'longTitle' => 'Long chapter title',
                    'time' => 0,
                    'impactStatement' => 'Chapter impact statement',
                    'content' => [$this->createArticlePoAJson('1', true, $complete)],
                ],
            ],
        ];

        if (!$complete) {
            unset($podcastEpisode['impactStatement']);
            unset($podcastEpisode['image']['social']);
            unset($podcastEpisode['updated']);
            unset($podcastEpisode['chapters'][0]['longTitle']);
            unset($podcastEpisode['chapters'][0]['impactStatement']);
            unset($podcastEpisode['chapters'][0]['content']);
        }

        if ($isSnippet) {
            unset($podcastEpisode['image']['banner']);
            unset($podcastEpisode['image']['social']);
            unset($podcastEpisode['chapters']);
        }

        return $podcastEpisode;
    }

    private function createPressPackageJson(string $id, bool $isSnippet = false, bool $isComplete = false) : array
    {
        $package = [
            'id' => $id,
            'title' => "Press package $id name",
            'impactStatement' => "Press package $id impact statement",
            'published' => '2000-01-01T00:00:00Z',
            'updated' => '2000-01-02T00:00:00Z',
            'image' => [
                'social' => [
                    'uri' => 'https://iiif.elifesciences.org/social.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                        'filename' => 'social.jpg',
                    ],
                    'size' => [
                        'width' => 600,
                        'height' => 600,
                    ],
                ],
            ],
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => "Press package $id text",
                ],
            ],
            'relatedContent' => [
                [
                    'id' => '14107',
                    'stage' => 'preview',
                    'version' => 1,
                    'type' => 'research-article',
                    'doi' => '10.7554/eLife.14107',
                    'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
                    'volume' => 5,
                    'elocationId' => 'e14107',
                    'status' => 'poa',
                ],
            ],
            'mediaContacts' => [
                [
                    'name' => [
                        'preferred' => 'preferred',
                        'index' => 'index',
                    ],
                ],
            ],
            'about' => [
                [
                    'type' => 'paragraph',
                    'text' => "Press package $id about",
                ],
            ],
        ];

        if (!$isComplete) {
            unset($package['impactStatement']);
            unset($package['image']);
            unset($package['updated']);
            unset($package['relatedContent']);
            unset($package['mediaContacts']);
            unset($package['about']);
        }

        if ($isSnippet) {
            unset($package['impactStatement']);
            unset($package['image']);
            unset($package['content']);
            unset($package['relatedContent']);
            unset($package['mediaContacts']);
            unset($package['about']);
        }

        return $package;
    }

    private function createProfileJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $profile = [
            'id' => $id,
            'name' => [
                'preferred' => "{$id} preferred",
                'index' => "{$id} index",
            ],
            'orcid' => '0000-0002-1825-0097',
            'affiliations' => [
                [
                    'value' => [
                        'name' => ['affiliation'],
                    ],
                    'access' => 'public',
                ],
            ],
            'emailAddresses' => [
                [
                    'value' => 'foo@example.com',
                    'access' => 'public',
                ],
                [
                    'value' => 'secret@example.com',
                    'access' => 'restricted',
                ],
            ],
        ];

        if (!$complete) {
            unset($profile['orcid']);
            unset($profile['affiliations']);
            unset($profile['emailAddresses']);
        }

        if ($isSnippet) {
            unset($profile['affiliations']);
            unset($profile['emailAddresses']);
        }

        return $profile;
    }

    private function createCollectionJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $collection = [
            'id' => $id,
            'title' => ucfirst($id),
            'impactStatement' => ucfirst($id).' impact statement',
            'published' => '2000-01-01T00:00:00Z',
            'updated' => '2000-01-02T00:00:00Z',
            'image' => [
                'banner' => [
                    'uri' => 'https://iiif.elifesciences.org/banner.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/banner.jpg/full/full/0/default.jpg',
                        'filename' => 'banner.jpg',
                    ],
                    'size' => [
                        'width' => 1800,
                        'height' => 900,
                    ],
                ],
                'thumbnail' => [
                    'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                        'filename' => 'thumbnail.jpg',
                    ],
                    'size' => [
                        'width' => 140,
                        'height' => 140,
                    ],
                ],
                'social' => [
                    'uri' => 'https://iiif.elifesciences.org/social.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                        'filename' => 'social.jpg',
                    ],
                    'size' => [
                        'width' => 600,
                        'height' => 600,
                    ],
                ],
            ],
            'selectedCurator' => [
                'id' => 'pjha',
                'type' => [
                    'id' => 'senior-editor',
                    'label' => 'Senior Editor',
                ],
                'name' => [
                    'preferred' => 'Prabhat Jha',
                    'index' => 'Jha, Prabhat',
                ],
                'etAl' => true,
            ],
            'curators' => [
                [
                    'id' => 'bcooper',
                    'type' => [
                        'id' => 'reviewing-editor',
                        'label' => 'Reviewing Editor',
                    ],
                    'name' => [
                        'preferred' => 'Ben Cooper',
                        'index' => 'Cooper, Ben',
                    ],
                ],
                [
                    'id' => 'pjha',
                    'type' => [
                        'id' => 'senior-editor',
                        'label' => 'Senior Editor',
                    ],
                    'name' => [
                        'preferred' => 'Prabhat Jha',
                        'index' => 'Jha, Prabhat',
                    ],
                ],
            ],
            'summary' => [
                [
                    'type' => 'paragraph',
                    'text' => 'summary',
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
                    'abstract' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 14107 abstract text',
                            ],
                        ],
                    ],
                    'image' => [
                        'social' => [
                            'uri' => 'https://iiif.elifesciences.org/social.jpg',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                                'filename' => 'social.jpg',
                            ],
                            'size' => [
                                'width' => 600,
                                'height' => 600,
                            ],
                        ],
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                    ],
                ],
            ],
            'podcastEpisodes' => [
                $podcastEpisode = [
                    'number' => 29,
                    'title' => 'April/May 2016',
                    'published' => '2016-05-27T13:19:42Z',
                    'image' => [
                        'thumbnail' => [
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
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
            unset($collection['image']['social']);
            unset($collection['updated']);
            unset($collection['selectedCurator']['etAl']);
            unset($collection['summary']);
            unset($collection['relatedContent']);
            unset($collection['podcastEpisodes']);
            unset($collection['subjects']);
        }

        if ($isSnippet) {
            unset($collection['image']['banner']);
            unset($collection['image']['social']);
            unset($collection['curators']);
            unset($collection['summary']);
            unset($collection['content']);
            unset($collection['relatedContent']);
            unset($collection['podcastEpisodes']);
        }

        return $collection;
    }

    private function createDigestJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $digest = [
            'id' => $id,
            'title' => "Digest {$id} title",
            'impactStatement' => "Digest {$id} impact statement",
            'stage' => 'published',
            'published' => '2000-01-01T00:00:00Z',
            'updated' => '2000-01-02T00:00:00Z',
            'image' => [
                'thumbnail' => [
                    'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                        'filename' => 'thumbnail.jpg',
                    ],
                    'size' => [
                        'width' => 140,
                        'height' => 140,
                    ],
                ],
            ],
            'subjects' => [$this->createSubjectJson(1, true)],
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => "Digest {$id} text",
                ],
            ],
            'relatedContent' => [
                [
                    'type' => 'research-article',
                    'status' => 'vor',
                    'stage' => 'published',
                    'id' => '09560',
                    'version' => 1,
                    'doi' => '10.7554/eLife.09560',
                    'authorLine' => 'Lee R Berger et al',
                    'title' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
                    'volume' => 4,
                    'elocationId' => 'e09560',
                    'published' => '2015-09-10T00:00:00Z',
                    'versionDate' => '2015-09-10T00:00:00Z',
                    'statusDate' => '2015-09-10T00:00:00Z',
                    'pdf' => 'https://elifesciences.org/content/4/e09560.pdf',
                    'subjects' => [
                        [
                            'id' => 'genomics-evolutionary-biology',
                            'name' => 'Genomics and Evolutionary Biology',
                        ],
                    ],
                    'impactStatement' => 'A new hominin species has been unearthed in the Dinaledi Chamber of the Rising Star cave system in the largest assemblage of a single species of hominins yet discovered in Africa.',
                    'abstract' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 09560 abstract text',
                            ],
                        ],
                        'doi' => '10.7554/eLife.09560abstract',
                    ],
                    'image' => [
                        'social' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/social.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                                'filename' => 'social.jpg',
                            ],
                            'size' => [
                                'width' => 600,
                                'height' => 600,
                            ],
                        ],
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if (!$complete) {
            unset($digest['impactStatement']);
            unset($digest['updated']);
            unset($digest['subjects']);

            unset($digest['published']);
            $digest['stage'] = 'preview';
        }

        if ($isSnippet) {
            unset($digest['content']);
            unset($digest['relatedContent']);
        }

        return $digest;
    }

    private function createPromotionalCollectionJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $promotionalCollection = [
            'id' => $id,
            'title' => ucfirst($id),
            'impactStatement' => ucfirst($id).' impact statement',
            'published' => '2000-01-01T00:00:00Z',
            'updated' => '2000-01-02T00:00:00Z',
            'image' => [
                'banner' => [
                    'uri' => 'https://iiif.elifesciences.org/banner.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/banner.jpg/full/full/0/default.jpg',
                        'filename' => 'banner.jpg',
                    ],
                    'size' => [
                        'width' => 1800,
                        'height' => 900,
                    ],
                ],
                'thumbnail' => [
                    'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                        'filename' => 'thumbnail.jpg',
                    ],
                    'size' => [
                        'width' => 140,
                        'height' => 140,
                    ],
                ],
                'social' => [
                    'uri' => 'https://iiif.elifesciences.org/social.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                        'filename' => 'social.jpg',
                    ],
                    'size' => [
                        'width' => 600,
                        'height' => 600,
                    ],
                ],
            ],
            'editors' => [
                [
                    'id' => 'bcooper',
                    'type' => [
                        'id' => 'reviewing-editor',
                        'label' => 'Reviewing Editor',
                    ],
                    'name' => [
                        'preferred' => 'Ben Cooper',
                        'index' => 'Cooper, Ben',
                    ],
                ],
                [
                    'id' => 'pjha',
                    'type' => [
                        'id' => 'senior-editor',
                        'label' => 'Senior Editor',
                    ],
                    'name' => [
                        'preferred' => 'Prabhat Jha',
                        'index' => 'Jha, Prabhat',
                    ],
                ],
            ],
            'summary' => [
                [
                    'type' => 'paragraph',
                    'text' => 'summary',
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
                    'abstract' => [
                        'content' => [
                            [
                                'type' => 'paragraph',
                                'text' => 'Article 14107 abstract text',
                            ],
                        ],
                    ],
                    'image' => [
                        'social' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/social.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                                'filename' => 'social.jpg',
                            ],
                            'size' => [
                                'width' => 600,
                                'height' => 600,
                            ],
                        ],
                        'thumbnail' => [
                            'alt' => '',
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
                            ],
                        ],
                    ],
                ],
            ],
            'podcastEpisodes' => [
                $podcastEpisode = [
                    'number' => 29,
                    'title' => 'April/May 2016',
                    'published' => '2016-05-27T13:19:42Z',
                    'image' => [
                        'thumbnail' => [
                            'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                            'alt' => '',
                            'source' => [
                                'mediaType' => 'image/jpeg',
                                'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                                'filename' => 'thumbnail.jpg',
                            ],
                            'size' => [
                                'width' => 140,
                                'height' => 140,
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
            unset($promotionalCollection['impactStatement']);
            unset($promotionalCollection['image']['social']);
            unset($promotionalCollection['updated']);
            unset($promotionalCollection['editors']);
            unset($promotionalCollection['summary']);
            unset($promotionalCollection['relatedContent']);
            unset($promotionalCollection['podcastEpisodes']);
            unset($promotionalCollection['subjects']);
        }

        if ($isSnippet) {
            unset($promotionalCollection['image']['banner']);
            unset($promotionalCollection['image']['social']);
            unset($promotionalCollection['editors']);
            unset($promotionalCollection['summary']);
            unset($promotionalCollection['content']);
            unset($promotionalCollection['relatedContent']);
            unset($promotionalCollection['podcastEpisodes']);
        }

        return $promotionalCollection;
    }

    private function createSearchResultJson(string $id) : array
    {
        $allowedModelFactories = [
            'createArticlePoAJson' => 'research-article',
            'createArticleVoRJson' => 'research-article',
            'createBlogArticleJson' => 'blog-article',
            'createCollectionJson' => 'collection',
            'createInterviewJson' => 'interview',
            'createLabsPostJson' => 'labs-post',
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

    private function createSubjectJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $subject = [
            'id' => $id,
            'name' => 'Subject '.$id.' name',
            'impactStatement' => 'Subject '.$id.' impact statement',
            'aimsAndScope' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Subject '.$id.' aims and scope',
                ],
            ],
            'image' => [
                'banner' => [
                    'uri' => 'https://iiif.elifesciences.org/banner.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/banner.jpg/full/full/0/default.jpg',
                        'filename' => 'banner.jpg',
                    ],
                    'size' => [
                        'width' => 1800,
                        'height' => 900,
                    ],
                ],
                'thumbnail' => [
                    'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/thumbnail.jpg/full/full/0/default.jpg',
                        'filename' => 'thumbnail.jpg',
                    ],
                    'size' => [
                        'width' => 140,
                        'height' => 140,
                    ],
                ],
                'social' => [
                    'uri' => 'https://iiif.elifesciences.org/social.jpg',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/social.jpg/full/full/0/default.jpg',
                        'filename' => 'social.jpg',
                    ],
                    'size' => [
                        'width' => 600,
                        'height' => 600,
                    ],
                ],
            ],
        ];

        if (!$complete) {
            unset($subject['aimsAndScope']);
            unset($subject['image']['social']);
        }

        if ($isSnippet) {
            unset($subject['impactStatement']);
            unset($subject['aimsAndScope']);
            unset($subject['image']);
        }

        return $subject;
    }

    private function createReviewedPreprintJson($number, bool $isSnippet = false, bool $complete = false, $hasType = false) : array
    {
        if (is_int($number)) {
            $id = 'reviewed-preprint-'.$number;
        } else {
            $id = $number;
        }

        $reviewedPreprint = [
            'id' => $id,
            'doi' => '10.7554/eLife.'.$id,
            'status' => 'reviewed',
            'authorLine' => 'Lee R Berger, John Hawks ... Scott A Williams',
            'title' => 'reviewed preprint '.$number,
            'indexContent' => '<i>Homo naledi</i>, a new species of the genus <i>Homo</i> from the Dinaledi Chamber, South Africa',
            'titlePrefix' => 'Title prefix',
            'stage' => 'published',
            'published' => '2022-08-01T00:00:00Z',
            'reviewedDate' => '2022-08-01T00:00:00Z',
            'statusDate' => '2022-08-01T00:00:00Z',
            'volume' => 4,
            'elocationId' => 'e'.$id,
            'pdf' => 'https://elifesciences.org/content/4/e'.$id.'.pdf',
            'subjects' => [$this->createSubjectJson('1', true)],
            'curationLabels' => [
                0 => 'Ground-breaking',
                1 => 'Convincing',
            ],
            'image' => [
                'thumbnail' => [
                    'uri' => 'https://iiif.elifesciences.org/lax/'.$id.'%2Felife-'.$id.'-fig1-v1.tif',
                    'alt' => '',
                    'source' => [
                        'mediaType' => 'image/jpeg',
                        'uri' => 'https://iiif.elifesciences.org/lax/'.$id.'%2Felife-'.$id.'-fig1-v1.tif/full/full/0/default.jpg',
                        'filename' => 'an-image.jpg',
                    ],
                    'size' => [
                        'width' => 4194,
                        'height' => 4714,
                    ],
                    'focalPoint' => [
                        'x' => 25,
                        'y' => 75,
                    ],
                ],
            ],
        ];

        if (!$complete) {
            unset($reviewedPreprint['doi']);
            unset($reviewedPreprint['authorLine']);
            unset($reviewedPreprint['titlePrefix']);
            unset($reviewedPreprint['published']);
            unset($reviewedPreprint['statusDate']);
            unset($reviewedPreprint['reviewedDate']);
            unset($reviewedPreprint['volume']);
            unset($reviewedPreprint['elocationId']);
            unset($reviewedPreprint['pdf']);
            unset($reviewedPreprint['subjects']);
            unset($reviewedPreprint['curationLabels']);
            unset($reviewedPreprint['image']);
            unset($reviewedPreprint['indexContent']);
        }

        if ($isSnippet) {
            unset($reviewedPreprint['indexContent']);
        }

        if ($hasType) {
            $reviewedPreprint['type'] = 'reviewed-preprint';
        }

        return $reviewedPreprint;
    }
}
