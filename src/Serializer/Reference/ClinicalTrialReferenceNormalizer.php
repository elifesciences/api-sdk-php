<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\Date;
use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\ClinicalTrialReference;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ClinicalTrialReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : ClinicalTrialReference
    {
        return new ClinicalTrialReference(
            $data['id'],
            Date::fromString($data['date']),
            $data['discriminator'] ?? null,
            array_map(function (array $author) {
                return $this->denormalizer->denormalize($author, AuthorEntry::class);
            }, $data['authors']),
            $data['authorsEtAl'] ?? false,
            $data['authorsType'],
            $data['title'],
            $data['uri']
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            ClinicalTrialReference::class === $type
            ||
            (Reference::class === $type && 'clinical-trial' === $data['type']);
    }

    /**
     * @param ClinicalTrialReference $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'clinical-trial',
            'id' => $object->getId(),
            'date' => $object->getDate()->toString(),
            'authors' => array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, ['type' => true] + $context);
            }, $object->getAuthors()),
            'authorsType' => $object->getAuthorsType(),
            'title' => $object->getTitle(),
            'uri' => $object->getUri(),
        ];

        if ($object->getDiscriminator()) {
            $data['discriminator'] = $object->getDiscriminator();
        }

        if ($object->authorsEtAl()) {
            $data['authorsEtAl'] = $object->authorsEtAl();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ClinicalTrialReference;
    }
}
