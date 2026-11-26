<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    // The five roles (company departments) of Halcon
    public const ADMINISTRATOR = 'Administrator';
    public const SALES = 'Sales';
    public const PURCHASING = 'Purchasing';
    public const WAREHOUSE = 'Warehouse';
    public const ROUTE = 'Route';

    public const ALL = [
        self::ADMINISTRATOR,
        self::SALES,
        self::PURCHASING,
        self::WAREHOUSE,
        self::ROUTE,
    ];

    protected $fillable = [
        'name',
        'description',
    ];


    public function users()
    {
        return $this->hasMany(User::class);
    }
}
