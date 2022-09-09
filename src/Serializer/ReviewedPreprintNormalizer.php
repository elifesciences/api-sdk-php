<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Image;
use eLife\ApiSdk\Model\ReviewedPreprint;
use eLife\ApiSdk\Model\Subject;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use DateTimeImmutable;

final class ReviewedPreprintNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{

    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []): ReviewedPreprint
    {
        if (!empty($data['published'])) {
            $data['published'] = DateTimeImmutable::createFromFormat(DATE_ATOM, $data['published']);
        }

        if (!empty($data['reviewedDate'])) {
            $data['reviewedDate'] = DateTimeImmutable::createFromFormat(DATE_ATOM, $data['reviewedDate']);
        }

        if (!empty($data['statusDate'])) {
            $data['statusDate'] = DateTimeImmutable::createFromFormat(DATE_ATOM, $data['statusDate']);
        }

        $data['subjects'] = new ArraySequence(array_map(function (array $subject) use ($format, $context) {
            $context['snippet'] = true;

            return $this->denormalizer->denormalize($subject, Subject::class, $format, $context);
        }, $data['subjects'] ?? []));


        $data['curationLabels'] = new ArraySequence($data['curationLabels'] ?? []);

        if (isset($data['image'])) {
            $data['image']['thumbnail'] = $this->denormalizer->denormalize($data['image']['thumbnail'], Image::class, $format, $context);
        }

        return new ReviewedPreprint(
            $data['id'],
            $data['title'],
            $data['status'],
            $data['stage'],
            $data['indexContent'] ?? null,
            $data['doi'] ?? null,
            $data['authorLine'] ?? null,
            $data['titlePrefix'] ?? null,
            $data['published'] ?? null,
            $data['reviewedDate'] ?? null,
            $data['statusDate'] ?? null,
            $data['volume'] ?? null,
            $data['elocationId'] ?? null,
            $data['pdf'] ?? null,
            $data['subjects'],
            $data['curationLabels'],
            $data['image']['thumbnail'] ?? null
        );
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return ReviewedPreprint::class === $type
            ||
            (isset($data['type']) && $data['type'] == 'reviewed-preprint');
    }

    /**
     * @param $object ReviewedPreprint
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        $data = [
            'id' => $object->getId(),
        ];

        if ($object->getTitle()) {
            $data['title'] = $object->getTitle();
        }

        if ($object->getStatus()) {
            $data['status'] = $object->getStatus();
        }

        if ($object->getStage()) {
            $data['stage'] = $object->getStage();
        }

        if ($object->getIndexContent()) {
            $data['indexContent'] = $object->getIndexContent();
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

        if (null != $object->getSubjects()) {
            $data['subjects'] = $object->getSubjects()->map(function (Subject $subject) use ($format, $context) {
                $context['snippet'] = true;

                return $this->normalizer->normalize($subject, $format, $context);
            })->toArray();
        }

        if (null != $object->getCurationLabels()) {
            $data['curationLabels'] = $object->getCurationLabels()->toArray();
        }

        if (null !== $object->getImage()) {
            $data['image']['thumbnail'] = $this->normalizer->normalize($object->getImage(), $format, $context);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ReviewedPreprint;
    }
}
