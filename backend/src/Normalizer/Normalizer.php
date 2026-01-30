<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\SplitFairly\DenormalizerInterface as ContractDenormalizerInterface;
use App\SplitFairly\NormalizerInterface as ContractNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer as SymfonyAbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface as SymfonyDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface as SymfonyNormalizerInterface;

final readonly class Normalizer implements ContractNormalizerInterface, ContractDenormalizerInterface
{
    public function __construct(
        private SymfonyNormalizerInterface $normalizer,
        private SymfonyDenormalizerInterface $denormalizer,
    ) {
    }

    /**
     * Create an object of the specified type from the specified set of normalized data.
     */
    public function fromArray(mixed $data, string $type): mixed
    {
        return $this->denormalizer->denormalize($data, $type);
    }

    /**
     * Normalize an object into an array structure of key value pairs.
     *
     * @param array<string> $ignoreFields
     *
     * @return array<string,mixed>
     */
    public function toArray(mixed $object, array $ignoreFields = []): array
    {
        return $this->normalizer->normalize(
            $object,
            context: !empty($ignoreFields)
                ? [SymfonyAbstractNormalizer::IGNORED_ATTRIBUTES => $ignoreFields]
                : []
        );
    }
}
