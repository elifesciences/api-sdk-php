<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\ElifeAssessment;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class ElifeAssessmentNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : ElifeAssessment
    {
        $title = $data['title'];
        $significance = $data['significance'] ?? null;
        $strength = $data['strength'] ?? null;
        $articleSection = new ArticleSection(
            new ArraySequence(
                array_map(
                    function (array $block) use ($format, $context) {
                        return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                    },
                    $data['content']
                )
            ),
            $data['doi'] ?? null,
            $data['id'] ?? null
        );
        return new ElifeAssessment($title, $articleSection, $significance, $strength);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return ElifeAssessment::class === $type;
    }


    /**
     * @param ElifeAssessment $data
     * @param $format
     * @param array $context
     * @return array
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [];
        $arr['title'] = $data->getTitle();
        $articleSection = $data->getArticleSection();
        $arr['content'] = $articleSection
            ->getContent()
            ->map(function (Block $block) use ($format, $context) {
                return $this->normalizer->normalize($block, $format, $context);
            })
            ->toArray();
        if ($articleSection->getDoi() !== null) {
            $arr['doi'] = $articleSection->getDoi();
        }
        if ($articleSection->getId() !== null) {
            $arr['id'] = $articleSection->getId();
        }
        if ($data->getSignificance() !== null) {
            $arr['significance'] = $data->getSignificance();
        }
        if ($data->getStrength() !== null) {
            $arr['strength'] = $data->getStrength();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof ElifeAssessment;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            ElifeAssessment::class => true,
        ];
    }
}
