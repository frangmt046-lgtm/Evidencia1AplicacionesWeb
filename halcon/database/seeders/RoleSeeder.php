<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Roles are the company departments described in the case.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'description' => 'Registers new users and assigns their roles.'],
            ['name' => 'Sales', 'description' => 'Takes orders from customers and registers them.'],
            ['name' => 'Purchasing', 'description' => 'Buys missing materials from external suppliers.'],
            ['name' => 'Warehouse', 'description' => 'Prepares orders and reports low or missing stock.'],
            ['name' => 'Route', 'description' => 'Delivers orders and uploads the evidence photos.'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
