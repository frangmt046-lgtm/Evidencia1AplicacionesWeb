<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_lists_orders_from_newest_to_oldest_without_archived(): void
    {
        $response = $this->getJson('/api/orders')
            ->assertOk()
            ->assertJsonPath('total', 45);

        $dates = collect($response->json('data'))->pluck('ordered_at');
        $this->assertEquals($dates->sortDesc()->values()->all(), $dates->all());

        $this->getJson('/api/orders/archived')
            ->assertOk()
            ->assertJsonPath('total', 5);
    }

    public function test_searches_by_invoice_number(): void
    {
        $this->getJson('/api/orders/invoice/INV-00001')
            ->assertOk()
            ->assertJsonPath('invoice_number', 'INV-00001')
            ->assertJsonStructure(['customer', 'user', 'status_histories', 'evidence_photos']);

        $this->getJson('/api/orders/invoice/INV-99999')->assertNotFound();
    }

    public function test_complete_order_flow(): void
    {
        // 1. Create a user
        $userId = $this->postJson('/api/users', [
            'name' => 'Pedro Gómez',
            'email' => 'pedro.gomez@halcon.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 2,
        ])->assertCreated()->json('id');

        // 2. Create an order with that user
        $order = $this->postJson('/api/orders', [
            'customer_id' => 1,
            'user_id' => $userId,
            'delivery_address' => 'Av. Juárez 123, Guadalajara, Jal.',
            'notes' => 'Deliver in the morning',
        ])->assertCreated()
            ->assertJsonPath('status', 'Ordered')
            ->assertJsonPath('invoice_number', 'INV-00051')
            ->assertJsonPath('user.id', $userId)
            ->assertJsonCount(1, 'status_histories')
            ->json();

        // The new order is the first one in the list (newest to oldest)
        $this->getJson('/api/orders')->assertJsonPath('data.0.id', $order['id']);

        // 3. Retrieve it
        $this->getJson("/api/orders/{$order['id']}")
            ->assertOk()
            ->assertJsonPath('delivery_address', 'Av. Juárez 123, Guadalajara, Jal.');

        // 4. Update information and status
        $this->patchJson("/api/orders/{$order['id']}", [
            'notes' => 'Call before delivering',
            'status' => 'In process',
            'changed_by' => $userId,
        ])->assertOk()
            ->assertJsonPath('notes', 'Call before delivering')
            ->assertJsonPath('status', 'In process');

        $this->patchJson("/api/orders/{$order['id']}/status", [
            'status' => 'In route',
            'changed_by' => 4,
        ])->assertOk()
            ->assertJsonPath('data.status', 'In route');

        // 5. Retrieve again: changes saved
        $this->getJson("/api/orders/{$order['id']}")
            ->assertJsonPath('status', 'In route')
            ->assertJsonCount(3, 'status_histories');

        // 6. Archive
        $this->deleteJson("/api/orders/{$order['id']}")
            ->assertOk()
            ->assertJsonPath('data.id', $order['id']);

        // 7. It is archived: 404 on show, listed in archived, still in the DB
        $this->getJson("/api/orders/{$order['id']}")->assertNotFound();
        $this->getJson('/api/orders/archived')->assertJsonPath('data.0.id', $order['id']);
        $this->assertSoftDeleted('orders', ['id' => $order['id']]);

        // Restore
        $this->patchJson("/api/orders/{$order['id']}/restore")->assertOk();
        $this->getJson("/api/orders/{$order['id']}")->assertOk();
    }

    public function test_status_business_rules(): void
    {
        $order = Order::where('status', 'Ordered')->firstOrFail();

        // Cannot skip steps
        $this->patchJson("/api/orders/{$order->id}/status", ['status' => 'Delivered', 'changed_by' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');

        // Inactive users cannot change statuses
        $this->patchJson("/api/orders/{$order->id}/status", ['status' => 'In process', 'changed_by' => 6])
            ->assertStatus(422)
            ->assertJsonValidationErrors('changed_by');

        // Inactive users cannot create orders
        $this->postJson('/api/orders', [
            'customer_id' => 1,
            'user_id' => 6,
            'delivery_address' => 'Somewhere 1',
        ])->assertStatus(422)->assertJsonValidationErrors('user_id');
    }
}
