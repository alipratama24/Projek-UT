<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_code',
        'order_id',
        'sender_name',
        'transfer_date',
        'bank_source',
        'nominal',
        'sender_notes',
        'proof_file',
        'file_original_name',
        'file_extension',
        'status',
        'internal_review_notes',
        'verified_at',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'verified_at' => 'datetime',
        'nominal' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

