<?php

namespace App\Http\Controllers\Api\V1\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\RespondsWithApiResponse;
use App\Models\Company;
use App\Models\WhatsApp\WhatsAppAccount;
use App\Models\WhatsApp\WhatsAppPhoneNumber;
use App\Models\Chat\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SuperAdminDiagnosticController extends Controller
{
    use RespondsWithApiResponse;

    protected function checkSuperAdmin(Request $request): ?JsonResponse
    {
        $user = $request->user();
        if (!$user || ($user->role !== 'super_admin' && !($user->is_super_admin ?? false))) {
            return $this->errorResponse('Unauthorized. Super Admin access required.', [], 403);
        }
        return null;
    }

    /**
     * Audit phone numbers, accounts, and detect cross-company duplicates.
     */
    public function auditPhoneNumbers(Request $request): JsonResponse
    {
        if ($authErr = $this->checkSuperAdmin($request)) {
            return $authErr;
        }

        $allNumbers = WhatsAppPhoneNumber::with(['company', 'account'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($num) {
                return [
                    'id' => $num->id,
                    'company_id' => $num->company_id,
                    'company_name' => $num->company?->name,
                    'company_email' => $num->company?->primary_email,
                    'whatsapp_account_id' => $num->whatsapp_account_id,
                    'phone_number_id' => $num->phone_number_id,
                    'phone_number' => $num->phone_number,
                    'display_name' => $num->display_name,
                    'status' => $num->status,
                    'waba_id' => $num->account?->waba_id,
                    'created_at' => $num->created_at?->toIso8601String(),
                    'updated_at' => $num->updated_at?->toIso8601String(),
                ];
            });

        // Group by phone_number_id to find duplicates across companies
        $duplicates = [];
        $grouped = $allNumbers->filter(fn($n) => !empty($n['phone_number_id']))->groupBy('phone_number_id');

        foreach ($grouped as $phoneNumId => $records) {
            $companyIds = $records->pluck('company_id')->unique();
            if ($companyIds->count() > 1) {
                $duplicates[] = [
                    'phone_number_id' => $phoneNumId,
                    'phone_number' => $records->first()['phone_number'],
                    'company_count' => $companyIds->count(),
                    'affected_company_ids' => $companyIds->values()->all(),
                    'records' => $records->values()->all(),
                ];
            }
        }

        // List all companies
        $companies = Company::with(['whatsappAccount', 'phoneNumbers'])
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($comp) {
                return [
                    'id' => $comp->id,
                    'name' => $comp->name,
                    'primary_email' => $comp->primary_email,
                    'status' => $comp->status,
                    'waba_id' => $comp->whatsappAccount?->waba_id,
                    'phone_numbers' => $comp->phoneNumbers->map(fn($pn) => [
                        'id' => $pn->id,
                        'phone_number_id' => $pn->phone_number_id,
                        'phone_number' => $pn->phone_number,
                        'status' => $pn->status,
                    ]),
                ];
            });

        return $this->successResponse([
            'duplicate_phone_numbers' => $duplicates,
            'all_phone_numbers' => $allNumbers,
            'companies' => $companies,
        ], 'Phone number and credentials audit completed successfully.');
    }

    /**
     * Consolidate duplicate phone numbers into a target company and clean up duplicates.
     */
    public function cleanupDuplicatePhoneNumber(Request $request): JsonResponse
    {
        if ($authErr = $this->checkSuperAdmin($request)) {
            return $authErr;
        }

        $request->validate([
            'phone_number_id' => 'required|string',
            'keep_company_id' => 'required|integer|exists:companies,id',
        ]);

        $phoneNumberId = $request->input('phone_number_id');
        $keepCompanyId = (int) $request->input('keep_company_id');

        // Find primary active number record for keep_company_id
        $primaryNumber = WhatsAppPhoneNumber::where('phone_number_id', $phoneNumberId)
            ->where('company_id', $keepCompanyId)
            ->first();

        if (!$primaryNumber) {
            // Find any number with this phone_number_id to reassign
            $primaryNumber = WhatsAppPhoneNumber::where('phone_number_id', $phoneNumberId)->first();
            if (!$primaryNumber) {
                return $this->errorResponse("No phone number record found for phone_number_id {$phoneNumberId}", [], 404);
            }
            // Update company_id to keepCompanyId
            $primaryNumber->update([
                'company_id' => $keepCompanyId,
                'status' => 'active',
            ]);
        } else {
            $primaryNumber->update(['status' => 'active']);
        }

        // Find all other duplicate records for this phone_number_id in OTHER companies or duplicate rows
        $duplicateNumbers = WhatsAppPhoneNumber::where('phone_number_id', $phoneNumberId)
            ->where('id', '!=', $primaryNumber->id)
            ->get();

        $deletedCount = 0;
        $reassignedConvsCount = 0;

        if ($duplicateNumbers->isNotEmpty()) {
            $dupIds = $duplicateNumbers->pluck('id');

            // Reassign any conversations from duplicate numbers to primaryNumber and keepCompanyId
            $reassignedConvsCount = Conversation::whereIn('whatsapp_phone_number_id', $dupIds)
                ->update([
                    'whatsapp_phone_number_id' => $primaryNumber->id,
                    'company_id' => $keepCompanyId,
                ]);

            $deletedCount = WhatsAppPhoneNumber::whereIn('id', $dupIds)->delete();
        }

        Log::info("MANUAL_SUPERADMIN_CLEANUP: Consolidated phone_number_id {$phoneNumberId} to Company {$keepCompanyId}", [
            'kept_number_id' => $primaryNumber->id,
            'deleted_duplicates_count' => $deletedCount,
            'reassigned_conversations_count' => $reassignedConvsCount,
        ]);

        return $this->successResponse([
            'kept_number_id' => $primaryNumber->id,
            'keep_company_id' => $keepCompanyId,
            'deleted_duplicates_count' => $deletedCount,
            'reassigned_conversations_count' => $reassignedConvsCount,
        ], "Successfully consolidated phone_number_id {$phoneNumberId} to Company {$keepCompanyId}.");
    }
}
