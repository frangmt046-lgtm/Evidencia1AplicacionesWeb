<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_main_screen_is_displayed(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('HALCON')
            ->assertSee('Track Your Order')
            ->assertSee('Customer Number')
            ->assertSee('Invoice Number')
            ->assertSee('Check Order Status');
    }

    public function test_customer_gets_the_order_status(): void
    {
        $customer = Customer::factory()->create(['customer_number' => 'CUS-T0001']);
        Order::factory()->for($customer)->create([
            'invoice_number' => 'INV-90001',
            'status' => Order::STATUS_IN_ROUTE,
        ]);

        // lowercase and spaces are accepted
        $this->getJson('/api/track?customer_number=cus-t0001&invoice_number=%20inv-90001%20')
            ->assertOk()
            ->assertJsonPath('invoice_number', 'INV-90001')
            ->assertJsonPath('status', 'In route')
            ->assertJsonPath('status_step', 3)
            ->assertJsonPath('customer.customer_number', 'CUS-T0001')
            ->assertJsonCount(3, 'history')
            ->assertJsonPath('delivery_photo', null);
    }

    public function test_delivered_order_includes_the_delivery_photo(): void
    {
        $customer = Customer::factory()->create(['customer_number' => 'CUS-T0002']);
        Order::factory()->for($customer)->create([
            'invoice_number' => 'INV-90002',
            'status' => Order::STATUS_DELIVERED,
        ]);

        $this->getJson('/api/track?customer_number=CUS-T0002&invoice_number=INV-90002')
            ->assertOk()
            ->assertJsonPath('status', 'Delivered')
            ->assertJsonStructure(['delivery_photo' => ['url', 'uploaded_at']]);
    }

    public function test_invoice_of_another_customer_is_not_shown(): void
    {
        $owner = Customer::factory()->create(['customer_number' => 'CUS-T0003']);
        Customer::factory()->create(['customer_number' => 'CUS-T0004']);
        Order::factory()->for($owner)->create(['invoice_number' => 'INV-90003']);

        $this->getJson('/api/track?customer_number=CUS-T0004&invoice_number=INV-90003')
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    public function test_archived_orders_are_not_shown(): void
    {
        $customer = Customer::factory()->create(['customer_number' => 'CUS-T0005']);
        $order = Order::factory()->for($customer)->create(['invoice_number' => 'INV-90004']);
        $order->delete();

        $this->getJson('/api/track?customer_number=CUS-T0005&invoice_number=INV-90004')
            ->assertNotFound();
    }

    public function test_both_fields_are_required(): void
    {
        $this->getJson('/api/track')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['customer_number', 'invoice_number']);
    }
}
