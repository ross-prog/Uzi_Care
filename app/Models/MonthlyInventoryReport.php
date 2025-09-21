<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyInventoryReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus',
        'report_month',
        'report_year',
        'generated_by',
        'status',
        'inventory_data',
        'quantity_to_order',
        'generated_at',
        'submitted_at',
    ];

    protected $casts = [
        'inventory_data' => 'array',
        'quantity_to_order' => 'array',
        'generated_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function scopeForCampus($query, $campus)
    {
        return $query->where('campus', $campus);
    }

    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('report_month', $month)->where('report_year', $year);
    }

    public function getReportPeriodAttribute()
    {
        return date('F Y', mktime(0, 0, 0, $this->report_month, 1, $this->report_year));
    }
}
