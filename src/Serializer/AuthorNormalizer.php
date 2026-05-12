<?php

namespace eLife\ApiSdk\Serializer;

use eLife\ApiSdk\Model\Address;
use eLife\ApiSdk\Model\Author;
use eLife\ApiSdk\Model\Place;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

abstract class AuthorNormalizer implements NormalizerInterface, DenormalizerInterface, NormalizerAwareInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    final public function denormalize($data, $type, $format = null, array $context = []) : Author
    {
        $data['affiliations'] = array_map(function (array $affiliation) use ($format, $context) {
            return $this->denormalizer->denormalize($affiliation, Place::class, $format, $context);
        }, $data['affiliations'] ?? []);

        $data['postalAddresses'] = array_map(function (array $address) use ($format, $context) {
            return $this->denormalizer->denormalize($address, Address::class, $format, $context);
        }, $data['postalAddresses'] ?? []);

        return $this->denormalizeAuthor($data, $type, $format, $context);
    }


    /**
     * @param Author $data
     * @param $format
     * @param array $context
     * @return array
     * @throws ExceptionInterface
     */
    final public function normalize($data, $format = null, array $context = []) : array
    {
        $arr = [];

        if (count($data->getAdditionalInformation())) {
            $arr['additionalInformation'] = $data->getAdditionalInformation();
        }

        if (count($data->getAffiliations())) {
            $arr['affiliations'] = array_map(function (Place $place) use ($format, $context) {
                return $this->normalizer->normalize($place, $format, $context);
            }, $data->getAffiliations());
        }

        if ($data->getCompetingInterests()) {
            $arr['competingInterests'] = $data->getCompetingInterests();
        }
        if ($data->getContribution()) {
            $arr['contribution'] = $data->getContribution();
        }

        if (count($data->getEmailAddresses())) {
            $arr['emailAddresses'] = $data->getEmailAddresses();
        }

        if (count($data->getEqualContributionGroups())) {
            $arr['equalContributionGroups'] = $data->getEqualContributionGroups();
        }

        if (count($data->getPhoneNumbers())) {
            $arr['phoneNumbers'] = $data->getPhoneNumbers();
        }

        if (count($data->getPostalAddresses())) {
            $arr['postalAddresses'] = array_map(function (Address $address) use ($format, $context) {
                return $this->normalizer->normalize($address, $format, $context);
            }, $data->getPostalAddresses());
        }

        return $this->normalizeAuthor($data, $arr, $format, $context);
    }

    abstract protected function denormalizeAuthor(
        array $data,
        string $class,
        string $format = null,
        array $context = []
    ) : Author;

    abstract protected function normalizeAuthor(
        Author $object,
        array $data,
        $format = null,
        array $context = []
    ) : array;
}
