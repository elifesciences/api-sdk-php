<?php

namespace eLife\ApiSdk;

use eLife\ApiClient\ApiClient\AnnualReportsClient;
use eLife\ApiClient\ApiClient\BlogClient;
use eLife\ApiClient\ApiClient\InterviewsClient;
use eLife\ApiClient\ApiClient\LabsClient;
use eLife\ApiClient\ApiClient\MediumClient;
use eLife\ApiClient\ApiClient\SubjectsClient;
use eLife\ApiClient\HttpClient;
use eLife\ApiSdk\Client\AnnualReports;
use eLife\ApiSdk\Client\BlogArticles;
use eLife\ApiSdk\Client\Interviews;
use eLife\ApiSdk\Client\LabsExperiments;
use eLife\ApiSdk\Client\MediumArticles;
use eLife\ApiSdk\Client\Subjects;
use eLife\ApiSdk\Serializer\AnnualReportNormalizer;
use eLife\ApiSdk\Serializer\Block;
use eLife\ApiSdk\Serializer\BlogArticleNormalizer;
use eLife\ApiSdk\Serializer\ImageNormalizer;
use eLife\ApiSdk\Serializer\InterviewNormalizer;
use eLife\ApiSdk\Serializer\LabsExperimentNormalizer;
use eLife\ApiSdk\Serializer\MediumArticleNormalizer;
use eLife\ApiSdk\Serializer\PersonNormalizer;
use eLife\ApiSdk\Serializer\SubjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class ApiSdk
{
    private $httpClient;
    private $serializer;
    private $annualReports;
    private $blogArticles;
    private $interviews;
    private $labsExperiments;
    private $mediumArticles;
    private $subjects;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->serializer = new Serializer([
            new AnnualReportNormalizer(),
            $blogArticleNormalizer = new BlogArticleNormalizer(),
            new ImageNormalizer(),
            new InterviewNormalizer(),
            new LabsExperimentNormalizer(),
            new MediumArticleNormalizer(),
            new PersonNormalizer(),
            new SubjectNormalizer(),
            new Block\BoxNormalizer(),
            new Block\FileNormalizer(),
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
        ]);

        $this->subjects = new Subjects(new SubjectsClient($this->httpClient), $this->serializer);

        $blogArticleNormalizer->setSubjects($this->subjects);
    }

    public function annualReports() : AnnualReports
    {
        if (empty($this->annualReports)) {
            $this->annualReports = new AnnualReports(new AnnualReportsClient($this->httpClient), $this->serializer);
        }

        return $this->annualReports;
    }

    public function blogArticles() : BlogArticles
    {
        if (empty($this->blogArticles)) {
            $this->blogArticles = new BlogArticles(new BlogClient($this->httpClient), $this->serializer);
        }

        return $this->blogArticles;
    }

    public function interviews() : Interviews
    {
        if (empty($this->interviews)) {
            $this->interviews = new Interviews(new InterviewsClient($this->httpClient), $this->serializer);
        }

        return $this->interviews;
    }

    public function labsExperiments() : LabsExperiments
    {
        if (empty($this->labsExperiments)) {
            $this->labsExperiments = new LabsExperiments(new LabsClient($this->httpClient), $this->serializer);
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

    public function subjects() : Subjects
    {
        return $this->subjects;
    }

    public function getSerializer() : Serializer
    {
        return $this->serializer;
    }
}
