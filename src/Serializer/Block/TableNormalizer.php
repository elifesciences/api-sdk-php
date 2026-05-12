<?php

namespace eLife\ApiSdk\Serializer\Block;

use eLife\ApiSdk\Collection\ArraySequence;
use eLife\ApiSdk\Model\Block;
use eLife\ApiSdk\Model\Block\Table;
use eLife\ApiSdk\Model\Footnote;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TableNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    public function denormalize($data, $type, $format = null, array $context = []) : Table
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

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            Table::class === $type
            ||
            (Block::class === $type && 'table' === $data['type']);
    }

    /**
     * @param Table $data
     * @throws ExceptionInterface
     */
    public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [
            'type' => 'table',
            'tables' => $data->getTables(),
        ];

        if ($data->getId()) {
            $arr['id'] = $data->getId();
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

        if (count($data->getFootnotes())) {
            $arr['footnotes'] = array_map(function (Footnote $footnote) {
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
            }, $data->getFootnotes());
        }

        return $arr;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof Table;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Table::class => false,
            Block::class => false,
        ];
    }
}
