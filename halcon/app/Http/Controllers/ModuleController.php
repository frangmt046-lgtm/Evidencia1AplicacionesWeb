<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Order modules of the dashboard (Sales, Purchasing, Warehouse, Route).
 * Each one lists the orders that department works with. The actions of each
 * department (create orders, change status, upload photos) already exist in
 * the REST API and will be connected to these screens in the next activities.
 */
class ModuleController extends Controller
{
    /**
     * GET /dashboard/modules/{module}
     */
    public function show(Request $request, string $module)
    {
        $config = config("halcon.modules.{$module}");

        // "users" has its own controller; unknown modules → 404
        if (! $config || ! array_key_exists('statuses', $config)) {
            abort(404);
        }

        // Role-based access: the module is only for the roles in its list
        if (! $request->user()->hasRole(...$config['roles'])) {
            abort(403, 'Your role does not have access to this module.');
        }

        $orders = Order::with('customer:id,customer_number,name', 'user:id,name')
            ->withCount('evidencePhotos')
            ->when($config['statuses'], fn ($query, $statuses) => $query->whereIn('status', $statuses))
            ->orderByDesc('ordered_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('modules.orders', [
            'module' => $config + ['key' => $module],
            'orders' => $orders,
        ]);
    }
}
