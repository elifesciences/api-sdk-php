<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\File;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

final class AssetFileNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : AssetFile
    {
        return new AssetFile($data['doi'] ?? null, $data['id'], $data['label'], $data['title'] ?? null,
            new ArraySequence(array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $data['caption'] ?? [])), new ArraySequence($data['attribution'] ?? []), $this->denormalizer->denormalize($data, File::class, $format, $context));
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return AssetFile::class === $type;
    }


    /**
     * @param AssetFile $data
     * @param $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = $this->normalizer->normalize($data->getFile());

        $arr['id'] = $data->getId();
        $arr['label'] = $data->getLabel();

        if ($data->getDoi()) {
            $arr['doi'] = $data->getDoi();
        }

        if ($data->getTitle()) {
            $arr['title'] = $data->getTitle();
        }

        if ($data->getCaption()->notEmpty()) {
            $arr['caption'] = $data->getCaption()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray();
        }

        if ($data->getAttribution()->notEmpty()) {
            $arr['attribution'] = $data->getAttribution()->toArray();
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof AssetFile;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AssetFile::class => true,
        ];
    }
}
