<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\PatentReference;
use eLife\ApiSdk\Model\Reference\ReferenceDate;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PatentReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : PatentReference
    {
        return new PatentReference(
            $data['id'],
            ReferenceDate::fromString($data['date']),
            array_map(function (array $inventor) {
                return $this->denormalizer->denormalize($inventor, AuthorEntry::class);
            }, $data['inventors']),
            $data['inventorsEtAl'] ?? false,
            array_map(function (array $assignee) {
                return $this->denormalizer->denormalize($assignee, AuthorEntry::class);
            }, $data['assignees'] ?? []),
            $data['assigneesEtAl'] ?? false,
            $data['title'],
            $data['patentType'],
            $data['country'],
            $data['number'] ?? null,
            $data['uri'] ?? null
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            PatentReference::class === $type
            ||
            (Reference::class === $type && 'patent' === $data['type']);
    }

    /**
     * @param PatentReference $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'patent',
            'id' => $object->getId(),
            'date' => $object->getDate()->toString(),
            'inventors' => array_map(function (AuthorEntry $inventors) use ($format, $context) {
                return $this->normalizer->normalize($inventors, $format, $context);
            }, $object->getInventors()),
            'title' => $object->getTitle(),
            'patentType' => $object->getPatentType(),
            'country' => $object->getCountry(),
        ];

        if ($object->inventorsEtAl()) {
            $data['inventorsEtAl'] = $object->inventorsEtAl();
        }

        if ($object->getAssignees()) {
            $data['assignees'] = array_map(function (AuthorEntry $assignees) use ($format, $context) {
                return $this->normalizer->normalize($assignees, $format, $context);
            }, $object->getAssignees());
        }

        if ($object->assigneesEtAl()) {
            $data['assigneesEtAl'] = $object->assigneesEtAl();
        }

        if ($object->getNumber()) {
            $data['number'] = $object->getNumber();
        }

        if ($object->getUri()) {
            $data['uri'] = $object->getUri();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof PatentReference;
    }
}
