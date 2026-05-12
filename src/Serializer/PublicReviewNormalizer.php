<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\PublicReview;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PublicReviewNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : PublicReview
    {
        $data['content'] = new ArraySequence(array_map(function (array $block) {
            return $this->denormalizer->denormalize($block, Block::class);
        }, $data['content']));

        return new PublicReview($data['title'], $data['content'], $data['doi'] ?? null, $data['id'] ?? null);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return PublicReview::class === $type;
    }

    /**
     * @param PublicReview $data
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'title' => $data->getTitle(),
            'content' => $data->getContent()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray(),
        ];

        if ($data->getDoi()) {
            $arr['doi'] = $data->getDoi();
        }

        if ($data->getId()) {
            $arr['id'] = $data->getId();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof PublicReview;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PublicReview::class => false,
        ];
    }
}
