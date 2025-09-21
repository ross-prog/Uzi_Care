<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StrictRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$allowedActions): Response
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Unauthorized');
        }

        // Check each allowed action
        foreach ($allowedActions as $action) {
            if ($this->userCanPerformAction($user, $action)) {
                return $next($request);
            }
        }

        abort(403, 'Access denied. Insufficient permissions.');
    }

    private function userCanPerformAction($user, $action): bool
    {
        return match($action) {
            'manage-accounts' => $user->canManageAccounts(),
            'manage-records' => $user->canManageRecords(),
            'view-inventory' => $user->canViewInventory(),
            'manage-inventory' => $user->canManageInventory(),
            'download-reports' => $user->canDownloadReports(),
            'generate-inventory-reports' => $user->canGenerateInventoryReports(),
            'compile-reports' => $user->canCompileReports(),
            'distribute-medicines' => $user->canDistributeMedicines(),
            'request-distributions' => $user->canRequestDistributions(),
            'view-statistics' => $user->canViewStatistics(),
            'nurse' => $user->role === 'nurse',
            'admin' => $user->role === 'admin',
            'super-admin' => $user->role === 'super_admin',
            'main-admin' => $user->isMainCampusAdmin(),
            'admin-nurse' => in_array($user->role, ['admin', 'nurse', 'super_admin']),
            default => false,
        };
    }
}
