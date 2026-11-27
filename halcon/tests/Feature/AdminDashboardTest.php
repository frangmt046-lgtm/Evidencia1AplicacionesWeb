<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Activity 13: Administrative Dashboard, users and roles.
 */
class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function admin(): User
    {
        return User::where('email', 'admin@halcon.com')->firstOrFail();
    }

    private function sales(): User
    {
        return User::where('email', 'sales@halcon.com')->firstOrFail();
    }

    // ---------- Login ----------

    public function test_login_screen_is_shown(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Username or email')
            ->assertSee('Password')
            ->assertSee('Log in');
    }

    public function test_default_administrator_logs_in_with_email_and_sees_dashboard(): void
    {
        $this->post('/login', ['login' => 'admin@halcon.com', 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($this->admin());

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Welcome, Administrator')
            ->assertSee(Role::ADMINISTRATOR)
            ->assertSee('User Management');
    }

    public function test_employee_logs_in_with_username(): void
    {
        $this->post('/login', ['login' => 'sales', 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($this->sales());
    }

    public function test_incorrect_credentials_show_an_error(): void
    {
        $this->from('/login')
            ->post('/login', ['login' => 'admin', 'password' => 'wrong-password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['login' => 'Incorrect username/email or password.']);

        $this->from('/login')
            ->post('/login', ['login' => 'nobody@halcon.com', 'password' => 'password'])
            ->assertSessionHasErrors('login');

        $this->post('/login', [])->assertSessionHasErrors(['login', 'password']);

        $this->assertGuest();
    }

    public function test_inactive_employee_cannot_log_in(): void
    {
        $this->post('/login', ['login' => 'miguel.herrera', 'password' => 'password'])
            ->assertSessionHasErrors(['login' => 'Your account is deactivated. Contact the Administrator.']);

        $this->assertGuest();
    }

    public function test_logout(): void
    {
        $this->actingAs($this->admin())
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    // ---------- Access control ----------

    public function test_guests_cannot_open_the_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/dashboard/users')->assertRedirect('/login');
        $this->get('/dashboard/modules/sales')->assertRedirect('/login');
    }

    public function test_customers_cannot_register(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', ['name' => 'Customer', 'email' => 'c@x.com'])->assertNotFound();
    }

    public function test_dashboard_shows_only_the_modules_of_the_role(): void
    {
        $this->actingAs($this->sales())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Laura Méndez')
            ->assertSee(Role::SALES)
            ->assertSee('Order Management')
            ->assertDontSee('User Management')
            ->assertDontSee('data-module="warehouse"', false);
    }

    public function test_non_administrators_cannot_manage_users(): void
    {
        $sales = $this->sales();

        $this->actingAs($sales)->get('/dashboard/users')->assertForbidden();
        $this->actingAs($sales)->get('/dashboard/users/create')->assertForbidden();
        $this->actingAs($sales)->post('/dashboard/users', [])->assertForbidden();
        $this->actingAs($sales)->patch('/dashboard/users/1/toggle-status')->assertForbidden();
    }

    public function test_each_role_only_opens_its_own_module(): void
    {
        $warehouse = User::where('username', 'warehouse')->first();

        $this->actingAs($warehouse)->get('/dashboard/modules/warehouse')->assertOk()->assertSee('Warehouse');
        $this->actingAs($warehouse)->get('/dashboard/modules/route')->assertForbidden();
        $this->actingAs($warehouse)->get('/dashboard/modules/sales')->assertForbidden();
        $this->actingAs($warehouse)->get('/dashboard/modules/unknown')->assertNotFound();

        // The Administrator supervises every module
        $this->actingAs($this->admin())->get('/dashboard/modules/route')->assertOk();
    }

    public function test_employee_deactivated_during_session_is_logged_out(): void
    {
        $sales = $this->sales();
        $this->actingAs($sales);

        $sales->update(['is_active' => false]);

        $this->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    // ---------- User management ----------

    public function test_administrator_lists_users(): void
    {
        $this->actingAs($this->admin())
            ->get('/dashboard/users')
            ->assertOk()
            ->assertSee('admin@halcon.com')
            ->assertSee('miguel.herrera@halcon.com')
            ->assertSee('Inactive');

        $this->actingAs($this->admin())
            ->get('/dashboard/users?status=inactive')
            ->assertOk()
            ->assertSee('Miguel Herrera')
            ->assertDontSee('Laura Méndez');
    }

    public function test_administrator_registers_an_employee_who_can_then_log_in(): void
    {
        $warehouseRole = Role::where('name', Role::WAREHOUSE)->value('id');

        $this->actingAs($this->admin())
            ->get('/dashboard/users/create')
            ->assertOk()
            ->assertSee('Register employee');

        $this->actingAs($this->admin())
            ->post('/dashboard/users', [
                'name' => 'Pedro Gómez',
                'username' => 'pedro.gomez',
                'email' => 'pedro.gomez@halcon.com',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'role_id' => $warehouseRole,
                'is_active' => '1',
            ])
            ->assertRedirect('/dashboard/users')
            ->assertSessionHas('status');

        $pedro = User::where('username', 'pedro.gomez')->firstOrFail();
        $this->assertSame(Role::WAREHOUSE, $pedro->roleName());
        $this->assertTrue($pedro->is_active);

        // The new employee logs in with their own credentials
        $this->post('/logout');
        $this->post('/login', ['login' => 'pedro.gomez', 'password' => 'secret123'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($pedro);

        $this->get('/dashboard')
            ->assertSee('Pedro Gómez')
            ->assertSee(Role::WAREHOUSE)
            ->assertDontSee('User Management');
    }

    public function test_register_validation(): void
    {
        $this->actingAs($this->admin())
            ->post('/dashboard/users', [
                'name' => '',
                'username' => 'admin',               // already exists
                'email' => 'sales@halcon.com',       // already exists
                'password' => '123',
                'password_confirmation' => '456',
                'role_id' => '',
            ])
            ->assertSessionHasErrors(['name', 'username', 'email', 'password', 'role_id']);
    }

    public function test_administrator_edits_an_employee_and_changes_the_role(): void
    {
        $sales = $this->sales();
        $routeRole = Role::where('name', Role::ROUTE)->value('id');

        $this->actingAs($this->admin())
            ->get("/dashboard/users/{$sales->id}/edit")
            ->assertOk()
            ->assertSee('Laura Méndez');

        $this->actingAs($this->admin())
            ->put("/dashboard/users/{$sales->id}", [
                'name' => 'Laura Méndez Ruiz',
                'username' => 'sales',
                'email' => 'sales@halcon.com',
                'password' => '',
                'password_confirmation' => '',
                'role_id' => $routeRole,
                'is_active' => '1',
            ])
            ->assertRedirect('/dashboard/users');

        $sales->refresh();
        $this->assertSame('Laura Méndez Ruiz', $sales->name);
        $this->assertSame(Role::ROUTE, $sales->roleName());

        // Empty password = the old password still works
        $this->post('/logout');
        $this->post('/login', ['login' => 'sales', 'password' => 'password'])->assertRedirect('/dashboard');
    }

    public function test_administrator_activates_and_deactivates_users(): void
    {
        $sales = $this->sales();

        $this->actingAs($this->admin())
            ->patch("/dashboard/users/{$sales->id}/toggle-status")
            ->assertSessionHas('status');
        $this->assertFalse($sales->refresh()->is_active);

        $this->actingAs($this->admin())->patch("/dashboard/users/{$sales->id}/toggle-status");
        $this->assertTrue($sales->refresh()->is_active);
    }

    public function test_administrator_cannot_lock_themself_out(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->patch("/dashboard/users/{$admin->id}/toggle-status")
            ->assertSessionHasErrors('status');
        $this->assertTrue($admin->refresh()->is_active);

        $this->actingAs($admin)
            ->put("/dashboard/users/{$admin->id}", [
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@halcon.com',
                'role_id' => Role::where('name', Role::SALES)->value('id'),
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('role_id');
        $this->assertTrue($admin->refresh()->isAdministrator());
    }
}
