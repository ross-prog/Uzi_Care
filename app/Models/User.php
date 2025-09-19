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

    public function isNurse(): bool
    {
        return $this->role === 'nurse';
    }

    public function isInventoryManager(): bool
    {
        return $this->role === 'inventory_manager';
    }

    public function isAccountManager(): bool
    {
        return $this->role === 'account_manager';
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
        return in_array($this->role, ['admin', 'nurse', 'inventory_manager']);
    }

    /**
     * Check if user can manage inventory
     */
    public function canManageInventory(): bool
    {
        return in_array($this->role, ['admin', 'inventory_manager']);
    }

    /**
     * Check if user can manage accounts
     */
    public function canManageAccounts(): bool
    {
        return in_array($this->role, ['admin', 'account_manager']);
    }

    /**
     * Check if user can download reports
     */
    public function canDownloadReports(): bool
    {
        return in_array($this->role, ['admin', 'nurse']);
    }

    /**
     * Get user's role display name
     */
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'nurse' => 'Nurse',
            'inventory_manager' => 'Inventory Manager',
            'account_manager' => 'Account Manager',
            default => 'Unknown'
        };
    }
}
