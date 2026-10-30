<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * The default administrator required by the case, four active employees
     * and two inactive ones (to test the active/inactive list).
     * Password for every user: "password" (the User model hashes it automatically).
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Administrator', 'email' => 'admin@halcon.com', 'role' => 'Admin'],
            ['name' => 'Laura Méndez', 'email' => 'sales@halcon.com', 'role' => 'Sales'],
            ['name' => 'Jorge Ramírez', 'email' => 'warehouse@halcon.com', 'role' => 'Warehouse'],
            ['name' => 'Carlos Torres', 'email' => 'route@halcon.com', 'role' => 'Route'],
            ['name' => 'Ana López', 'email' => 'purchasing@halcon.com', 'role' => 'Purchasing'],
            // Former employees: kept in the database but inactive
            ['name' => 'Miguel Herrera', 'email' => 'miguel.herrera@halcon.com', 'role' => 'Sales', 'active' => false],
            ['name' => 'Sofía Castillo', 'email' => 'sofia.castillo@halcon.com', 'role' => 'Route', 'active' => false],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => 'password',
                'role_id' => Role::where('name', $user['role'])->value('id'),
                'is_active' => $user['active'] ?? true,
            ]);
        }
    }
}
