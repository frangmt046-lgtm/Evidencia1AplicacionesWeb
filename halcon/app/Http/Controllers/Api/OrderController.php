<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Relations returned with a single order.
     */
    private const DETAIL_RELATIONS = [
        'customer:id,customer_number,name,email,phone',
        'user:id,name,email,role_id',
        'statusHistories:id,order_id,user_id,previous_status,new_status,changed_at',
        'statusHistories.user:id,name',
        'evidencePhotos:id,order_id,user_id,type,image_path,uploaded_at',
    ];

    /**
     * GET /api/orders
     * Lists the active (not archived) orders from newest to oldest, 15 per page.
     * Optional filters: ?status=In process  ?customer_number=CUS-12345  ?date=2026-10-28
     */
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['sometimes', Rule::in(Order::STATUSES)],
            'customer_number' => 'sometimes|string',
            'date' => 'sometimes|date_format:Y-m-d',
        ]);

        return Order::with('customer:id,customer_number,name', 'user:id,name')
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('customer_number'), fn ($query, $number) => $query->whereHas(
                'customer', fn ($customer) => $customer->where('customer_number', $number)
            ))
            ->when($request->query('date'), fn ($query, $date) => $query->whereDate('ordered_at', $date))
            ->orderByDesc('ordered_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * GET /api/orders/archived
     * Lists the logically deleted (archived) orders, most recently archived first.
     */
    public function archived()
    {
        return Order::onlyTrashed()
            ->with('customer:id,customer_number,name', 'user:id,name')
            ->orderByDesc('deleted_at')
            ->orderByDesc('id')
            ->paginate(15);
    }

    /**
     * GET /api/orders/{id}
     * Returns one order with its customer, the user who registered it,
     * its status history and its evidence photos. 404 if it does not exist
     * or if it was archived.
     */
    public function show(string $id)
    {
        return response()->json(Order::with(self::DETAIL_RELATIONS)->findOrFail($id));
    }

    /**
     * GET /api/orders/invoice/{invoice}
     * Searches an order by its invoice number (for example INV-00001).
     */
    public function showByInvoice(string $invoice)
    {
        $order = Order::with(self::DETAIL_RELATIONS)
            ->where('invoice_number', $invoice)
            ->first();

        if (! $order) {
            return response()->json([
                'message' => "No order found with invoice number {$invoice}.",
            ], 404);
        }

        return response()->json($order);
    }

    /**
     * POST /api/orders
     * Registers a new order. It always starts with the status "Ordered" and
     * the first row of its status history is created automatically.
     * If invoice_number is not sent, the next consecutive number is generated.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'sometimes|string|max:50|unique:orders,invoice_number',
            'customer_id' => 'required|integer|exists:customers,id',
            'user_id' => 'required|integer|exists:users,id',
            'delivery_address' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'ordered_at' => 'sometimes|date',
        ]);

        $this->ensureUserIsActive($validated['user_id'], 'user_id');

        $order = DB::transaction(function () use ($validated) {
            $order = Order::create([
                ...$validated,
                'invoice_number' => $validated['invoice_number'] ?? $this->nextInvoiceNumber(),
                'ordered_at' => $validated['ordered_at'] ?? now(),
                'status' => Order::STATUS_ORDERED,
            ]);

            StatusHistory::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'previous_status' => null,
                'new_status' => Order::STATUS_ORDERED,
                'changed_at' => now(),
            ]);

            return $order;
        });

        return response()->json($order->load(self::DETAIL_RELATIONS), 201);
    }

    /**
     * PUT/PATCH /api/orders/{id}
     * Updates the information of an order. With PATCH only the fields that
     * change are sent. The status can also be changed here, sending "status"
     * together with "changed_by" (id of the user who makes the change).
     */
    public function update(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'invoice_number' => [
                'sometimes', 'required', 'string', 'max:50',
                Rule::unique('orders', 'invoice_number')->ignore($order->id),
            ],
            'customer_id' => 'sometimes|required|integer|exists:customers,id',
            'delivery_address' => 'sometimes|required|string|max:255',
            'notes' => 'sometimes|nullable|string|max:1000',
            'ordered_at' => 'sometimes|required|date',
            'status' => ['sometimes', 'required', Rule::in(Order::STATUSES)],
            'changed_by' => 'required_with:status|integer|exists:users,id',
        ]);

        $newStatus = $validated['status'] ?? null;
        $changedBy = $validated['changed_by'] ?? null;
        unset($validated['status'], $validated['changed_by']);

        DB::transaction(function () use ($order, $validated, $newStatus, $changedBy) {
            $order->update($validated);

            if ($newStatus !== null && $newStatus !== $order->status) {
                $this->changeStatus($order, $newStatus, $changedBy);
            }
        });

        return response()->json($order->fresh(self::DETAIL_RELATIONS));
    }

    /**
     * PATCH /api/orders/{id}/status
     * Changes the status of an order and records the change in its history.
     * Body: {"status": "In process", "changed_by": 3}
     */
    public function updateStatus(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
            'changed_by' => 'required|integer|exists:users,id',
        ]);

        DB::transaction(fn () => $this->changeStatus($order, $validated['status'], $validated['changed_by']));

        return response()->json([
            'message' => "Order status changed to {$validated['status']}.",
            'data' => $order->fresh(self::DETAIL_RELATIONS),
        ]);
    }

    /**
     * DELETE /api/orders/{id}
     * Logical delete (archive): the order stays in the database with deleted_at
     * filled, it disappears from the normal list and appears in /archived.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);

        $order->delete();

        return response()->json([
            'message' => 'Order archived successfully (logical delete).',
            'data' => $order->only('id', 'invoice_number', 'status', 'deleted_at'),
        ]);
    }

    /**
     * PATCH /api/orders/{id}/restore
     * Restores an archived order so it appears again in the normal list.
     */
    public function restore(string $id)
    {
        $order = Order::onlyTrashed()->findOrFail($id);

        $order->restore();

        return response()->json([
            'message' => 'Order restored successfully.',
            'data' => $order->fresh(self::DETAIL_RELATIONS),
        ]);
    }

    /**
     * Applies the status business rules and writes the status history row.
     */
    private function changeStatus(Order $order, string $newStatus, int $changedBy): void
    {
        $this->ensureUserIsActive($changedBy, 'changed_by');

        if ($newStatus === $order->status) {
            throw ValidationException::withMessages([
                'status' => "The order is already in status {$newStatus}.",
            ]);
        }

        $allowed = $order->nextStatus();

        if ($newStatus !== $allowed) {
            throw ValidationException::withMessages([
                'status' => $allowed === null
                    ? 'The order was already delivered; its status can no longer change.'
                    : "Invalid status change: an order in status {$order->status} can only move to {$allowed}.",
            ]);
        }

        $previous = $order->status;
        $order->update(['status' => $newStatus]);

        StatusHistory::create([
            'order_id' => $order->id,
            'user_id' => $changedBy,
            'previous_status' => $previous,
            'new_status' => $newStatus,
            'changed_at' => now(),
        ]);
    }

    /**
     * Business rule: inactive users cannot register orders or change statuses.
     */
    private function ensureUserIsActive(int $userId, string $field): void
    {
        if (! User::whereKey($userId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                $field => 'The selected user is inactive and cannot perform this action.',
            ]);
        }
    }

    /**
     * Next consecutive invoice number: INV-00051, INV-00052, ...
     * Archived orders are included so a number is never reused.
     */
    private function nextInvoiceNumber(): string
    {
        $max = Order::withTrashed()
            ->where('invoice_number', 'like', 'INV-%')
            ->pluck('invoice_number')
            ->map(fn ($number) => (int) substr($number, 4))
            ->max() ?? 0;

        return 'INV-'.str_pad($max + 1, 5, '0', STR_PAD_LEFT);
    }
}
