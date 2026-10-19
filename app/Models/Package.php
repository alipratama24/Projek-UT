<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'code',
        'name',
        'tagline',
        'description',
        'price',
        'duration',
        'duration_minutes',
        'photo_count',
        'photo_count_label',
        'features',
        'badge',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
