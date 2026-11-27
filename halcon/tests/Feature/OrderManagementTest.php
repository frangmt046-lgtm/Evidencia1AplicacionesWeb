<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function user(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    private function newOrderData(array $overrides = []): array
    {
        return array_merge([
            'customer_number' => 'cus-90001',
            'customer_name' => 'Constructora Jalisco SA de CV',
            'tax_id' => 'CJA010203AB1',
            'fiscal_address' => 'Av. Vallarta 1500, Guadalajara, Jal.',
            'email' => 'compras@constructorajalisco.mx',
            'phone' => '33 1234 5678',
            'ordered_at' => '2026-11-26T10:30',
            'delivery_address' => 'Calle Hidalgo 45, Zapopan, Jal.',
            'notes' => 'Deliver before noon',
        ], $overrides);
    }

    public function test_complete_order_management_flow(): void
    {
        $sales = $this->user('sales');

        $this->post('/login', ['login' => 'sales', 'password' => 'password'])->assertRedirect('/dashboard');

        $this->get('/dashboard/orders/create')->assertOk()->assertSee('INV-00051');

        $response = $this->post('/dashboard/orders', $this->newOrderData());

        $order = Order::where('invoice_number', 'INV-00051')->firstOrFail();
        $response->assertRedirect("/dashboard/orders/{$order->id}");

        $this->assertSame(Order::STATUS_ORDERED, $order->status);
        $this->assertSame($sales->id, $order->user_id);
        $this->assertSame('CUS-90001', $order->customer->customer_number);
        $this->assertDatabaseHas('status_histories', ['order_id' => $order->id, 'new_status' => 'Ordered']);

        $this->getJson('/api/track?customer_number=CUS-90001&invoice_number=INV-00051')
            ->assertOk()
            ->assertJsonPath('status', 'Ordered');

        $this->get('/dashboard/orders?invoice_number=INV-00051')
            ->assertOk()
            ->assertSee('INV-00051</strong>', false)
            ->assertDontSee('INV-00050</strong>', false);

        $this->get("/dashboard/orders/{$order->id}")
            ->assertOk()
            ->assertSee('Constructora Jalisco SA de CV')
            ->assertSee('CJA010203AB1');

        $this->put("/dashboard/orders/{$order->id}", $this->newOrderData(['notes' => 'Call before delivering']))
            ->assertRedirect("/dashboard/orders/{$order->id}");
        $this->assertSame('Call before delivering', $order->fresh()->notes);

        $this->delete("/dashboard/orders/{$order->id}")->assertRedirect('/dashboard/orders');
        $this->assertSoftDeleted('orders', ['id' => $order->id, 'deleted_by' => $sales->id]);
        $this->get('/dashboard/orders?invoice_number=INV-00051')->assertDontSee('INV-00051</strong>', false);
        $this->getJson('/api/track?customer_number=CUS-90001&invoice_number=INV-00051')->assertNotFound();

        $this->get('/dashboard/orders/deleted?invoice_number=INV-00051')->assertOk()->assertSee('INV-00051');

        $this->put("/dashboard/orders/{$order->id}", $this->newOrderData(['delivery_address' => 'Calle Morelos 12, Zapopan, Jal.']))
            ->assertRedirect();
        $this->assertSame('Calle Morelos 12, Zapopan, Jal.', Order::withTrashed()->find($order->id)->delivery_address);

        $this->patch("/dashboard/orders/{$order->id}/restore")->assertRedirect('/dashboard/orders/deleted');
        $this->assertNotSoftDeleted('orders', ['id' => $order->id]);
        $this->get('/dashboard/orders?invoice_number=INV-00051')->assertSee('INV-00051');
    }

    public function test_filters_by_customer_number_date_and_status(): void
    {
        $this->actingAs($this->user('admin'));

        $order = Order::with('customer')->where('invoice_number', 'INV-00001')->firstOrFail();

        $this->get('/dashboard/orders?customer_number='.$order->customer->customer_number)
            ->assertOk()->assertSee('INV-00001');

        $this->get('/dashboard/orders?date='.$order->ordered_at->format('Y-m-d'))
            ->assertOk()->assertSee('INV-00001');

        $other = collect(Order::STATUSES)->first(fn ($status) => $status !== $order->status);
        $this->get('/dashboard/orders?invoice_number=INV-00001&status='.urlencode($other))
            ->assertOk()->assertSee('No orders match the search.')->assertDontSee('INV-00001</strong>', false);

        $this->get('/dashboard/orders?date=26-11-2026')->assertSessionHasErrors('date');
    }

    public function test_new_orders_always_start_as_ordered(): void
    {
        $this->actingAs($this->user('sales'))
            ->post('/dashboard/orders', $this->newOrderData(['status' => 'Delivered']));

        $this->assertSame('Ordered', Order::latest('id')->first()->status);
    }

    public function test_existing_customer_is_reused(): void
    {
        $customer = Customer::first();

        $this->actingAs($this->user('sales'))->post('/dashboard/orders', $this->newOrderData([
            'customer_number' => $customer->customer_number,
            'customer_name' => $customer->name,
        ]));

        $this->assertSame($customer->id, Order::latest('id')->first()->customer_id);
        $this->assertSame(20, Customer::count());
    }

    public function test_create_validates_required_fields(): void
    {
        $this->actingAs($this->user('sales'))
            ->post('/dashboard/orders', [])
            ->assertSessionHasErrors(['customer_number', 'customer_name', 'tax_id', 'fiscal_address', 'delivery_address', 'ordered_at']);
    }

    public function test_only_sales_and_administrator_can_create_delete_and_restore(): void
    {
        $order = Order::first();
        $deleted = Order::onlyTrashed()->first();

        foreach (['warehouse', 'route', 'purchasing'] as $username) {
            $this->actingAs($this->user($username));
            $this->get('/dashboard/orders')->assertOk();
            $this->get('/dashboard/orders/create')->assertForbidden();
            $this->post('/dashboard/orders', $this->newOrderData())->assertForbidden();
            $this->delete("/dashboard/orders/{$order->id}")->assertForbidden();
            $this->get('/dashboard/orders/deleted')->assertForbidden();
            $this->patch("/dashboard/orders/{$deleted->id}/restore")->assertForbidden();
            $this->get("/dashboard/orders/{$deleted->id}")->assertNotFound();
        }

        $this->actingAs($this->user('admin'))->delete("/dashboard/orders/{$order->id}")->assertRedirect();
        $this->assertSoftDeleted($order);
    }

    public function test_each_role_can_only_modify_its_authorized_fields(): void
    {
        $order = Order::where('status', Order::STATUS_ORDERED)->firstOrFail();
        $original = $order->delivery_address;

        $this->actingAs($this->user('warehouse'))
            ->put("/dashboard/orders/{$order->id}", ['status' => 'In process', 'delivery_address' => 'Hacked 1'])
            ->assertRedirect();
        $order->refresh();
        $this->assertSame('In process', $order->status);
        $this->assertSame($original, $order->delivery_address);

        $this->actingAs($this->user('warehouse'))
            ->put("/dashboard/orders/{$order->id}", ['status' => 'Delivered'])
            ->assertSessionHasErrors('status');

        $this->actingAs($this->user('purchasing'))
            ->put("/dashboard/orders/{$order->id}", ['notes' => 'Material purchased', 'status' => 'In route'])
            ->assertRedirect();
        $order->refresh();
        $this->assertSame('Material purchased', $order->notes);
        $this->assertSame('In process', $order->status);

        $this->actingAs($this->user('route'))->get("/dashboard/orders/{$order->id}/edit")->assertForbidden();
    }

    public function test_guests_cannot_open_order_management(): void
    {
        $this->get('/dashboard/orders')->assertRedirect('/login');
        $this->get('/dashboard/orders/deleted')->assertRedirect('/login');
    }
}
