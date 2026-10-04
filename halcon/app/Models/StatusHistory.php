<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'previous_status',
        'new_status',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
        ];
    }

    // A status change belongs to one order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // A status change is made by one user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
