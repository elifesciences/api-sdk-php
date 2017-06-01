<?php

namespace eLife\ApiSdk\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @deprecated Will be replaced by Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait
 *             when symfony/serializer dependency is bumped to ^3.1
 */
trait DenormalizerAwareTrait
{
    /**
     * @var DenormalizerInterface
     */
    protected $denormalizer;

    public function setDenormalizer(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }
}
