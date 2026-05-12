<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiClient\ReviewedPreprintsClient;
use eLife\ApiClient\MediaType;
use eLife\ApiClient\Result;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Client\ReviewedPreprints;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\ApiSdk\Model\Subject;
use function GuzzleHttp\Promise\promise_for;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use DateTimeImmutable;
use eLife\ApiSdk\Model\ElifeAssessment;

final class ReviewedPreprintNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{

    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private SnippetDenormalizer $snippetDenormalizer;

    public function __construct(ReviewedPreprintsClient $reviewedPreprintsClient)
    {
        $this->snippetDenormalizer = new SnippetDenormalizer(
            function (array $article) : string {
                return $article['id'];
            },
            function (string $id) use ($reviewedPreprintsClient) : PromiseInterface {
                return $reviewedPreprintsClient->getReviewedPreprint(
                    ['Accept' => (string) new MediaType($reviewedPreprintsClient::TYPE_REVIEWED_PREPRINT, ReviewedPreprints::VERSION_REVIEWED_PREPRINT_LIST)],
                    $id
                );
            }
        );
    }

    public function denormalize($data, $type, $format = null, array $context = []): ReviewedPreprint
    {
        if (!empty($context['snippet'])) {
            $reviewedPreprint = $this->snippetDenormalizer->denormalizeSnippet($data);

            $data['indexContent'] = $reviewedPreprint
                ->then(function (Result $reviewedPreprint) {
                    return $reviewedPreprint['indexContent'] ?? null;
                });
        } else {
            $data['indexContent'] = promise_for($data['indexContent'] ?? null);
        }

        if (!empty($data['published'])) {
            $data['published'] = DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']);
        }

        if (!empty($data['reviewedDate'])) {
            $data['reviewedDate'] = DateTimeImmutable::createFromFormat(DATE_ATOM, $data['reviewedDate']);
        }

        if (!empty($data['versionDate'])) {
            $data['versionDate'] = DateTimeImmutable::createFromFormat(DATE_ATOM, $data['versionDate']);
        }

        if (!empty($data['statusDate'])) {
            $data['statusDate'] = DateTimeImmutable::createFromFormat(DATE_ATOM, $data['statusDate']);
        }

        $data['subjects'] = new ArraySequence(array_map(function (array $subject) use ($format, $context) {
            $context['snippet'] = true;

            return $this->denormalizer->denormalize($subject, Subject::class, $format, $context);
        }, $data['subjects'] ?? []));

        $data['curationLabels'] = $data['curationLabels'] ?? [];

        if (isset($data['image'])) {
            $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class, $format, $context);
        }

        if (isset($data['elifeAssessment'])) {
            $data['elifeAssessment'] = $this->denormalizer->denormalize($data['elifeAssessment'], ElifeAssessment::class, $format, $context);
        }

        return new ReviewedPreprint(
            $data['id'],
            $data['stage'],
            $data['doi'] ?? null,
            $data['authorLine'] ?? null,
            $data['titlePrefix'] ?? null,
            $data['title'],
            $data['published'] ?? null,
            $data['statusDate'] ?? null,
            $data['reviewedDate'] ?? null,
            $data['versionDate'] ?? null,
            $data['status'],
            $data['volume'] ?? null,
            $data['elocationId'] ?? null,
            $data['pdf'] ?? null,
            $data['subjects'],
            $data['curationLabels'],
            $data['image']['thumbnail'] ?? null,
            $data['indexContent'],
            $data['version'] ?? null,
            $data['elifeAssessment'] ?? null
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return ReviewedPreprint::class === $type
            ||
            Model::class === $type && 'reviewed-preprint' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param $data ReviewedPreprint
     */
    public function normalize($data, $format = null, array $context = []): array
    {
        $arr = [
            'id' => $data->getId(),
        ];

        if (!empty($context['type'])) {
            $arr['type'] = 'reviewed-preprint';
        }

        if ($data->getTitle()) {
            $arr['title'] = $data->getTitle();
        }

        if ($data->getStatus()) {
            $arr['status'] = $data->getStatus();
        }

        if ($data->getStage()) {
            $arr['stage'] = $data->getStage();
        }

        if ($data->getDoi()) {
            $arr['doi'] = $data->getDoi();
        }

        if ($data->getAuthorLine()) {
            $arr['authorLine'] = $data->getAuthorLine();
        }

        if ($data->getTitlePrefix()) {
            $arr['titlePrefix'] = $data->getTitlePrefix();
        }

        if ($data->getPublishedDate()) {
            $arr['published'] = $data->getPublishedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($data->getReviewedDate()) {
            $arr['reviewedDate'] = $data->getReviewedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($data->getVersionDate()) {
            $arr['versionDate'] = $data->getVersionDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($data->getStatusDate()) {
            $arr['statusDate'] = $data->getStatusDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($data->getVolume()) {
            $arr['volume'] = $data->getVolume();
        }

        if ($data->getElocationId()) {
            $arr['elocationId'] = $data->getElocationId();
        }

        if ($data->getPdf()) {
            $arr['pdf'] = $data->getPdf();
        }

        if (!$data->getSubjects()->isEmpty()) {
            $arr['subjects'] = $data->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($subject, $format, $context);
            })->toArray();
        }

        if (!empty($data->getCurationLabels())) {
            $arr['curationLabels'] = $data->getCurationLabels();
        }

        if (null !== $data->getThumbnail()) {
            $arr['image']['thumbnail'] = $this->normalizer->normalize($data->getThumbnail(), $format, $context);
        }

        if (empty($context['snippet'])) {
            if ($data->getIndexContent()) {
                $arr['indexContent'] = $data->getIndexContent();
            }
        }

        if ($data->getVersion()) {
            $arr['version'] = $data->getVersion();
        }

        if (null !== ($data->getElifeAssessment())) {
            $arr['elifeAssessment'] = $this->normalizer->normalize($data->getElifeAssessment(), $format, $context);
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return $data instanceof ReviewedPreprint;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ReviewedPreprint::class => false,
            Model::class => false,
        ];
    }
}
