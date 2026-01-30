<?php

declare(strict_types=1);

namespace WCFN\Normalizer;

final class FieldNormalizer
{
    public function normalize(string $value): string
    {
        $value = trim($value);

        // Normalize line breaks
        $value = preg_replace("/\r\n|\r/", "\n", $value);

        // Reduce multiple spaces/tabs
        $value = preg_replace('/[ \t]+/', ' ', $value);

        return $value ?? '';
    }
}
