<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class ArticleVoR extends ArticleVersion implements HasContent, HasImpactStatement, HasReferences, HasThumbnail
{
    private $figuresPdf;
    private $impactStatement;
    private $thumbnail;
    private $keywords;
    private $digest;
    private $content;
    private $appendices;
    private $references;
    private $acknowledgements;
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
        int $volume,
        string $elocationId,
        string $pdf = null,
        string $figuresPdf = null,
        PromiseInterface $xml,
        Sequence $subjects,
        array $researchOrganisms,
        ArticleSection $abstract = null,
        PromiseInterface $issue,
        PromiseInterface $copyright,
        Sequence $authors,
        Sequence $reviewers,
        string $impactStatement = null,
        Image $thumbnail = null,
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
        PromiseInterface $decisionLetter,
        Sequence $decisionLetterDescription,
        PromiseInterface $authorResponse
    ) {
        parent::__construct($id, $stage, $version, $type, $doi, $authorLine, $titlePrefix, $title, $published, $versionDate, $statusDate,
            $volume, $elocationId, $pdf, $xml, $subjects, $researchOrganisms, $abstract, $issue, $copyright, $authors, $reviewers,
            $ethics, $funding, $dataAvailability, $generatedDataSets, $usedDataSets, $additionalFiles);

        $this->figuresPdf = $figuresPdf;
        $this->impactStatement = $impactStatement;
        $this->thumbnail = $thumbnail;
        $this->keywords = $keywords;
        $this->digest = $digest;
        $this->content = $content;
        $this->appendices = $appendices;
        $this->references = $references;
        $this->acknowledgements = $acknowledgements;
        $this->decisionLetter = $decisionLetter;
        $this->decisionLetterDescription = $decisionLetterDescription;
        $this->authorResponse = $authorResponse;
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

    /**
     * @return Image|null
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
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
}
