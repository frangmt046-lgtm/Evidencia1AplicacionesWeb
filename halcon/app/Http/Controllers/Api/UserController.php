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
     * Lists the employees (15 per page) with their role (department).
     */
    public function index()
    {
        return User::select('id', 'name', 'email', 'role_id', 'is_active', 'created_at')
            ->with('role:id,name')
            ->orderBy('id')
            ->paginate(15);
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
     * Updates an employee. With PATCH only the fields that change are sent
     * (for example {"is_active": false} to deactivate it).
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
     * DELETE /api/users/{id}
     * Deletes an employee. Returns 204 (no content).
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        // orders, evidence_photos and status_histories have a foreign key to users.
        // Deleting a user with that history would break it (500 error), so the
        // user must be deactivated instead (PATCH with "is_active": false).
        $hasHistory = $user->orders()->withTrashed()->exists()
            || $user->evidencePhotos()->exists()
            || $user->statusHistories()->exists();

        if ($hasHistory) {
            return response()->json([
                'message' => 'The user has orders, photos or status changes. Deactivate it instead (is_active = false).',
            ], 409);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
