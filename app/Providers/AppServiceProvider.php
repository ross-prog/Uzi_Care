<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
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

        // Share notification data with all Inertia pages
        Inertia::share([
            'notificationData' => function () {
                try {
                    $lowStockCount = Inventory::whereRaw('quantity <= low_stock_threshold')->count();
                    $nearingExpiryCount = Inventory::where('expiry_date', '<=', Carbon::now()->addDays(30))
                        ->where('expiry_date', '>=', Carbon::now())
                        ->count();
                    
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
