<?php

namespace eLife\ApiSdk;

use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\HttpClient;
use eLife\ApiSdk\Client\BlogArticles;
use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Serializer\Block;
use eLife\ApiSdk\Serializer\BlogArticleNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\SubjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class ApiSdk
{
    private $httpClient;
    private $serializer;
    private $blogArticles;
    private $subjects;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->serializer = new Serializer([
            $blogArticleNormalizer = new BlogArticleNormalizer(),
            new ImageNormalizer(),
            new SubjectNormalizer(),
            new Block\ImageNormalizer(),
            new Block\ParagraphNormalizer(),
            new Block\YouTubeNormalizer(),
        ]);

        $this->subjects = new Subjects(new SubjectsClient($this->httpClient), $this->serializer);

        $blogArticleNormalizer->setSubjects($this->subjects);
    }

    public function blogArticles() : BlogArticles
    {
        if (empty($this->blogArticles)) {
            $this->blogArticles = new BlogArticles(new BlogClient($this->httpClient), $this->serializer);
        }

        return $this->blogArticles;
    }

    public function subjects() : Subjects
    {
        return $this->subjects;
    }

    public function getSerializer() : Serializer
    {
        return $this->serializer;
    }
}
