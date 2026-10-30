<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * GET /api/users
     * Lists the employees (active AND inactive, 15 per page) with their role.
     * Optional filter: ?status=active or ?status=inactive
     */
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'sometimes|in:active,inactive',
        ]);

        return User::select('id', 'name', 'email', 'role_id', 'is_active', 'created_at', 'updated_at')
            ->with('role:id,name')
            ->when($request->query('status') === 'active', fn ($query) => $query->where('is_active', true))
            ->when($request->query('status') === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * POST /api/users
     * Registers a new employee and assigns its role. Returns 201.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|integer|exists:roles,id',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // refresh() reads the row again so the response includes is_active
        // (default true in the database) and the role name.
        return response()->json($user->refresh()->load('role:id,name'), 201);
    }

    /**
     * GET /api/users/{id}
     * Returns one employee with its role. 404 if it does not exist.
     */
    public function show(string $id)
    {
        return response()->json(User::with('role:id,name')->findOrFail($id));
    }

    /**
     * PUT/PATCH /api/users/{id}
     * Updates the basic information of an employee (name, email, password, role).
     * With PATCH only the fields that change are sent.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes', 'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => 'sometimes|required|string|min:8|confirmed',
            'role_id' => 'sometimes|required|integer|exists:roles,id',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json($user->load('role:id,name'));
    }

    /**
     * PATCH /api/users/{id}/deactivate
     * Changes the status of an employee to inactive. The user stays in the
     * database (with all its history) but can no longer register orders
     * or change order statuses.
     */
    public function deactivate(string $id)
    {
        $user = User::findOrFail($id);

        $user->update(['is_active' => false]);

        return response()->json([
            'message' => 'User deactivated successfully.',
            'data' => $user->load('role:id,name'),
        ]);
    }

    /**
     * PATCH /api/users/{id}/activate
     * Changes the status of an employee back to active.
     */
    public function activate(string $id)
    {
        $user = User::findOrFail($id);

        $user->update(['is_active' => true]);

        return response()->json([
            'message' => 'User activated successfully.',
            'data' => $user->load('role:id,name'),
        ]);
    }

    /**
     * DELETE /api/users/{id}
     * Physically deletes an employee without history. Returns 204 (no content).
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // orders, evidence_photos and status_histories have a foreign key to users.
        // Deleting a user with that history would break it, so the user must be
        // deactivated instead (PATCH /api/users/{id}/deactivate).
        $hasHistory = $user->orders()->withTrashed()->exists()
            || $user->evidencePhotos()->exists()
            || $user->statusHistories()->exists();

        if ($hasHistory) {
            return response()->json([
                'message' => 'The user has orders, photos or status changes. Deactivate it instead (PATCH /api/users/'.$user->id.'/deactivate).',
            ], 409);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
