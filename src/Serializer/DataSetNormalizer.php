<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\DataSet;
use eLife\ApiSdk\Model\Date;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DataSetNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : DataSet
    {
        return new DataSet(
            $data['id'],
            Date::fromString($data['date']),
            array_map(function (array $author) {
                return $this->denormalizer->denormalize($author, AuthorEntry::class);
            }, $data['authors'] ?? []),
            $data['authorsEtAl'] ?? false,
            $data['title'],
            $data['dataId'] ?? null,
            $data['details'] ?? null,
            $data['doi'] ?? null,
            $data['uri'] ?? null
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return DataSet::class === $type;
    }

    /**
     * @param DataSet $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'id' => $object->getId(),
            'date' => $object->getDate()->toString(),
            'authors' => array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, $context);
            }, $object->getAuthors()),
            'title' => $object->getTitle(),
        ];

        if ($object->authorsEtAl()) {
            $data['authorsEtAl'] = $object->authorsEtAl();
        }

        if ($object->getDataId()) {
            $data['dataId'] = $object->getDataId();
        }

        if ($object->getDetails()) {
            $data['details'] = $object->getDetails();
        }

        if ($object->getDoi()) {
            $data['doi'] = $object->getDoi();
        }

        if ($object->getUri()) {
            $data['uri'] = $object->getUri();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof DataSet;
    }
}
