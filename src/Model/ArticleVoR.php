<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class ArticleVoR extends ArticleVersion implements HasContent, HasImpactStatement, HasReferences, HasReviewedDate, HasCurationLabels, IsReviewedPreprint
{
    private $reviewedDate;
    private $figuresPdf;
    private $curationLabels;
    private $impactStatement;
    private $keywords;
    private $digest;
    private $content;
    private $appendices;
    private $references;
    private $acknowledgements;
    private $editorEvaluation;
    private $editorEvaluationScietyUri;
    private $decisionLetter;
    private $decisionLetterDescription;
    private $authorResponse;
    private $elifeAssessmentArticleSection;
    private $elifeAssessmentTitle;
    private $elifeAssessmentScietyUri;
    private $recommendationsForAuthors;
    private $recommendationsForAuthorsTitle;
    private $doiVersion;
    private $publicReviews;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $stage,
        int $version,
        string $type,
        string $doi,
        string $doiVersion = null,
        string $authorLine = null,
        string $titlePrefix = null,
        string $title,
        DateTimeImmutable $published = null,
        DateTimeImmutable $versionDate = null,
        DateTimeImmutable $statusDate = null,
        DateTimeImmutable $reviewedDate = null,
        int $volume,
        string $elocationId,
        Image $thumbnail = null,
        Image $socialImage = null,
        string $pdf = null,
        string $figuresPdf = null,
        PromiseInterface $xml,
        Sequence $subjects,
        array $curationLabels,
        array $researchOrganisms,
        ArticleSection $abstract = null,
        PromiseInterface $issue,
        PromiseInterface $copyright,
        Sequence $authors,
        Sequence $reviewers,
        string $impactStatement = null,
        Sequence $keywords,
        PromiseInterface $digest,
        Sequence $content,
        Sequence $appendices,
        Sequence $references,
        Sequence $additionalFiles,
        Sequence $dataAvailability,
        Sequence $generatedDataSets,
        Sequence $usedDataSets,
        Sequence $acknowledgements,
        Sequence $ethics,
        PromiseInterface $funding,
        PromiseInterface $editorEvaluation,
        PromiseInterface $editorEvaluationScietyUri,
        PromiseInterface $decisionLetter,
        Sequence $decisionLetterDescription,
        PromiseInterface $authorResponse,
        ArticleSection $elifeAssessmentArticleSection = null,
        string $elifeAssessmentTitle = null,
        string $elifeAssessmentScietyUri = null,
        PromiseInterface $recommendationsForAuthors = null,
        PromiseInterface $recommendationsForAuthorsTitle = null,
        Sequence $publicReviews = null,
        ElifeAssessment $elifeAssessment = null
    ) {
        parent::__construct($id, $stage, $version, $type, $doi, $authorLine, $titlePrefix, $title, $published,
            $versionDate, $statusDate, $volume, $elocationId, $thumbnail, $socialImage, $pdf, $xml, $subjects,
            $researchOrganisms, $abstract, $issue, $copyright, $authors, $reviewers, $ethics, $funding,
            $dataAvailability, $generatedDataSets, $usedDataSets, $additionalFiles, $elifeAssessment);

        $this->reviewedDate = $reviewedDate;
        $this->curationLabels = $curationLabels;
        $this->figuresPdf = $figuresPdf;
        $this->impactStatement = $impactStatement;
        $this->keywords = $keywords;
        $this->digest = $digest;
        $this->content = $content;
        $this->appendices = $appendices;
        $this->references = $references;
        $this->acknowledgements = $acknowledgements;
        $this->editorEvaluation = $editorEvaluation;
        $this->editorEvaluationScietyUri = $editorEvaluationScietyUri;
        $this->decisionLetter = $decisionLetter;
        $this->decisionLetterDescription = $decisionLetterDescription;
        $this->authorResponse = $authorResponse;
        $this->elifeAssessmentArticleSection = $elifeAssessmentArticleSection;
        $this->elifeAssessmentTitle = $elifeAssessmentTitle;
        $this->elifeAssessmentScietyUri = $elifeAssessmentScietyUri;
        $this->recommendationsForAuthors = $recommendationsForAuthors;
        $this->recommendationsForAuthorsTitle = $recommendationsForAuthorsTitle;
        $this->doiVersion = $doiVersion;
        $this->publicReviews = $publicReviews;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getReviewedDate()
    {
        return $this->reviewedDate;
    }

    /**
     * @return string[]
     */
    public function getCurationLabels() : array
    {
        return $this->curationLabels;
    }

    /**
     * @return string|null
     */
    public function getFiguresPdf()
    {
        return $this->figuresPdf;
    }

    /**
     * @return string|null
     */
    public function getImpactStatement()
    {
        return $this->impactStatement;
    }

    public function getKeywords() : Sequence
    {
        return $this->keywords;
    }

    /**
     * @return ArticleSection|null
     */
    public function getDigest()
    {
        return $this->digest->wait();
    }

    public function getContent() : Sequence
    {
        return $this->content;
    }

    /**
     * @return Sequence|Appendix[]
     */
    public function getAppendices() : Sequence
    {
        return $this->appendices;
    }

    public function getReferences() : Sequence
    {
        return $this->references;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getAcknowledgements() : Sequence
    {
        return $this->acknowledgements;
    }

    /**
     * @return ArticleSection|null
     */
    public function getEditorEvaluation()
    {
        return $this->editorEvaluation->wait();
    }

    /**
     * @return string|null
     */
    public function getEditorEvaluationScietyUri()
    {
        return $this->editorEvaluationScietyUri->wait();
    }

    /**
     * @return ArticleSection|null
     */
    public function getDecisionLetter()
    {
        return $this->decisionLetter->wait();
    }

    /**
     * @return Sequence|Block[]
     */
    public function getDecisionLetterDescription() : Sequence
    {
        return $this->decisionLetterDescription;
    }

    /**
     * @return ArticleSection|null
     */
    public function getAuthorResponse()
    {
        return $this->authorResponse->wait();
    }

    /**
     * @return ArticleSection|null
     */
    public function getElifeAssessmentArticleSection()
    {
        return $this->elifeAssessmentArticleSection;
    }

    /**
     * @return string|null
     */
    public function getElifeAssessmentTitle()
    {
        return $this->elifeAssessmentTitle;
    }

    /**
     * @return string|null
     */
    public function getElifeAssessmentScietyUri()
    {
        return $this->elifeAssessmentScietyUri;
    }

    /**
     * @return ArticleSection|null
     */
    public function getRecommendationsForAuthors()
    {
        return $this->recommendationsForAuthors->wait();
    }

    /**
     * @return string|null
     */
    public function getRecommendationsForAuthorsTitle()
    {
        return $this->recommendationsForAuthorsTitle->wait();
    }

    /**
     * @return string|null
     */
    public function getDoiVersion()
    {
        return $this->doiVersion;
    }

    /**
     * @return Sequence|PublicReview[]
     */
    public function getPublicReviews() : Sequence
    {
        return $this->publicReviews;
    }

    public function isReviewedPreprint() : bool
    {
        return strpos($this->getElocationId(), 'RP') !== false;
    }
}
