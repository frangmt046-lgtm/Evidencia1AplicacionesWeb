<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * User management screens of the Administrative Dashboard.
 * All routes of this controller have the middleware role:Administrator.
 */
class UserManagementController extends Controller
{
    /**
     * GET /dashboard/users
     * List of employees with search (?search=) and status filter (?status=active|inactive).
     */
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive',
            'role' => 'nullable|integer|exists:roles,id',
        ]);

        $users = User::with('role')
            ->when($request->query('search'), function ($query, $search) {
                $query->where(fn ($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->query('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->query('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($request->query('role'), fn ($query, $role) => $query->where('role_id', $role))
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    /**
     * GET /dashboard/users/create
     */
    public function create()
    {
        return view('admin.users.create', [
            'user' => new User(['is_active' => true]),
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    /**
     * POST /dashboard/users
     * Registers a new employee with its role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(), $this->messages());
        $validated['is_active'] = $request->boolean('is_active');

        // The "hashed" cast of the User model encrypts the password
        $user = User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('status', "Employee {$user->name} registered with role {$user->role->name}.");
    }

    /**
     * GET /dashboard/users/{user}/edit
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    /**
     * PUT /dashboard/users/{user}
     * Edits an employee. The password only changes when a new one is typed.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate($this->rules($user), $this->messages());
        $validated['is_active'] = $request->boolean('is_active');

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        // The Administrator cannot lock themself out of the system
        if ($user->is($request->user())) {
            $adminRoleId = (int) Role::where('name', Role::ADMINISTRATOR)->value('id');

            if ((int) $validated['role_id'] !== $adminRoleId || ! $validated['is_active']) {
                return back()->withInput()->withErrors([
                    'role_id' => 'You cannot remove your own Administrator role or deactivate your own account.',
                ]);
            }
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('status', "Employee {$user->name} updated.");
    }

    /**
     * PATCH /dashboard/users/{user}/toggle-status
     * Activates or deactivates an employee. Users are never deleted, so their
     * order history is kept.
     */
    public function toggleStatus(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['status' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', $user->is_active
            ? "Employee {$user->name} activated."
            : "Employee {$user->name} deactivated. They can no longer log in.");
    }

    private function rules(?User $user = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'username' => [
                'required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($user?->id),
            ],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            // Required when registering, optional when editing
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'role_id' => 'required|integer|exists:roles,id',
            'is_active' => 'sometimes|boolean',
        ];
    }

    private function messages(): array
    {
        return [
            'username.regex' => 'The username can only contain letters, numbers, dots, dashes and underscores.',
            'role_id.required' => 'Select a role for the employee.',
        ];
    }
}
