<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'employee_id',
        'department',
        'campus',
        'contact_number',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Role-based permission methods
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isMainCampusAdmin(): bool
    {
        return $this->role === 'admin' && $this->campus === 'Main Campus';
    }

    public function isNurse(): bool
    {
        return $this->role === 'nurse';
    }

    /**
     * Check if user can manage patient records
     */
    public function canManageRecords(): bool
    {
        return in_array($this->role, ['admin', 'nurse']);
    }

    /**
     * Check if user can view inventory
     */
    public function canViewInventory(): bool
    {
        return in_array($this->role, ['admin', 'nurse']);
    }

    /**
     * Check if user can manage inventory (add, edit, delete items)
     */
    public function canManageInventory(): bool
    {
        return in_array($this->role, ['admin', 'nurse']); // Each campus manages their own inventory
    }

    /**
     * Check if user can manage accounts (user creation) - ONLY super_admin
     */
    public function canManageAccounts(): bool
    {
        return $this->isSuperAdmin(); // Only super admin can manage users
    }

    /**
     * Check if user can download reports
     */
    public function canDownloadReports(): bool
    {
        return in_array($this->role, ['admin', 'nurse']);
    }

    /**
     * Check if user can generate inventory reports
     */
    public function canGenerateInventoryReports(): bool
    {
        return in_array($this->role, ['admin', 'nurse']); // Each campus can generate their own reports
    }

    /**
     * Check if user can compile all campus reports (Main Campus only)
     */
    public function canCompileReports(): bool
    {
        return $this->isMainCampusAdmin(); // Only Main Campus admin can compile all reports
    }

    /**
     * Check if user can distribute medicines (Main Campus only)
     */
    public function canDistributeMedicines(): bool
    {
        return $this->isMainCampusAdmin(); // Only Main Campus can distribute
    }

    /**
     * Check if user can request medicine distributions
     */
    public function canRequestDistributions(): bool
    {
        return $this->role === 'nurse' && $this->campus !== 'Main Campus'; // Other campuses can request
    }

    /**
     * Check if user can view statistics
     */
    public function canViewStatistics(): bool
    {
        return $this->role === 'admin'; // Admin of each campus can view their stats
    }

    /**
     * Get user's role display name
     */
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            'super_admin' => 'System Administrator',
            'admin' => $this->campus === 'Main Campus' ? 'Head Administrator' : 'Administrator',
            'nurse' => 'Head Nurse',
            default => 'Unknown'
        };
    }
}
