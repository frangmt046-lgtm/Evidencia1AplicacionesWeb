<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvidencePhoto extends Model
{
    use HasFactory;

    public const TYPE_LOADING = 'loading';
    public const TYPE_DELIVERY = 'delivery';

    protected $fillable = [
        'order_id',
        'user_id',
        'type',
        'image_path',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
        ];
    }

    // A photo belongs to one order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // A photo is uploaded by one Route user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
