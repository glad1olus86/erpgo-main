<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Plan;
use App\Models\User;

class CheckPlanModule
{
    /**
     * Handle an incoming request.
     * Check if user's plan has access to the requested module
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $module
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $module)
    {
        $user = auth()->user();
        
        // Super admin always has access
        if ($user && $user->type === 'super admin') {
            return $next($request);
        }
        
        // Get company owner for non-company users
        if ($user && $user->type !== 'company') {
            $companyOwner = User::find($user->created_by);
            if ($companyOwner) {
                $user = $companyOwner;
            }
        }
        
        if (!$user || !$user->plan) {
            return $this->denyAccess($request, $module);
        }
        
        $plan = Plan::find($user->plan);
        
        if (!$plan) {
            return $this->denyAccess($request, $module);
        }
        
        // Check if module is enabled in plan
        $moduleField = 'module_' . $module;
        
        // If field doesn't exist yet (migration not run), allow access
        if (!isset($plan->{$moduleField})) {
            return $next($request);
        }
        
        if ($plan->{$moduleField} != 1) {
            return $this->denyAccess($request, $module);
        }
        
        return $next($request);
    }
    
    /**
     * Deny access to module
     */
    protected function denyAccess(Request $request, string $module)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => __('Your plan does not include access to this module.'),
                'module' => $module
            ], 403);
        }
        
        return redirect()->route('jobsi.dashboard')
            ->with('error', __('Your plan does not include access to this module. Please upgrade your plan.'));
    }
}
