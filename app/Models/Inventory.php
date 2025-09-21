<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'campus',
        'quantity',
        'expiry_date',
        'batch_number',
        'distributor',
        'date_added',
        'low_stock_threshold',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'date_added' => 'datetime',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function isLowStock()
    {
        return $this->quantity <= $this->low_stock_threshold;
    }

    public function isNearingExpiry($days = 30)
    {
        return $this->expiry_date <= now()->addDays($days);
    }
}
