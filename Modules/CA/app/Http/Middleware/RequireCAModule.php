<?php

namespace Modules\CA\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Platform\ModuleService;

class RequireCAModule
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->company) {
            return abort(403, 'Unauthorized access to CA module.');
        }

        $moduleService = app(ModuleService::class);
        
        if (!$moduleService->companyHasModule($user->company, 'ca')) {
            return abort(403, 'Your company does not have the CA module enabled.');
        }

        return $next($request);
    }
}
