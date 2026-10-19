<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudioSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'type',
        'order_id',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

