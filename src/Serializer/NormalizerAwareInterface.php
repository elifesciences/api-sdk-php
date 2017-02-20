<?php

namespace eLife\ApiSdk\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @deprecated Will be replaced by Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface
 *             when symfony/serializer dependency is bumped to ^3.1
 */
interface NormalizerAwareInterface
{
    public function setNormalizer(NormalizerInterface $normalizer);
}
