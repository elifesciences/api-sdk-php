<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\ArticleSection;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\ElifeAssessment;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ElifeAssessmentNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : ElifeAssessment
    {
        $title = $data['title'];
        $significance = $data['significance'] ?? null;
        $strength = $data['strength'] ?? null;
        $elifeAssessmentArticleSection = new ArticleSection(
            new ArraySequence(
                array_map(
                    function (array $block) use ($format, $context) {
                        return $this->denormalizer->denormalize($block, Block::class, $format, $context);
                    },
                    $data['content'] ?? []
                )
            ),
            $data['doi'] ?? null,
            $data['id'] ?? null
        );
        return new ElifeAssessment($title, null, $significance, $strength);
    }

    public function supportsDenormalization($data, $type, $format = null) : bool
    {
        return ElifeAssessment::class === $type;
    }

    /**
     * @param ElifeAssessment $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [];
        $data['title'] = $object->getTitle();
        $articleSection = $object->getArticleSection();
        if ($articleSection !== null) {
            $data['content'] = $articleSection
                ->getContent()
                ->map(function (Block $block) use ($format, $context) {
                    return $this->normalizer->normalize($block, $format, $context);
                })
                ->toArray();
        }
        if ($object->getSignificance() !== null) {
            $data['significance'] = $object->getSignificance();
        }
        if ($object->getStrength() !== null) {
            $data['strength'] = $object->getStrength();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof ElifeAssessment;
    }
}
