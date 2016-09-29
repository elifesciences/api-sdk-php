<?php

namespace test\eLife\ApiSdk;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\MockMiddleware;
use eLife\ApiClient\ApiClient\AnnualReportsClient;
use eLife\ApiClient\ApiClient\ArticlesClient;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\ApiClient\EventsClient;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\ApiClient\MediumClient;
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
        $articles = array_map(function (int $id) {
            return $this->createArticleVoRJson($id, true);
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

    final protected function mockArticleCall(int $number, bool $subject = false, bool $vor = false)
    {
        if ($vor) {
            $response = new Response(
                200,
                ['Content-Type' => new MediaType(ArticlesClient::TYPE_ARTICLE_VOR, 1)],
                json_encode($this->createArticleVoRJson($number, false, $subject))
            );
        } else {
            $response = new Response(
                200,
                ['Content-Type' => new MediaType(ArticlesClient::TYPE_ARTICLE_POA, 1)],
                json_encode($this->createArticlePoAJson($number, false, $subject))
            );
        }

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/articles/article'.$number,
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
            return $this->createBlogArticleJson($id, true);
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

    final protected function mockBlogArticleCall(int $number, bool $subject = false)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/blog-articles/blogArticle'.$number,
                ['Accept' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(BlogClient::TYPE_BLOG_ARTICLE, 1)],
                json_encode($this->createBlogArticleJson($number, false, $subject))
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

    final protected function mockEventCall(int $number, bool $venue = false)
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
                json_encode($this->createEventJson($number, false, $venue))
            )
        );
    }

    final protected function mockInterviewListCall(int $page, int $perPage, int $total, $descendingOrder = true)
    {
        $interviews = array_map(function (int $id) {
            return $this->createInterviewJson($id, true);
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

    final protected function mockInterviewCall(int $number)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/interviews/interview'.$number,
                ['Accept' => new MediaType(InterviewsClient::TYPE_INTERVIEW, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(InterviewsClient::TYPE_INTERVIEW, 1)],
                json_encode($this->createInterviewJson($number, false))
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

    final protected function mockLabsExperimentCall(int $number)
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
                json_encode($this->createLabsExperimentJson($number, false))
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

    final protected function mockSubjectListCall(int $page, int $perPage, int $total, $descendingOrder = true)
    {
        $subjects = array_map(function (int $id) {
            return $this->createSubjectJson($id);
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

    final protected function mockSubjectCall(int $number)
    {
        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects/subject'.$number,
                ['Accept' => new MediaType(SubjectsClient::TYPE_SUBJECT, 1)]
            ),
            new Response(
                200,
                ['Content-Type' => new MediaType(SubjectsClient::TYPE_SUBJECT, 1)],
                json_encode($this->createSubjectJson($number))
            )
        );
    }

    private function generateIdList(int $page, int $perPage, int $total) : array
    {
        $firstId = ($page * $perPage) - $perPage + 1;
        if ($firstId > $total) {
            throw new LogicException('Page should not exist');
        }

        return range($firstId, $firstId + $perPage - 1);
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
                    '2:1' => [
                        '900' => 'https://placehold.it/900x450',
                        '1800' => 'https://placehold.it/1800x900',
                    ],
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

    private function createArticlePoAJson(int $number, bool $isSnippet = false, bool $subject = false) : array
    {
        $article = [
            'status' => 'poa',
            'id' => 'article'.$number,
            'version' => 1,
            'type' => 'research-article',
            'doi' => '10.7554/eLife.'.$number,
            'title' => 'Article '.$number.' title',
            'published' => '2000-01-01T00:00:00+00:00',
            'volume' => 1,
            'elocationId' => 'e'.$number,
            'copyright' => [
                'license' => 'CC-BY-4.0',
                'holder' => 'Author et al',
                'statement' => 'This article is distributed under the terms of the <a href=\'http://creativecommons.org/licenses/by/4.0/\'>Creative Commons Attribution License</a> permitting unrestricted use and redistribution provided that the original author and source are credited.',
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
        ];

        if ($subject) {
            $article['subjects'][] = 'subject1';
        }

        if ($isSnippet) {
            unset($article['copyright']);
            unset($article['authors']);
        }

        return $article;
    }

    private function createArticleVoRJson(int $number, bool $isSnippet = false, bool $subject = false) : array
    {
        $article = [
            'status' => 'vor',
            'id' => 'article'.$number,
            'version' => 1,
            'type' => 'research-article',
            'doi' => '10.7554/eLife.'.$number,
            'title' => 'Article '.$number.' title',
            'published' => '2000-01-01T00:00:00+00:00',
            'volume' => 1,
            'elocationId' => 'e'.$number,
            'copyright' => [
                'license' => 'CC-BY-4.0',
                'holder' => 'Author et al',
                'statement' => 'This article is distributed under the terms of the <a href=\'http://creativecommons.org/licenses/by/4.0/\'>Creative Commons Attribution License</a> permitting unrestricted use and redistribution provided that the original author and source are credited.',
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
            'body' => [
                [
                    'type' => 'section',
                    'title' => 'Article '.$number.' section title',
                    'id' => 'article'.$number.'section',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Article '.$number.' text',
                        ],
                    ],
                ],
            ],
        ];

        if ($subject) {
            $article['subjects'][] = 'subject1';
        }

        if ($isSnippet) {
            unset($article['copyright']);
            unset($article['authors']);
            unset($article['content']);
        }

        return $article;
    }

    private function createBlogArticleJson(int $number, bool $isSnippet = false, bool $subject = false) : array
    {
        $blogArticle = [
            'id' => 'blogArticle'.$number,
            'title' => 'Blog article '.$number.' title',
            'impactStatement' => 'Blog article '.$number.' impact statement',
            'published' => '2000-01-01T00:00:00+00:00',
            'subjects' => [],
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Blog article '.$number.' text',
                ],
            ],
        ];

        if ($subject) {
            $blogArticle['subjects'][] = 'subject1';
        }

        if ($isSnippet) {
            unset($blogArticle['content']);
        }

        return $blogArticle;
    }

    private function createEventJson(int $number, bool $isSnippet = false, bool $venue = false) : array
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

        if ($venue) {
            $event['timezone'] = 'Europe/London';
            $event['venue'] = ['name' => 'venue'];
        }

        if ($isSnippet) {
            unset($event['content']);
            unset($event['venue']);
        }

        return $event;
    }

    private function createInterviewJson(int $number, bool $isSnippet = false) : array
    {
        $interview = [
            'id' => 'interview'.$number,
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
            'title' => 'Interview '.$number.' title',
            'impactStatement' => 'Interview '.$number.' impact statement',
            'published' => '2000-01-01T00:00:00+00:00',
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Interview '.$number.' text',
                ],
            ],
        ];

        if ($isSnippet) {
            unset($interview['content']);
        }

        return $interview;
    }

    private function createLabsExperimentJson(int $number, bool $isSnippet = false) : array
    {
        $labsExperiment = [
            'number' => $number,
            'title' => 'Labs experiment '.$number.' title',
            'impactStatement' => 'Labs experiment '.$number.' impact statement',
            'published' => '2000-01-01T00:00:00+00:00',
            'image' => [
                'alt' => '',
                'sizes' => [
                    '2:1' => [
                        '900' => 'https://placehold.it/900x450',
                        '1800' => 'https://placehold.it/1800x900',
                    ],
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
            'content' => [
                [
                    'type' => 'paragraph',
                    'text' => 'Labs experiment '.$number.' text',
                ],
            ],
        ];

        if ($isSnippet) {
            unset($labsExperiment['content']);
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
                    '2:1' => [
                        '900' => 'https://placehold.it/900x450',
                        '1800' => 'https://placehold.it/1800x900',
                    ],
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

    final private function createSubjectJson(int $number)
    {
        return [
            'id' => 'subject'.$number,
            'name' => 'Subject '.$number.' name',
            'impactStatement' => 'Subject '.$number.' impact statement',
            'image' => [
                'alt' => '',
                'sizes' => [
                    '2:1' => [
                        '900' => 'https://placehold.it/900x450',
                        '1800' => 'https://placehold.it/1800x900',
                    ],
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
}
