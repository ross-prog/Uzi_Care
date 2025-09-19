<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MedicineDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'inventory_id',
        'distributed_by',
        'from_campus',
        'to_campus',
        'to_department',
        'quantity_distributed',
        'batch_number',
        'expiry_date',
        'reference_number',
        'notes',
        'status',
        'distribution_date',
    ];

    protected $casts = [
        'distribution_date' => 'datetime',
        'expiry_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($distribution) {
            if (empty($distribution->reference_number)) {
                $distribution->reference_number = 'DIST-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            }
        });
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function distributedBy()
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }

    public function scopeForCampus($query, $campus)
    {
        return $query->where('to_campus', $campus)->orWhere('from_campus', $campus);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
