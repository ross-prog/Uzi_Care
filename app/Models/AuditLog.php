<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'event',
        'old_values',
        'new_values',
        'changes',
        'user_type',
        'user_id',
        'user_name',
        'user_role',
        'ip_address',
        'user_agent',
        'metadata',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changes' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the auditable model.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Create a new audit log entry
     */
    public static function logEvent(
        Model $model,
        string $event,
        array $oldValues = null,
        array $newValues = null,
        string $description = null,
        array $metadata = []
    ): self {
        $changes = [];
        
        if ($oldValues && $newValues) {
            foreach ($newValues as $key => $value) {
                if (isset($oldValues[$key]) && $oldValues[$key] !== $value) {
                    $changes[$key] = [
                        'old' => $oldValues[$key],
                        'new' => $value
                    ];
                }
            }
        }

        return self::create([
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'event' => $event,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changes' => $changes,
            'user_type' => Auth::check() ? get_class(Auth::user()) : null,
            'user_id' => Auth::id(),
            'user_name' => Auth::check() ? Auth::user()->name ?? 'Unknown User' : 'System',
            'user_role' => Auth::check() ? (Auth::user()->role ?? 'user') : 'system',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
            'description' => $description,
        ]);
    }

    /**
     * Get formatted description of the change
     */
    public function getFormattedDescriptionAttribute(): string
    {
        if ($this->description) {
            return $this->description;
        }

        $eventDescriptions = [
            'created' => 'Record was created',
            'updated' => 'Record was updated',
            'deleted' => 'Record was deleted',
            'viewed' => 'Record was viewed',
        ];

        $baseDescription = $eventDescriptions[$this->event] ?? "Record was {$this->event}";
        
        if ($this->changes && count($this->changes) > 0) {
            $changedFields = array_keys($this->changes);
            $baseDescription .= ' (Fields: ' . implode(', ', $changedFields) . ')';
        }

        return $baseDescription;
    }
}
