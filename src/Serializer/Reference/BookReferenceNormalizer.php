<?php

namespace eLife\ApiSdk\Serializer\Reference;

use eLife\ApiSdk\Model\Reference;
use eLife\ApiSdk\Model\Reference\BookReference;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class BookReferenceNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use ContainsBookNormalizer;

    protected function denormalizeReference($data, $class, $format = null, array $context = []) : Reference
    {
        return new BookReference(
            $data['id'],
            $data['date'],
            $data['discriminator'],
            $data['authors'],
            $data['authorsEtAl'],
            $data['editors'],
            $data['editorsEtAl'],
            $data['bookTitle'],
            $data['publisher'],
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
            BookReference::class === $type
            ||
            (Reference::class === $type && 'book' === $data['type']);
    }

    /**
     * @param BookReference $reference
     */
    protected function normalizeReference(Reference $reference, array $data, string $format = null, array $context = []) : array
    {
        $data['type'] = 'book';

        return $data;
    }

    public function supportsNormalization($data, $format = null, array $context = []) : bool
    {
        return $data instanceof BookReference;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            BookReference::class => false,
            Reference::class => false,
        ];
    }
}
