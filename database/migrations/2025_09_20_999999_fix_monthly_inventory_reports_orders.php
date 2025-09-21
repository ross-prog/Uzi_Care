<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Fix existing monthly inventory reports by filtering out zero quantity orders
        $reports = DB::table('monthly_inventory_reports')->get();
        
        foreach ($reports as $report) {
            $orderRequests = json_decode($report->order_requests, true);
            if (is_array($orderRequests)) {
                $filteredOrders = array_filter($orderRequests, function($order) {
                    return isset($order['quantity_to_order']) && $order['quantity_to_order'] > 0;
                });
                
                DB::table('monthly_inventory_reports')
                    ->where('id', $report->id)
                    ->update(['order_requests' => json_encode(array_values($filteredOrders))]);
            }
        }
    }

    public function down()
    {
        // No rollback needed
    }
};