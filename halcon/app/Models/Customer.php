<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_number',
        'name',
        'tax_id',
        'fiscal_address',
        'email',
        'phone',
    ];

    // A customer places many orders
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
