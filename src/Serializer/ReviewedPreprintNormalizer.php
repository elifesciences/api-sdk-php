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
use DateTimeImmutable;

final class ReviewedPreprintNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{

    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    private $snippetDenormalizer;

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

    public function denormalize($data, $class, $format = null, array $context = []): ReviewedPreprint
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
            $data['version'] ?? null
        );
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return ReviewedPreprint::class === $type
            ||
            Model::class === $type && 'reviewed-preprint' === ($data['type'] ?? 'unknown');
    }

    /**
     * @param $object ReviewedPreprint
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = [
            'id' => $object->getId(),
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'reviewed-preprint';
        }

        if ($object->getTitle()) {
            $data['title'] = $object->getTitle();
        }

        if ($object->getStatus()) {
            $data['status'] = $object->getStatus();
        }

        if ($object->getStage()) {
            $data['stage'] = $object->getStage();
        }

        if ($object->getDoi()) {
            $data['doi'] = $object->getDoi();
        }

        if ($object->getAuthorLine()) {
            $data['authorLine'] = $object->getAuthorLine();
        }

        if ($object->getTitlePrefix()) {
            $data['titlePrefix'] = $object->getTitlePrefix();
        }

        if ($object->getPublishedDate()) {
            $data['published'] = $object->getPublishedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getReviewedDate()) {
            $data['reviewedDate'] = $object->getReviewedDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getVersionDate()) {
            $data['versionDate'] = $object->getVersionDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getStatusDate()) {
            $data['statusDate'] = $object->getStatusDate()->format(ApiSdk::DATE_FORMAT);
        }

        if ($object->getVolume()) {
            $data['volume'] = $object->getVolume();
        }

        if ($object->getElocationId()) {
            $data['elocationId'] = $object->getElocationId();
        }

        if ($object->getPdf()) {
            $data['pdf'] = $object->getPdf();
        }

        if (!$object->getSubjects()->isEmpty()) {
            $data['subjects'] = $object->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($subject, $format, $context);
            })->toArray();
        }

        if (!empty($object->getCurationLabels())) {
            $data['curationLabels'] = $object->getCurationLabels();
        }

        if (null !== $object->getThumbnail()) {
            $data['image']['thumbnail'] = $this->normalizer->normalize($object->getThumbnail(), $format, $context);
        }

        if (empty($context['snippet'])) {
            if ($object->getIndexContent()) {
                $data['indexContent'] = $object->getIndexContent();
            }
        }

        if ($object->getVersion()) {
            $data['version'] = $object->getVersion();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ReviewedPreprint;
    }
}
