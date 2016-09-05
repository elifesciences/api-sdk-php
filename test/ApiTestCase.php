<?php

namespace test\eLife\ApiSdk;

use Csa\Bundle\GuzzleBundle\GuzzleHttp\Middleware\MockMiddleware;
use eLife\ApiClient\ApiClient\BlogClient;
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
use PHPUnit_Framework_TestCase;
use Webmozart\Json\JsonDecoder;

abstract class ApiTestCase extends PHPUnit_Framework_TestCase
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

    final protected function mockBlogArticleListCall(int $page, int $perPage, int $total)
    {
        $blogArticles = array_map(function (int $id) {
            return $this->createBlogArticleJson($id, true);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/blog-articles?page='.$page.'&per-page='.$perPage.'&order=desc',
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

    final protected function mockSubjectListCall(int $page, int $perPage, int $total)
    {
        $subjects = array_map(function (int $id) {
            return $this->createSubjectJson($id);
        }, $this->generateIdList($page, $perPage, $total));

        $this->storage->save(
            new Request(
                'GET',
                'http://api.elifesciences.org/subjects?page='.$page.'&per-page='.$perPage.'&order=desc',
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
