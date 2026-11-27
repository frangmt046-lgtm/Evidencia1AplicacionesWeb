<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'deleted_by',
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

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['invoice_number'] ?? null, fn ($q, $invoice) => $q->where('invoice_number', 'like', '%'.trim($invoice).'%'))
            ->when($filters['customer_number'] ?? null, fn ($q, $number) => $q->whereHas(
                'customer', fn ($customer) => $customer->where('customer_number', 'like', '%'.trim($number).'%')
            ))
            ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('ordered_at', $date))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));
    }

    public static function nextInvoiceNumber(): string
    {
        $max = static::query()
            ->where('invoice_number', 'like', 'INV-%')
            ->pluck('invoice_number')
            ->map(fn ($number) => (int) substr($number, 4))
            ->max() ?? 0;

        return 'INV-'.str_pad($max + 1, 5, '0', STR_PAD_LEFT);
    }
}
