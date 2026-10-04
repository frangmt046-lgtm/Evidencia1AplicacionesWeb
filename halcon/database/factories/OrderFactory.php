<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\EvidencePhoto;
use App\Models\Order;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'invoice_number' => 'INV-' . fake()->unique()->numerify('#####'),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'delivery_address' => str_replace("\n", ', ', fake()->address()),
            'notes' => fake()->optional(0.6)->sentence(),   // 60% of orders have notes
            'ordered_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'status' => fake()->randomElement(Order::STATUSES),
        ];
    }

    /**
     * After an order is created, fill the tables that depend on it so the
     * data is consistent with its status:
     *  - status_histories: one row for each status the order went through.
     *  - evidence_photos: loading photo when "In route", loading + delivery when "Delivered".
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            $warehouseUser = $this->userWithRole('Warehouse') ?? $order->user;
            $routeUser = $this->userWithRole('Route') ?? $order->user;

            // Who performs each step of the order life cycle
            $steps = [
                Order::STATUS_ORDERED => $order->user,
                Order::STATUS_IN_PROCESS => $warehouseUser,
                Order::STATUS_IN_ROUTE => $warehouseUser,
                Order::STATUS_DELIVERED => $routeUser,
            ];

            $date = Carbon::parse($order->ordered_at);
            $previous = null;

            foreach ($steps as $status => $user) {
                StatusHistory::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'previous_status' => $previous,
                    'new_status' => $status,
                    'changed_at' => $date,
                ]);

                if ($status === Order::STATUS_IN_ROUTE) {
                    $this->createPhoto($order, $routeUser, EvidencePhoto::TYPE_LOADING, $date);
                }

                if ($status === Order::STATUS_DELIVERED) {
                    $this->createPhoto($order, $routeUser, EvidencePhoto::TYPE_DELIVERY, $date);
                }

                if ($status === $order->status) {
                    break;  // stop at the order's current status
                }

                $previous = $status;
                $date = $date->copy()->addHours(fake()->numberBetween(2, 36));
            }
        });
    }

    private function userWithRole(string $role): ?User
    {
        return User::whereHas('role', fn ($query) => $query->where('name', $role))
            ->inRandomOrder()
            ->first();
    }

    private function createPhoto(Order $order, User $user, string $type, Carbon $date): void
    {
        EvidencePhoto::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'type' => $type,
            'image_path' => 'evidence/' . $type . '/' . fake()->uuid() . '.jpg',
            'uploaded_at' => $date,
        ]);
    }
}
