<?php

namespace eLife\ApiSdk\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @deprecated Will be replaced by Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface
 *             when symfony/serializer dependency is bumped to ^3.1
 */
interface DenormalizerAwareInterface
{
    public function setDenormalizer(DenormalizerInterface $denormalizer);
}
