<?php

namespace App\Http\Middleware;

use App\Services\Platform\ModuleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    protected ModuleService $moduleService;

    public function __construct(ModuleService $moduleService)
    {
        $this->moduleService = $moduleService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Super admins have access to all modules/pages by default
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        $company = $user->company;

        if (!$company) {
            abort(403, 'User does not belong to any registered company.');
        }

        if (!$this->moduleService->companyHasModule($company, $moduleSlug)) {
            abort(403, 'This module is not active or assigned to your company.');
        }

        return $next($request);
    }
}
