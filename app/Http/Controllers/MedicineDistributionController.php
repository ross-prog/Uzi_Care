<?php

namespace App\Http\Controllers;

use App\Models\MedicineDistribution;
use App\Models\Medicine;
use App\Models\Inventory;
use App\Models\DistributionNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class MedicineDistributionController extends Controller
{
    /**
     * Display a listing of distributions
     */
    public function index()
    {
        $user = Auth::user();
        
        $distributions = MedicineDistribution::with(['medicine', 'inventory', 'distributedBy'])
            ->when($user->role !== 'admin', function ($query) use ($user) {
                // Non-admin users only see distributions involving their campus as sender OR receiver
                return $query->where(function ($q) use ($user) {
                    $q->where('from_campus', $user->campus)
                      ->orWhere('to_campus', $user->campus);
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get incoming distributions for notifications
        $incomingDistributions = MedicineDistribution::where('to_campus', $user->campus)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->count();

        // Get outgoing distributions
        $outgoingDistributions = MedicineDistribution::where('from_campus', $user->campus)
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->count();

        // Get unread notifications for this campus
        $unreadNotifications = DistributionNotification::forCampus($user->campus)
            ->unread()
            ->with('distribution.medicine')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $totalUnreadCount = DistributionNotification::forCampus($user->campus)
            ->unread()
            ->count();

        return Inertia::render('MedicineDistribution/Index', [
            'distributions' => $distributions,
            'userCampus' => $user->campus,
            'canCreateDistribution' => $user->role === 'inventory_manager' || $user->role === 'admin',
            'incomingCount' => $incomingDistributions,
            'outgoingCount' => $outgoingDistributions,
            'notifications' => $unreadNotifications,
            'unreadNotificationCount' => $totalUnreadCount,
        ]);
    }

    /**
     * Show the form for creating a new distribution
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get available medicines with inventory for the user's campus
        $medicines = Medicine::with(['inventory' => function ($query) use ($user) {
            $query->where('campus', $user->campus)
                  ->where('quantity', '>', 0);
        }])->get()->filter(function ($medicine) {
            return $medicine->inventory->isNotEmpty();
        })->values(); // Reset array keys after filtering

        $campuses = [
            'Main Campus',
            'North Campus',
            'South Campus',
            'East Campus',
            'West Campus',
            'Downtown Campus',
            'Satellite Clinic A',
            'Satellite Clinic B'
        ];

        // Debug info - remove in production
        Log::info('Distribution Create Debug', [
            'user_campus' => $user->campus,
            'medicines_count' => $medicines->count(),
            'total_inventory_count' => \App\Models\Inventory::where('campus', $user->campus)->count(),
        ]);

        return Inertia::render('MedicineDistribution/Create', [
            'medicines' => $medicines,
            'campuses' => $campuses,
            'userCampus' => $user->campus,
        ]);
    }

    /**
     * Store a newly created distribution
     */
    public function store(Request $request)
    {
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'inventory_id' => 'required|exists:inventories,id',
            'to_campus' => 'required|string|max:255',
            'to_department' => 'required|string|max:255',
            'quantity_distributed' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $inventory = Inventory::findOrFail($request->inventory_id);

        // Validate quantity
        if ($request->quantity_distributed > $inventory->quantity) {
            return back()->withErrors(['quantity_distributed' => 'Quantity exceeds available stock.']);
        }

        // Create distribution record
        $distribution = MedicineDistribution::create([
            'medicine_id' => $request->medicine_id,
            'inventory_id' => $request->inventory_id,
            'distributed_by' => $user->id,
            'from_campus' => $user->campus,
            'to_campus' => $request->to_campus,
            'to_department' => $request->to_department,
            'quantity_distributed' => $request->quantity_distributed,
            'batch_number' => $inventory->batch_number,
            'expiry_date' => $inventory->expiry_date,
            'notes' => $request->notes,
            'status' => 'completed', // Auto-approve for now
            'distribution_date' => now(),
        ]);

        // Update inventory quantity
        $inventory->decrement('quantity', $request->quantity_distributed);

        // Create new inventory entry for the receiving campus
        Inventory::create([
            'medicine_id' => $request->medicine_id,
            'campus' => $request->to_campus,
            'quantity' => $request->quantity_distributed,
            'expiry_date' => $inventory->expiry_date,
            'batch_number' => $inventory->batch_number,
            'distributor' => $user->campus, // The distributing campus
            'cost_per_unit' => $inventory->cost_per_unit,
            'low_stock_threshold' => $inventory->low_stock_threshold,
        ]);

        // Create notifications for both campuses
        DistributionNotification::createForDistribution($distribution, 'created');

        return redirect()->route('medicine-distributions.index')
            ->with('success', 'Medicine distribution created successfully. Reference: ' . $distribution->reference_number);
    }

    /**
     * Display the specified distribution
     */
    public function show(MedicineDistribution $medicineDistribution)
    {
        $medicineDistribution->load(['medicine', 'inventory', 'distributedBy']);
        
        return Inertia::render('MedicineDistribution/Show', [
            'distribution' => $medicineDistribution,
        ]);
    }

    /**
     * Update the status of a distribution
     */
    public function updateStatus(Request $request, MedicineDistribution $medicineDistribution)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,completed,cancelled',
        ]);

        $medicineDistribution->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Distribution status updated successfully.');
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|exists:distribution_notifications,id',
        ]);

        $user = Auth::user();
        $notification = DistributionNotification::where('id', $request->notification_id)
            ->where('campus', $user->campus)
            ->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read for current user's campus
     */
    public function markAllNotificationsRead()
    {
        $user = Auth::user();
        
        DistributionNotification::forCampus($user->campus)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
