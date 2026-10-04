<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seeders: departments (roles) and users
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        // 2. Factories: customers, then 50 orders
        $customers = Customer::factory(20)->create();

        $salesUsers = User::whereHas('role', fn ($query) => $query->where('name', 'Sales'))->get();

        Order::factory(50)
            ->recycle($customers)    // each order uses one of the 20 customers
            ->recycle($salesUsers)   // each order is registered by a Sales user
            ->sequence(fn (Sequence $sequence) => [
                // consecutive invoice numbers: INV-00001, INV-00002, ...
                'invoice_number' => 'INV-' . str_pad($sequence->index + 1, 5, '0', STR_PAD_LEFT),
            ])
            ->create();
    }
}
