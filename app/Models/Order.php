<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'invoice_number',
        'customer_id',
        'package_id',
        'customer_name',
        'customer_email',
        'customer_whatsapp',
        'package_name',
        'total_price',
        'session_date',
        'session_time_slot',
        'location',
        'customer_notes',
        'internal_notes',
        'session_status',
        'payment_status',
    ];

    protected $casts = [
        'session_date' => 'date',
        'total_price' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function studioSlots()
    {
        return $this->hasMany(StudioSlot::class);
    }

    public function gallery()
    {
        return $this->hasOne(Gallery::class);
    }
}

