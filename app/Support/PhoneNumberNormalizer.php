<?php

namespace App\Support;

class PhoneNumberNormalizer
{
    /**
     * Clean scientific notation strings, formulas, or raw strings into standard phone text.
     */
    public static function clean(string $phone): string
    {
        $phone = trim($phone);

        // Remove surrounding Excel formula quotes if present (e.g. ="123456" or '123456')
        if (preg_match('/^=\s*"?([^"]*)"?$/', $phone, $m)) {
            $phone = $m[1];
        }
        $phone = trim($phone, "'\"");

        // Detect scientific notation (e.g., 9.17058E+11, 9.17058e+11, 9.17058E11, 9.17058e11)
        if (is_numeric($phone) && preg_match('/[eE]/', $phone)) {
            $phone = sprintf('%.0f', (float) $phone);
        }

        return $phone;
    }

    /**
     * Normalize a phone number to digits-only format.
     */
    public static function normalize(string $phone): string
    {
        $cleaned = self::clean($phone);

        // Remove all non-numeric characters
        return preg_replace('/[^0-9]/', '', $cleaned);
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

