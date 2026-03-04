<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Converts between array (PHP) and JSON string (form display).
 * Used to handle JSON fields that Doctrine deserializes automatically.
 *
 * @implements DataTransformerInterface<mixed[], string>
 */
class JsonToStringTransformer implements DataTransformerInterface
{
    /**
     * Transform array to JSON string for display in form.
     */
    public function transform($value): string
    {
        if (null === $value || '' === $value) {
            return '';
        }

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            return is_string($encoded) ? $encoded : '';
        }

        if (is_string($value)) {
            // Already a string, try to pretty-print it
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $encoded = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                return is_string($encoded) ? $encoded : $value;
            }

            return $value;
        }

        return '';
    }

    /**
     * Transform JSON string from form back to array.
     *
     * @return array<mixed>
     */
    public function reverseTransform($value): array
    {
        if (null === $value || '' === $value) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
