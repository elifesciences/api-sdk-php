<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\ExternalArticle;
use eLife\ApiSdk\Model\Model;
use eLife\ApiSdk\Model\Place;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ExternalArticleNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use NormalizerAwareTrait;
    use DenormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : ExternalArticle
    {
        return new ExternalArticle(
            $data['articleTitle'],
            $this->denormalizer->denormalize($data['journal'], Place::class, $format, $context),
            $data['authorLine'],
            $data['uri']
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            ExternalArticle::class === $type ||
            (is_a($type, Model::class, true) && 'external-article' === ($data['type'] ?? 'unknown'))
        ;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ExternalArticle;
    }

    /**
     * @param ExternalArticle $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'articleTitle' => $object->getArticleTitle(),
            'journal' => $this->normalizer->normalize($object->getJournal(), $format, $context),
            'authorLine' => $object->getAuthorLine(),
            'uri' => $object->getUri(),
        ];

        if (!empty($context['type'])) {
            $data['type'] = 'external-article';
        }

        return $data;
    }
}
