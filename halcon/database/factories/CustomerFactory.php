<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        // Half of the customers are companies, half are individual persons
        $name = fake()->boolean() ? fake()->company() : fake()->name();

        return [
            'customer_number' => 'CUS-' . fake()->unique()->numerify('#####'),
            'name' => $name,
            // Mexican RFC format: 4 letters + 6 digits (date) + 3 alphanumeric characters
            'tax_id' => fake()->regexify('[A-Z]{4}[0-9]{6}[A-Z0-9]{3}'),
            'fiscal_address' => str_replace("\n", ', ', fake()->address()),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
        ];
    }
}
