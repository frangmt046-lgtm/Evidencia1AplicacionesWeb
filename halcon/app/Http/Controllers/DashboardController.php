<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /dashboard
     * Shows the logged-in user's name and role and the modules of that role.
     */
    public function index(Request $request)
    {
        $user = $request->user()->load('role');

        $modules = self::modulesFor($user);

        // Small summary cards (only numbers, no restricted data)
        $stats = [
            'orders' => Order::count(),
            'in_route' => Order::where('status', Order::STATUS_IN_ROUTE)->count(),
            'delivered' => Order::where('status', Order::STATUS_DELIVERED)->count(),
        ];

        if ($user->isAdministrator()) {
            $stats['active_users'] = User::where('is_active', true)->count();
        }

        return view('dashboard', compact('user', 'modules', 'stats'));
    }

    /**
     * Modules from config/halcon.php that the user's role can open.
     * Also used by the layout to build the side menu.
     */
    public static function modulesFor(User $user): array
    {
        return collect(config('halcon.modules'))
            ->filter(fn (array $module) => $user->hasRole(...$module['roles']))
            ->map(function (array $module, string $key) {
                $module['key'] = $key;
                $module['url'] = $module['route'] === 'modules.show'
                    ? route('modules.show', $key)
                    : route($module['route']);

                return $module;
            })
            ->all();
    }
}
