<?php

namespace eLife\ApiSdk\Model;

use DateTimeImmutable;
use eLife\ApiSdk\Collection\Sequence;
use GuzzleHttp\Promise\PromiseInterface;

abstract class ArticleVersion implements Article, HasCiteAs, HasDoi, HasIdentifier, HasPdf, HasPublishedDate, HasSubjects, HasThumbnail, HasSocialImage
{
    const STAGE_PREVIEW = 'preview';
    const STAGE_PUBLISHED = 'published';

    private $id;
    private $stage;
    private $version;
    private $type;
    private $doi;
    private $authorLine;
    private $titlePrefix;
    private $title;
    private $published;
    private $versionDate;
    private $statusDate;
    private $volume;
    private $elocationId;
    private $thumbnail;
    private $socialImage;
    private $pdf;
    private $xml;
    private $subjects;
    private $researchOrganisms;
    private $abstract;
    private $issue;
    private $copyright;
    private $authors;
    private $reviewers;
    private $ethics;
    private $funding;
    private $dataAvailability;
    private $generatedDataSets;
    private $usedDataSets;
    private $additionalFiles;

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
        Image $thumbnail = null,
        Image $socialImage = null,
        string $pdf = null,
        PromiseInterface $xml,
        Sequence $subjects,
        array $researchOrganisms,
        ArticleSection $abstract = null,
        PromiseInterface $issue,
        PromiseInterface $copyright,
        Sequence $authors,
        Sequence $reviewers,
        Sequence $ethics,
        PromiseInterface $funding,
        Sequence $dataAvailability,
        Sequence $generatedDataSets,
        Sequence $usedDataSets,
        Sequence $additionalFiles
    ) {
        $this->id = $id;
        $this->stage = $stage;
        $this->version = $version;
        $this->type = $type;
        $this->doi = $doi;
        $this->authorLine = $authorLine;
        $this->titlePrefix = $titlePrefix;
        $this->title = $title;
        $this->published = $published;
        $this->versionDate = $versionDate;
        $this->statusDate = $statusDate;
        $this->volume = $volume;
        $this->elocationId = $elocationId;
        $this->thumbnail = $thumbnail;
        $this->socialImage = $socialImage;
        $this->pdf = $pdf;
        $this->xml = $xml;
        $this->subjects = $subjects;
        $this->researchOrganisms = $researchOrganisms;
        $this->abstract = $abstract;
        $this->issue = $issue;
        $this->copyright = $copyright;
        $this->authors = $authors;
        $this->reviewers = $reviewers;
        $this->ethics = $ethics;
        $this->funding = $funding;
        $this->dataAvailability = $dataAvailability;
        $this->generatedDataSets = $generatedDataSets;
        $this->usedDataSets = $usedDataSets;
        $this->additionalFiles = $additionalFiles;
    }

    final public function getIdentifier() : Identifier
    {
        return Identifier::article($this->id);
    }

    final public function getId() : string
    {
        return $this->id;
    }

    final public function getStage() : string
    {
        return $this->stage;
    }

    final public function getVersion() : int
    {
        return $this->version;
    }

    final public function getType() : string
    {
        return $this->type;
    }

    final public function getCiteAs()
    {
        if (null === $this->getPublishedDate()) {
            return null;
        }

        return sprintf('eLife %s;%s:%s', $this->getPublishedDate()->format('Y'), $this->getVolume(), $this->getElocationId());
    }

    final public function getDoi() : string
    {
        return $this->doi;
    }

    /**
     * @return string|null
     */
    final public function getAuthorLine()
    {
        return $this->authorLine;
    }

    /**
     * @return string|null
     */
    final public function getTitlePrefix()
    {
        return $this->titlePrefix;
    }

    final public function getTitle() : string
    {
        return $this->title;
    }

    final public function getFullTitle() : string
    {
        return implode(': ', array_filter([$this->titlePrefix, $this->title]));
    }

    final public function isPublished() : bool
    {
        return null !== $this->published;
    }

    /**
     * @return DateTimeImmutable|null
     */
    final public function getPublishedDate()
    {
        return $this->published;
    }

    /**
     * @return DateTimeImmutable|null
     */
    final public function getVersionDate()
    {
        return $this->versionDate;
    }

    /**
     * @return DateTimeImmutable|null
     */
    final public function getStatusDate()
    {
        return $this->statusDate;
    }

    final public function getVolume() : int
    {
        return $this->volume;
    }

    final public function getElocationId() : string
    {
        return $this->elocationId;
    }

    /**
     * @return Image|null
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @return Image|null
     */
    final public function getSocialImage()
    {
        return $this->socialImage;
    }

    /**
     * @return string|null
     */
    public function getPdf()
    {
        return $this->pdf;
    }

    /**
     * @return string|null
     */
    public function getXml()
    {
        return $this->xml->wait();
    }

    /**
     * @return Sequence|Subject[]
     */
    final public function getSubjects() : Sequence
    {
        return $this->subjects;
    }

    /**
     * @return string[]
     */
    final public function getResearchOrganisms() : array
    {
        return $this->researchOrganisms;
    }

    /**
     * @return ArticleSection|null
     */
    final public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * @return int|null
     */
    final public function getIssue()
    {
        return $this->issue->wait();
    }

    final public function getCopyright() : Copyright
    {
        return $this->copyright->wait();
    }

    final public function getAuthors() : Sequence
    {
        return $this->authors;
    }

    final public function getReviewers() : Sequence
    {
        return $this->reviewers;
    }

    /**
     * @return Sequence|Block[]
     */
    final public function getEthics() : Sequence
    {
        return $this->ethics;
    }

    /**
     * @return Funding|null
     */
    public function getFunding()
    {
        return $this->funding->wait();
    }

    /**
     * @return Sequence|Block[]
     */
    public function getDataAvailability() : Sequence
    {
        return $this->dataAvailability;
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
     * @return Sequence|AssetFile[]
     */
    public function getAdditionalFiles() : Sequence
    {
        return $this->additionalFiles;
    }

    public function hasReviewedPreprint() : bool
    {
        return false;
    }
}
