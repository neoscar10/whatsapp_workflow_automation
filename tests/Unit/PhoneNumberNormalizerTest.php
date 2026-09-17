<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Support\PhoneNumberNormalizer;

class PhoneNumberNormalizerTest extends TestCase
{
    public function test_expands_scientific_notation_uppercase()
    {
        $input = "9.17058E+11";
        $this->assertEquals("917058000000", PhoneNumberNormalizer::clean($input));
        $this->assertEquals("917058000000", PhoneNumberNormalizer::normalize($input));
        $this->assertTrue(PhoneNumberNormalizer::isValid($input));
    }

    public function test_expands_scientific_notation_lowercase()
    {
        $input = "9.17218e+11";
        $this->assertEquals("917218000000", PhoneNumberNormalizer::clean($input));
        $this->assertEquals("917218000000", PhoneNumberNormalizer::normalize($input));
        $this->assertTrue(PhoneNumberNormalizer::isValid($input));
    }

    public function test_cleans_excel_formula_strings()
    {
        $input = '="917058123456"';
        $this->assertEquals("917058123456", PhoneNumberNormalizer::clean($input));
        $this->assertEquals("917058123456", PhoneNumberNormalizer::normalize($input));
        $this->assertTrue(PhoneNumberNormalizer::isValid($input));
    }

    public function test_normalizes_standard_phone_numbers()
    {
        $input = "+91 70581 23456";
        $this->assertEquals("+91 70581 23456", PhoneNumberNormalizer::clean($input));
        $this->assertEquals("917058123456", PhoneNumberNormalizer::normalize($input));
        $this->assertTrue(PhoneNumberNormalizer::isValid($input));
    }
}
