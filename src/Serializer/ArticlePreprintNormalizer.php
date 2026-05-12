<?php

namespace eLife\ApiSdk\Serializer;

use DateTimeImmutable;
use eLife\ApiSdk\ApiSdk;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\ArticlePreprint;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ArticlePreprintNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : ArticlePreprint
    {
        return new ArticlePreprint(
            $data['description'],
            $data['uri'],
            DateTimeImmutable::createFromFormat(DATE_ATOM, $data['date'])
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return ArticlePreprint::class === $type
            ||
            is_a($type, Model::class, true) && 'preprint' === ($data['status'] ?? 'unknown');
    }

    /**
     * @param ArticlePreprint $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        return [
            'status' => 'preprint',
            'description' => $data->getDescription(),
            'uri' => $data->getUri(),
            'date' => $data->getPublishedDate()->format(ApiSdk::DATE_FORMAT),
        ];
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof ArticlePreprint;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ArticlePreprint::class => false,
            Model::class => false,
        ];
    }
}
