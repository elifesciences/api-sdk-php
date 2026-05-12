<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\ExternalArticle;
use eLife\ApiSdk\Model\Model;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ExternalArticleNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use NormalizerAwareTrait;
    use DenormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : ExternalArticle
    {
        return new ExternalArticle(
            $data['articleTitle'],
            $data['journal'],
            $data['authorLine'],
            $data['uri']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            ExternalArticle::class === $type ||
            (is_a($type, Model::class, true) && 'external-article' === ($data['type'] ?? 'unknown'));
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof ExternalArticle;
    }

    /**
     * @param ExternalArticle $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'articleTitle' => $data->getTitle(),
            'journal' => $data->getJournal(),
            'authorLine' => $data->getAuthorLine(),
            'uri' => $data->getUri(),
        ];

        if (!empty($context['type'])) {
            $arr['type'] = 'external-article';
        }

        return $arr;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ExternalArticle::class => false,
            Model::class => false,
        ];
    }
}
