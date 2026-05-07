<?php

namespace App\Support;

class PhoneNumberNormalizer
{
    /**
     * Normalize a phone number to digits-only format.
     */
    public static function normalize(string $phone): string
    {
        // Remove all non-numeric characters
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Check if a phone number is valid (has enough digits).
     */
    public static function isValid(string $phone): bool
    {
        $normalized = self::normalize($phone);
        
        // Basic check: minimum 7 digits, maximum 15 (ITU-T E.164 standard)
        $length = strlen($normalized);
        return $length >= 7 && $length <= 15;
    }
}
