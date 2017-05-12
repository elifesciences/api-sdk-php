<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Table;
use eLife\ApiSdk\Model\Footnote;
use eLife\ApiSdk\Serializer\DenormalizerAwareInterface;
use eLife\ApiSdk\Serializer\DenormalizerAwareTrait;
use eLife\ApiSdk\Serializer\NormalizerAwareInterface;
use eLife\ApiSdk\Serializer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TableNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $class, $format = null, array $context = []) : Table
    {
        return new Table(
            $data['id'] ?? null,
            $data['title'] ?? null,
            new ArraySequence(array_map(function (array $block) {
                return $this->denormalizer->denormalize($block, Block::class);
            }, $data['caption'] ?? [])),
            new ArraySequence($data['attribution'] ?? []),
            $data['tables'],
            array_map(function (array $footnote) {
                return new Footnote(
                    $footnote['id'] ?? null,
                    $footnote['label'] ?? null,
                    new ArraySequence(array_map(function (array $block) {
                        return $this->denormalizer->denormalize($block, Block::class);
                    }, $footnote['text']))
                );
            }, $data['footnotes'] ?? [])
        );
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Table::class === $type
            ||
            (Block::class === $type && 'table' === $data['type']);
    }

    /**
     * @param Table $object
     */
    public function normalize($object, $format = null, array $context = []) : array
    {
        $data = [
            'type' => 'table',
            'tables' => $object->getTables(),
        ];

        if ($object->getId()) {
            $data['id'] = $object->getId();
        }

        if ($object->getTitle()) {
            $data['title'] = $object->getTitle();
        }

        if ($object->getCaption()->notEmpty()) {
            $data['caption'] = $object->getCaption()->map(function (Block $block) {
                return $this->normalizer->normalize($block);
            })->toArray();
        }

        if ($object->getAttribution()->notEmpty()) {
            $data['attribution'] = $object->getAttribution()->toArray();
        }

        if (count($object->getFootnotes())) {
            $data['footnotes'] = array_map(function (Footnote $footnote) {
                $data = [
                    'text' => $footnote->getText()->map(function (Block $block) {
                        return $this->normalizer->normalize($block);
                    })->toArray(),
                ];

                if ($footnote->getId()) {
                    $data['id'] = $footnote->getId();
                }

                if ($footnote->getLabel()) {
                    $data['label'] = $footnote->getLabel();
                }

                return $data;
            }, $object->getFootnotes());
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null) : bool
    {
        return $data instanceof Table;
    }
}
