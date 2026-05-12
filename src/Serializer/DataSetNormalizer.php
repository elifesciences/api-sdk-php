<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\AuthorEntry;
use eLife\ApiSdk\Model\DataSet;
use eLife\ApiSdk\Model\Date;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class DataSetNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : DataSet
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

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return DataSet::class === $type;
    }


    /**
     * @param DataSet $data
     * @param $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'id' => $data->getId(),
            'date' => $data->getDate()->toString(),
            'authors' => array_map(function (AuthorEntry $author) use ($format, $context) {
                return $this->normalizer->normalize($author, $format, ['type' => true] + $context);
            }, $data->getAuthors()),
            'title' => $data->getTitle(),
        ];

        if ($data->authorsEtAl()) {
            $arr['authorsEtAl'] = $data->authorsEtAl();
        }

        if ($data->getDataId()) {
            $arr['dataId'] = $data->getDataId();
        }

        if ($data->getDetails()) {
            $arr['details'] = $data->getDetails();
        }

        if ($data->getDoi()) {
            $arr['doi'] = $data->getDoi();
        }

        if ($data->getUri()) {
            $arr['uri'] = $data->getUri();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof DataSet;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            DataSet::class => false,
        ];
    }
}
