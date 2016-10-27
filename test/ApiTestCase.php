<?php

namespace test\eLife\ApiSdk;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\MockMiddleware;
use eLife\ApiClient\ApiClient\AnnualReportsClient;
use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\ApiClient\CollectionsClient;
use eLife\ApiClient\ApiClient\EventsClient;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\ApiClient\MediumClient;
use eLife\ApiClient\ApiClient\PeopleClient;
use eLife\ApiClient\ApiClient\PodcastClient;
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

    final protected function mockArticleCall($numberOrId, bool $complete = false, bool $vor = false)
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
                json_encode($this->createArticleVoRJson($id, false, $complete))
            );
        } else {
            $response = new Response(
                200,
                ['Content-Type' => new MediaType(ArticlesClient::TYPE_ARTICLE_POA, 1)],
                json_encode($this->createArticlePoAJson($id, false, $complete))
            );
        }

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/'.$id,
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

    final protected function mockBlogArticleListCall(
        int $page,
        int $perPage,
        int $total,
        $descendingOrder = true,
        array $subjects = []
    ) {
        $blogArticles = array_map(function (int $id) {
            return $this->createBlogArticleJson('blogArticle'.$id, true);
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
            $id = "blogArticle{$numberOrId}";
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
     * @param string|integer $numberOrId
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
     * @param string|integer $numberOrId
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

    private function createArticlePoAJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $article = [
            'status' => 'poa',
            'id' => $id,
            'version' => 1,
            'type' => 'research-article',
            'doi' => '10.7554/eLife.'.$id,
            'title' => 'Article '.$id.' title',
            'titlePrefix' => 'Article '.$id.' title prefix',
            'published' => '2000-01-01T00:00:00+00:00',
            'statusDate' => '1999-12-31T00:00:00+00:00',
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
            'abstract' => [
                'content' => [
                    [
                        'type' => 'paragraph',
                        'text' => 'Article '.$id.' abstract text',
                    ],
                ],
            ],
        ];

        if (!$complete) {
            unset($article['titlePrefix']);
            unset($article['issue']);
            unset($article['pdf']);
            unset($article['subjects']);
            unset($article['researchOrganisms']);
            unset($article['abstract']);
        }

        if ($isSnippet) {
            unset($article['issue']);
            unset($article['copyright']);
            unset($article['authors']);
            unset($article['abstract']);
        }

        return $article;
    }

    private function createArticleVoRJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $article = $this->createArticlePoAJson($id, $isSnippet, $complete);

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
            unset($article['references']);
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
            unset($article['references']);
            unset($article['decisionLetter']);
            unset($article['authorResponse']);
        }

        return $article;
    }

    private function createBlogArticleJson(string $id, bool $isSnippet = false, bool $complete = false) : array
    {
        $blogArticle = [
            'id' => $id,
            'title' => 'Blog article '.$id.' title',
            'published' => '2000-01-01T00:00:00+00:00',
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

    private function createEventJson(int $number, bool $isSnippet = false, bool $complete = false) : array
    {
        $event = [
            'id' => 'event'.$number,
            'title' => 'Event '.$number.' title',
            'impactStatement' => 'Event '.$number.' impact statement',
            'starts' => '2000-01-01T00:00:00+00:00',
            'ends' => '2100-01-01T00:00:00+00:00',
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
            'published' => '2000-01-01T00:00:00+00:00',
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
            'published' => '2000-01-01T00:00:00+00:00',
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
            'published' => '2000-01-01T00:00:00+00:00',
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
            'published' => '2000-01-01T00:00:00+00:00',
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
        }

        if ($isSnippet) {
            unset($podcastEpisode['image']['banner']);
            unset($podcastEpisode['content']);
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
            'updated' => '2000-01-01T00:00:00+00:00',
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
            /*
            'sources' => [
                [
                    'mediaType' => 'audio/mpeg',
                    'uri' => 'https://www.example.com/episode.mp3',
                ],
            ],
             */
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
                    'impactStatement' => "In their research paper – Cyanobacteria use micro-optics to sense light direction – Schuergers et al. reveal how bacterial cells act as the equivalent of a microscopic eyeball or the world’s oldest and smallest camera eye, allowing them to ‘see’.",
                    'published' => '2016-07-08T08:33:25+00:00',
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
                    'id' => '14107',
                    'version' => 1,
                    'doi' => '10.7554/eLife.14107',
                    'authorLine' => 'Yongjian Huang et al',
                    'title' => 'Molecular basis for multimerization in the activation of the epidermal growth factor',
                    'published' => '2016-03-28T00:00:00+00:00',
                    'statusDate' => '2016-03-28T00:00:00+00:00',
                    'volume' => 5,
                    'elocationId' => 'e14107',
                ]
            ],
            'podcastEpisodes' => [
            ],
            'subjects' => [$this->createSubjectJson(1, true)],
        ];

        if (!$complete) {
            unset($collection['impactStatement']);
            unset($collection['subjects']);
            unset($collection['relatedContent']);
        }

        if ($isSnippet) {
            unset($collection['image']['banner']);
            unset($collection['content']);
            unset($collection['relatedContent']);
        }

        return $collection;
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
