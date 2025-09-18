<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'unit',
        'dosage_strength',
        'form',
    ];

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }
}
