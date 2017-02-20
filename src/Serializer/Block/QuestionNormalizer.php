<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Question;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class QuestionNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Question
    {
        return new Question($data['question'], array_map(function (array $block) {
            return $this->denormalizer->denormalize($block, Block::class);
        }, $data['answer']));
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Question::class === $type
            ||
            (Block::class === $type && 'question' === $data['type']);
    }

    /**
     * @param Question $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'type' => 'question',
            'question' => $object->getQuestion(),
            'answer' => array_map(function (Block $block) {
                return $this->normalizer->normalize($block);
            }, $object->getAnswer()),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Question;
    }
}
