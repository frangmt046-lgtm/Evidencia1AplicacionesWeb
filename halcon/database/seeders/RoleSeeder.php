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
            ['name' => Role::ADMINISTRATOR, 'description' => 'Manages users and roles.'],
            ['name' => Role::SALES, 'description' => 'Creates and manages customer orders.'],
            ['name' => Role::PURCHASING, 'description' => 'Supports orders that require purchasing missing materials.'],
            ['name' => Role::WAREHOUSE, 'description' => 'Processes and prepares orders.'],
            ['name' => Role::ROUTE, 'description' => 'Manages routed orders and delivery evidence.'],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
