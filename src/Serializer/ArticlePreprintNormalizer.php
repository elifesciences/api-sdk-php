<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\ArticlePreprint;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ArticlePreprintNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : ArticlePreprint
    {
        return new ArticlePreprint(
            $data['description'],
            $data['uri'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['date'])
        );
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return ArticlePreprint::class === $type
            ||
            is_a($type, Model::class, true) && 'preprint' === ($data['status'] ?? 'unknown');
    }

    /**
     * @param ArticlePreprint $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'status' => 'preprint',
            'description' => $object->getDescription(),
            'uri' => $object->getUri(),
            'date' => $object->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
        ];

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ArticlePreprint;
    }
}
