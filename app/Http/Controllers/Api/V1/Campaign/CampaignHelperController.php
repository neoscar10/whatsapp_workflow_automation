<?php

namespace App\Http\Controllers\Api\V1\Campaign;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Services\Campaign\CampaignTemplateVariableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignHelperController extends Controller
{
    use RespondsWithApiResponse;

    public function __construct(
        protected CampaignTemplateVariableService $variableService
    ) {}

    /**
     * List eligible templates for campaigns.
     */
    public function templates(Request $request): JsonResponse
    {
        $templates = WhatsAppTemplate::where('company_id', $request->user()->company_id)
            ->where('status', 'approved')
            ->when($request->search, function($q) use ($request) {
                $q->where('remote_template_name', 'like', "%{$request->search}%");
            })
            ->when($request->category, function($q) use ($request) {
                $q->where('category', $request->category);
            })
            ->latest()
            ->get();

        return $this->successResponse($templates, 'Templates retrieved successfully.');
    }

    /**
     * Get variables for a specific template.
     */
    public function templateVariables(Request $request, int $templateId): JsonResponse
    {
        $template = WhatsAppTemplate::where('company_id', $request->user()->company_id)
            ->findOrFail($templateId);

        $variables = $this->variableService->extractVariables($template);
        $sources = $this->variableService->provideAvailablePersonalizationFields();

        return $this->successResponse([
            'variables' => $variables,
            'available_sources' => $sources,
        ], 'Template variables retrieved successfully.');
    }

    /**
     * Get available personalization fields.
     */
    public function personalizationFields(Request $request): JsonResponse
    {
        $fields = $this->variableService->provideAvailablePersonalizationFields();
        return $this->successResponse($fields, 'Personalization fields retrieved successfully.');
    }
}
