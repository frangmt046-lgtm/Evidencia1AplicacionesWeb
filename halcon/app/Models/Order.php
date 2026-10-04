<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    // SoftDeletes = logical delete: delete() only fills deleted_at,
    // withTrashed()/onlyTrashed() list deleted orders and restore() brings them back.
    use HasFactory, SoftDeletes;

    public const STATUS_ORDERED = 'Ordered';
    public const STATUS_IN_PROCESS = 'In process';
    public const STATUS_IN_ROUTE = 'In route';
    public const STATUS_DELIVERED = 'Delivered';

    public const STATUSES = [
        self::STATUS_ORDERED,
        self::STATUS_IN_PROCESS,
        self::STATUS_IN_ROUTE,
        self::STATUS_DELIVERED,
    ];

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'user_id',
        'delivery_address',
        'notes',
        'ordered_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
        ];
    }

    // An order belongs to one customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // An order is registered by one Sales user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // An order has many evidence photos (loading and delivery)
    public function evidencePhotos()
    {
        return $this->hasMany(EvidencePhoto::class);
    }

    // An order has many status changes
    public function statusHistories()
    {
        return $this->hasMany(StatusHistory::class);
    }
}
