<?php

namespace App\Http\Controllers\Api\V1\WhatsApp;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use Illuminate\Http\JsonResponse;

class WhatsAppHelperController extends Controller
{
    use RespondsWithApiResponse;

    /**
     * Get supported template categories.
     */
    public function categories(): JsonResponse
    {
        $categories = [
            ['id' => 'marketing', 'name' => 'Marketing', 'description' => 'Promos, offers, updates'],
            ['id' => 'utility', 'name' => 'Utility', 'description' => 'Order updates, alerts'],
            ['id' => 'authentication', 'name' => 'Authentication', 'description' => 'OTPs'],
        ];

        return $this->successResponse($categories, 'Categories retrieved successfully.');
    }

    /**
     * Get supported languages.
     */
    public function languages(): JsonResponse
    {
        $languages = [
            ['code' => 'en_US', 'name' => 'English (US)'],
            ['code' => 'en_GB', 'name' => 'English (UK)'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'pt_BR', 'name' => 'Portuguese (Brazil)'],
            ['code' => 'pt_PT', 'name' => 'Portuguese (Portugal)'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'it', 'name' => 'Italian'],
            ['code' => 'hi', 'name' => 'Hindi'],
            ['code' => 'ar', 'name' => 'Arabic'],
        ];

        return $this->successResponse($languages, 'Languages retrieved successfully.');
    }
}
