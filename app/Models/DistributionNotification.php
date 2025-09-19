<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DistributionNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'distribution_id',
        'campus',
        'type',
        'title',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function distribution()
    {
        return $this->belongsTo(MedicineDistribution::class);
    }

    public function scopeForCampus($query, $campus)
    {
        return $query->where('campus', $campus);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public static function createForDistribution($distribution, $type)
    {
        $notifications = [];

        if ($type === 'created') {
            // Notify the receiving campus
            $notifications[] = self::create([
                'distribution_id' => $distribution->id,
                'campus' => $distribution->to_campus,
                'type' => 'incoming',
                'title' => 'New Medicine Distribution Received',
                'message' => "Received {$distribution->quantity_distributed} units of {$distribution->medicine->name} from {$distribution->from_campus} for {$distribution->to_department} department.",
            ]);

            // Notify the sending campus (confirmation)
            $notifications[] = self::create([
                'distribution_id' => $distribution->id,
                'campus' => $distribution->from_campus,
                'type' => 'outgoing',
                'title' => 'Distribution Sent Successfully',
                'message' => "Successfully distributed {$distribution->quantity_distributed} units of {$distribution->medicine->name} to {$distribution->to_campus} ({$distribution->to_department} department).",
            ]);
        }

        return $notifications;
    }
}
