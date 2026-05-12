<?php

namespace App\Http\Controllers\Api\V1\WhatsApp;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Http\Requests\Api\V1\WhatsApp\Templates\ListWhatsAppTemplatesRequest;
use App\Http\Requests\Api\V1\WhatsApp\Templates\StoreWhatsAppTemplateRequest;
use App\Http\Requests\Api\V1\WhatsApp\Templates\UpdateWhatsAppTemplateRequest;
use App\Http\Resources\Api\V1\WhatsApp\WhatsAppTemplateResource;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppTemplateController extends Controller
{
    use RespondsWithApiResponse;

    protected WhatsAppTemplateService $templateService;

    public function __construct(WhatsAppTemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * Display a listing of the WhatsApp templates.
     *
     * @param ListWhatsAppTemplatesRequest $request
     * @return JsonResponse
     */
    public function index(ListWhatsAppTemplatesRequest $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        $filters = $request->validated();
        $templates = $this->templateService->listTemplatesForCompany($company, $filters);

        return $this->successResponse(
            WhatsAppTemplateResource::collection($templates)->response()->getData(true),
            'Templates retrieved successfully.'
        );
    }

    /**
     * Synchronize templates from Meta.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sync(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        $account = WhatsAppAccount::where('company_id', $company->id)->first();

        if (!$account) {
            return $this->errorResponse('WhatsApp account not found for this company.', [], 404);
        }

        try {
            $result = $this->templateService->syncTemplatesFromMeta($account);
            return $this->successResponse($result, 'Templates synced successfully.');
        } catch (\Exception $e) {
            Log::error('API Template Sync Error', ['message' => $e->getMessage(), 'company_id' => $company->id]);
            return $this->errorResponse('Failed to sync templates: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Store a newly created WhatsApp template.
     *
     * @param StoreWhatsAppTemplateRequest $request
     * @return JsonResponse
     */
    public function store(StoreWhatsAppTemplateRequest $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        $account = WhatsAppAccount::where('company_id', $company->id)->first();
        if (!$account) {
            return $this->errorResponse('WhatsApp account not found.', [], 404);
        }

        $data = $request->validated();
        $buttons = $data['buttons'] ?? [];
        $headerSampleFile = $request->file('header_sample_file');

        // Map request fields to service expected format
        $serviceData = [
            'remote_template_name' => $data['name'],
            'category' => $data['category'],
            'language_code' => $data['language_code'],
            'header_type' => $data['header_type'],
            'header_text' => $data['header_type'] === 'text' ? ($data['header_text'] ?? null) : null,
            'body_text' => $data['body_text'],
            'footer_text' => $data['footer_text'] ?? null,
            'example_payload' => $data['example_payload'] ?? [],
        ];

        try {
            $template = $this->templateService->createTemplate(
                $account,
                $serviceData,
                $buttons,
                $user->id,
                $headerSampleFile
            );

            return $this->successResponse(
                new WhatsAppTemplateResource($template),
                'Template created successfully and submitted for review.',
                201
            );
        } catch (\Exception $e) {
            Log::error('API Template Creation Error', ['message' => $e->getMessage(), 'company_id' => $company->id]);
            return $this->errorResponse('Failed to create template: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified WhatsApp template.
     *
     * @param UpdateWhatsAppTemplateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateWhatsAppTemplateRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        try {
            $template = $this->templateService->findTemplateForCompany($company, $id);
            $account = $template->account;

            if (!$account) {
                return $this->errorResponse('WhatsApp account not found for this template.', [], 404);
            }

            $data = $request->validated();
            $buttons = $data['buttons'] ?? $template->buttons->toArray();
            $headerSampleFile = $request->file('header_sample_file');

            // Map request fields to service expected format
            $serviceData = [
                'category' => $data['category'] ?? $template->category,
                'header_type' => $data['header_type'] ?? $template->header_type,
                'header_text' => isset($data['header_text']) ? $data['header_text'] : $template->header_text,
                'body_text' => $data['body_text'] ?? $template->body_text,
                'footer_text' => isset($data['footer_text']) ? $data['footer_text'] : $template->footer_text,
                'example_payload' => $data['example_payload'] ?? [],
            ];

            $updatedTemplate = $this->templateService->updateTemplateRecord(
                $template,
                $account,
                $serviceData,
                $buttons,
                $user->id,
                $headerSampleFile
            );

            return $this->successResponse(
                new WhatsAppTemplateResource($updatedTemplate),
                'Template updated successfully.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Template not found or access denied.', [], 404);
        } catch (\Exception $e) {
            Log::error('API Template Update Error', ['message' => $e->getMessage(), 'id' => $id]);
            return $this->errorResponse('Failed to update template: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Display the specified WhatsApp template.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        try {
            $template = $this->templateService->findTemplateForCompany($company, $id);
            return $this->successResponse(new WhatsAppTemplateResource($template), 'Template retrieved successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Template not found or access denied.', [], 404);
        } catch (\Exception $e) {
            return $this->errorResponse('An error occurred while retrieving the template.', [], 500);
        }
    }

    /**
     * Remove the specified WhatsApp template from Meta and locally.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;

        if (!$company) {
            return $this->errorResponse('User does not belong to a company.', [], 403);
        }

        try {
            $template = $this->templateService->findTemplateForCompany($company, $id);
            $this->templateService->deleteTemplate($template);

            return $this->successResponse(null, 'Template deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Template not found or access denied.', [], 404);
        } catch (\Exception $e) {
            Log::error('API Template Deletion Error', ['message' => $e->getMessage(), 'id' => $id]);
            return $this->errorResponse('Failed to delete template: ' . $e->getMessage(), [], 500);
        }
    }
}
