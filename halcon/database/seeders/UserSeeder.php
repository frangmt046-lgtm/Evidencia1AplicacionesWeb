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
     * They can log in with the username or with the email.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Administrator', 'username' => 'admin', 'email' => 'admin@halcon.com', 'role' => Role::ADMINISTRATOR],
            ['name' => 'Laura Méndez', 'username' => 'sales', 'email' => 'sales@halcon.com', 'role' => Role::SALES],
            ['name' => 'Jorge Ramírez', 'username' => 'warehouse', 'email' => 'warehouse@halcon.com', 'role' => Role::WAREHOUSE],
            ['name' => 'Carlos Torres', 'username' => 'route', 'email' => 'route@halcon.com', 'role' => Role::ROUTE],
            ['name' => 'Ana López', 'username' => 'purchasing', 'email' => 'purchasing@halcon.com', 'role' => Role::PURCHASING],
            // Former employees: kept in the database but inactive (they cannot log in)
            ['name' => 'Miguel Herrera', 'username' => 'miguel.herrera', 'email' => 'miguel.herrera@halcon.com', 'role' => Role::SALES, 'active' => false],
            ['name' => 'Sofía Castillo', 'username' => 'sofia.castillo', 'email' => 'sofia.castillo@halcon.com', 'role' => Role::ROUTE, 'active' => false],
        ];

        foreach ($users as $user) {
            User::create([
                'name' => $user['name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'password' => 'password',
                'role_id' => Role::where('name', $user['role'])->value('id'),
                'is_active' => $user['active'] ?? true,
            ]);
        }
    }
}
