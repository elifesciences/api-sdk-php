<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\PatentReference;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PatentReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : PatentReference
    {
        return new PatentReference(
            $data['id'],
            Date::fromString($data['date']),
            $data['discriminator'] ?? null,
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

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            PatentReference::class === $type
            ||
            (Reference::class === $type && 'patent' === $data['type']);
    }

    /**
     * @param PatentReference $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'patent',
            'id' => $data->getId(),
            'date' => $data->getDate()->toString(),
            'inventors' => array_map(function (AuthorEntry $inventors) use ($format, $context) {
                return $this->normalizer->normalize($inventors, $format, ['type' => true] + $context);
            }, $data->getInventors()),
            'title' => $data->getTitle(),
            'patentType' => $data->getPatentType(),
            'country' => $data->getCountry(),
        ];

        if ($data->getDiscriminator()) {
            $arr['discriminator'] = $data->getDiscriminator();
        }

        if ($data->inventorsEtAl()) {
            $arr['inventorsEtAl'] = $data->inventorsEtAl();
        }

        if ($data->getAssignees()) {
            $arr['assignees'] = array_map(function (AuthorEntry $assignees) use ($format, $context) {
                return $this->normalizer->normalize($assignees, $format, ['type' => true] + $context);
            }, $data->getAssignees());
        }

        if ($data->assigneesEtAl()) {
            $arr['assigneesEtAl'] = $data->assigneesEtAl();
        }

        if ($data->getNumber()) {
            $arr['number'] = $data->getNumber();
        }

        if ($data->getUri()) {
            $arr['uri'] = $data->getUri();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof PatentReference;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PatentReference::class => false,
            Reference::class => false,
        ];
    }
}
