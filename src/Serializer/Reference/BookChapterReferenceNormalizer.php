<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\BookChapterReference;
use eLife\ApiSdk\Model\Reference\ReferencePages;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BookChapterReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use ContainsBookNormalizer;

    /**
     * @throws ExceptionInterface
     */
    protected function denormalizeReference($data, $class, $format = null, array $context = []) : Reference
    {
        return new BookChapterReference(
            $data['id'],
            $data['date'],
            $data['discriminator'],
            $data['authors'],
            $data['authorsEtAl'],
            $data['editors'],
            $data['editorsEtAl'],
            $data['chapterTitle'],
            $data['bookTitle'],
            $data['publisher'],
            $this->denormalizer->denormalize($data['pages'], ReferencePages::class, $format, $context),
            $data['volume'],
            $data['edition'],
            $data['doi'],
            $data['pmid'],
            $data['isbn']
        );
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []) : bool
    {
        return
            BookChapterReference::class === $type
            ||
            (Reference::class === $type && 'book-chapter' === $data['type']);
    }

    /**
     * @param BookChapterReference $reference
     */
    protected function normalizeReference(Reference $reference, array $data, string $format = null, array $context = []) : array
    {
        $data['type'] = 'book-chapter';
        $data['chapterTitle'] = $reference->getChapterTitle();
        $data['pages'] = $this->normalizer->normalize($reference->getPages(), $format, $context);

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof BookChapterReference;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            BookChapterReference::class => false,
            Reference::class => false,
        ];
    }
}
