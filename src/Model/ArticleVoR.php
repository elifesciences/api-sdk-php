<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

final class ArticleVoR extends ArticleVersion
{
    private $impactStatement;
    private $banner;
    private $thumbnail;
    private $keywords;
    private $digest;
    private $content;
    private $appendices;
    private $references;
    private $additionalFiles;
    private $generatedDataSets;
    private $usedDataSets;
    private $acknowledgements;
    private $ethics;
    private $decisionLetter;
    private $decisionLetterDescription;
    private $authorResponse;

    /**
     * @internal
     */
    public function __construct(
        string $id,
        int $version,
        string $type,
        string $doi,
        string $authorLine,
        string $titlePrefix = null,
        string $title,
        DateTimeImmutable $published,
        DateTimeImmutable $statusDate,
        int $volume,
        string $elocationId,
        string $pdf = null,
        Sequence $subjects,
        array $researchOrganisms,
        PromiseInterface $abstract,
        PromiseInterface $issue,
        PromiseInterface $copyright,
        Sequence $authors,
        string $impactStatement = null,
        PromiseInterface $banner,
        Image $thumbnail = null,
        Sequence $keywords,
        PromiseInterface $digest,
        Sequence $content,
        Sequence $appendices,
        Sequence $references,
        Sequence $additionalFiles,
        Sequence $generatedDataSets,
        Sequence $usedDataSets,
        Sequence $acknowledgements,
        Sequence $ethics,
        PromiseInterface $decisionLetter,
        Sequence $decisionLetterDescription,
        PromiseInterface $authorResponse
    ) {
        parent::__construct($id, $version, $type, $doi, $authorLine, $titlePrefix, $title, $published, $statusDate,
            $volume, $elocationId, $pdf, $subjects, $researchOrganisms, $abstract, $issue, $copyright, $authors);

        $this->impactStatement = $impactStatement;
        $this->banner = $banner;
        $this->thumbnail = $thumbnail;
        $this->keywords = $keywords;
        $this->digest = $digest;
        $this->content = $content;
        $this->appendices = $appendices;
        $this->references = $references;
        $this->additionalFiles = $additionalFiles;
        $this->generatedDataSets = $generatedDataSets;
        $this->usedDataSets = $usedDataSets;
        $this->acknowledgements = $acknowledgements;
        $this->ethics = $ethics;
        $this->decisionLetter = $decisionLetter;
        $this->decisionLetterDescription = $decisionLetterDescription;
        $this->authorResponse = $authorResponse;
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
    public function getBanner()
    {
        return $this->banner->wait();
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

    /**
     * @return Sequence|Reference[]
     */
    public function getReferences() : Sequence
    {
        return $this->references;
    }

    /**
     * @return Sequence|File[]
     */
    public function getAdditionalFiles() : Sequence
    {
        return $this->additionalFiles;
    }

    /**
     * @return Sequence|DataSet[]
     */
    public function getGeneratedDataSets() : Sequence
    {
        return $this->generatedDataSets;
    }

    /**
     * @return Sequence|DataSet[]
     */
    public function getUsedDataSets() : Sequence
    {
        return $this->usedDataSets;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getAcknowledgements() : Sequence
    {
        return $this->acknowledgements;
    }

    /**
     * @return Sequence|Block[]
     */
    public function getEthics() : Sequence
    {
        return $this->ethics;
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
