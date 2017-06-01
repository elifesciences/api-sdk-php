<?php

namespace eLife\ApiSdk\Serializer;

use Symfony\Component\Serializer\Serializer;

/**
 * @deprecated Will be replaced by Symfony\Component\Serializer\Serializer
 *             when symfony/serializer dependency is bumped to ^3.1
 */
final class NormalizerAwareSerializer extends Serializer
{
    public function __construct(array $normalizers = [], array $encoders = [])
    {
        foreach ($normalizers as $normalizer) {
            if ($normalizer instanceof DenormalizerAwareInterface) {
                $normalizer->setDenormalizer($this);
            }
            if ($normalizer instanceof NormalizerAwareInterface) {
                $normalizer->setNormalizer($this);
            }
        }

        parent::__construct($normalizers, $encoders);
    }
}
