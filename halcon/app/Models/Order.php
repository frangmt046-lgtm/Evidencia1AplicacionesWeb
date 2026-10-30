<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{

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


    public function nextStatus(): ?string
    {
        $position = array_search($this->status, self::STATUSES, true);

        return self::STATUSES[$position + 1] ?? null;
    }

    // An order belongs to one customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function evidencePhotos()
    {
        return $this->hasMany(EvidencePhoto::class);
    }


    public function statusHistories()
    {
        return $this->hasMany(StatusHistory::class);
    }
}
