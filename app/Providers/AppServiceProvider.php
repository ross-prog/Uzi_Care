<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Inventory;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        if (config('app.env') === 'production' || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        // Share notification data with authenticated users only
        Inertia::share([
            'notificationData' => function () {
                // Only provide notifications if user is authenticated
                if (!Auth::check()) {
                    return [
                        'lowStockCount' => 0,
                        'nearingExpiryCount' => 0,
                    ];
                }

                try {
                    $user = Auth::user();
                    
                    // Admin sees all notifications across all campuses
                    if ($user->role === 'admin') {
                        $lowStockCount = Inventory::whereRaw('quantity <= low_stock_threshold')->count();
                        $nearingExpiryCount = Inventory::where('expiry_date', '<=', Carbon::now()->addDays(30))
                            ->where('expiry_date', '>=', Carbon::now())
                            ->count();
                    } else {
                        // Non-admin users see only their campus notifications
                        $lowStockCount = Inventory::where('campus_id', $user->campus_id)
                            ->whereRaw('quantity <= low_stock_threshold')
                            ->count();
                        $nearingExpiryCount = Inventory::where('campus_id', $user->campus_id)
                            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
                            ->where('expiry_date', '>=', Carbon::now())
                            ->count();
                    }
                    
                    return [
                        'lowStockCount' => $lowStockCount,
                        'nearingExpiryCount' => $nearingExpiryCount,
                    ];
                } catch (\Exception $e) {
                    // Fallback in case of database errors
                    return [
                        'lowStockCount' => 0,
                        'nearingExpiryCount' => 0,
                    ];
                }
            },
        ]);
    }
}
