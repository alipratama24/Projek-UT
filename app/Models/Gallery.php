<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'order_id',
        'client_name',
        'cover_image',
        'target_photos',
        'status',
        'access_type',
        'pin_code',
    ];

    protected $casts = [
        'target_photos' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function photos()
    {
        return $this->hasMany(GalleryPhoto::class);
    }
}

