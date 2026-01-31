<?php

namespace App\Support\CarteiImobil;

class SensitiveMasker
{
    public static function mask(?string $value, int $keepLast = 3): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $suffix = mb_substr($value, -$keepLast);
        return '***' . $suffix;
    }
}

