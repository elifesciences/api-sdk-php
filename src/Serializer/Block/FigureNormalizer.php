<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\AssetFile;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Figure;
use eLife\ApiSdk\Model\Block\FigureAsset;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class FigureNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Figure
    {
        return new Figure(...array_map(function (array $asset) {
            return new FigureAsset(
                $asset['doi'] ?? null,
                $asset['label'],
                new ArraySequence(array_map(function (array $file) {
                    return $this->denormalizer->denormalize($file, AssetFile::class);
                }, $asset['sourceData'] ?? [])),
                $this->denormalizer->denormalize($asset, Block::class)
            );
        }, $data['assets']));
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Figure::class === $type
            ||
            (Block::class === $type && 'figure' === $data['type']);
    }

    /**
     * @param Figure $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        return [
            'type' => 'figure',
            'assets' => $object->getAssets()->map(function (FigureAsset $asset) {
                $data = $this->normalizer->normalize($asset->getAsset());

                if ($asset->getDoi()) {
                    $data['doi'] = $asset->getDoi();
                }

                $data['label'] = $asset->getLabel();

                if ($asset->getSourceData()->notEmpty()) {
                    $data['sourceData'] = $asset->getSourceData()->map(function (AssetFile $file) {
                        return $this->normalizer->normalize($file);
                    })->toArray();
                }

                return $data;
            })->toArray(),
        ];
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Figure;
    }
}
