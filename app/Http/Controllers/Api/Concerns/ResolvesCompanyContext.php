<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

trait ResolvesCompanyContext
{
    /**
     * Resolves the target company_id for the current API request.
     * Allows Super Admins to impersonate and manage any company's data
     * via ?company_id=X or X-Company-ID request header.
     */
    protected function resolveCompanyId(Request $request): ?int
    {
        $user = $request->user();
        if (!$user) {
            return null;
        }

        if ($user->role === 'super_admin' || ($user->is_super_admin ?? false)) {
            if ($request->has('company_id') && is_numeric($request->input('company_id'))) {
                return (int) $request->input('company_id');
            }

            if ($request->hasHeader('X-Company-ID') && is_numeric($request->header('X-Company-ID'))) {
                return (int) $request->header('X-Company-ID');
            }

            // Fallback for Super Admin: default to user's assigned company or first active company
            return $user->company_id ?: (Company::where('status', 'active')->value('id') ?: null);
        }

        return $user->company_id;
    }

    /**
     * Resolves the target Company model for the current API request.
     */
    protected function resolveCompany(Request $request): ?Company
    {
        $companyId = $this->resolveCompanyId($request);
        if (!$companyId) {
            return null;
        }

        return Company::find($companyId);
    }
}
