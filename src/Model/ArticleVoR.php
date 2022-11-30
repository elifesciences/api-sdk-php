<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class ArticleVoR extends ArticleVersion implements HasContent, HasImpactStatement, HasReferences, HasReviewedDate, HasCurationLabels
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

    /**
     * @internal
     */
    public function __construct(
        string $id,
        string $stage,
        int $version,
        string $type,
        string $doi,
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
        PromiseInterface $authorResponse
    ) {
        parent::__construct($id, $stage, $version, $type, $doi, $authorLine, $titlePrefix, $title, $published,
            $versionDate, $statusDate, $volume, $elocationId, $thumbnail, $socialImage, $pdf, $xml, $subjects,
            $researchOrganisms, $abstract, $issue, $copyright, $authors, $reviewers, $ethics, $funding,
            $dataAvailability, $generatedDataSets, $usedDataSets, $additionalFiles);

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

    public function hasReviewedPreprint() : bool
    {
        return false;
    }
}
