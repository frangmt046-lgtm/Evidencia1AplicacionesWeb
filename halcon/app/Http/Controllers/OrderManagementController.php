<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\StatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderManagementController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $orders = Order::with('customer:id,customer_number,name', 'user:id,name')
            ->filter($filters)
            ->orderByDesc('ordered_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => Order::STATUSES,
            'canManage' => $request->user()->canManageOrders(),
        ]);
    }

    public function deleted(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $orders = Order::onlyTrashed()
            ->with('customer:id,customer_number,name', 'deletedBy:id,name')
            ->filter($filters)
            ->orderByDesc('deleted_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('orders.deleted', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function create()
    {
        return view('orders.create', [
            'order' => new Order(['ordered_at' => now(), 'status' => Order::STATUS_ORDERED]),
            'nextInvoice' => Order::nextInvoiceNumber(),
            'customers' => $this->customersForForm(),
            'editable' => ['customer', 'ordered_at', 'delivery_address', 'notes'],
            'allowedStatuses' => [],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(
            $this->customerRules() + $this->orderRules(),
            $this->messages()
        );

        $order = DB::transaction(function () use ($validated, $request) {
            $customer = $this->saveCustomer($validated);

            $order = Order::create([
                'invoice_number' => Order::nextInvoiceNumber(),
                'customer_id' => $customer->id,
                'user_id' => $request->user()->id,
                'delivery_address' => $validated['delivery_address'],
                'notes' => $validated['notes'] ?? null,
                'ordered_at' => $validated['ordered_at'],
                'status' => Order::STATUS_ORDERED,
            ]);

            StatusHistory::create([
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'previous_status' => null,
                'new_status' => Order::STATUS_ORDERED,
                'changed_at' => now(),
            ]);

            return $order;
        });

        return redirect()->route('admin.orders.show', $order)
            ->with('status', "Order {$order->invoice_number} created with status {$order->status}.");
    }

    public function show(Request $request, Order $order)
    {
        $this->ensureCanSee($request, $order);

        $order->load([
            'customer',
            'user:id,name',
            'deletedBy:id,name',
            'statusHistories' => fn ($query) => $query->orderBy('changed_at')->orderBy('id'),
            'statusHistories.user:id,name',
            'evidencePhotos',
        ]);

        return view('orders.show', [
            'order' => $order,
            'canManage' => $request->user()->canManageOrders(),
            'canEdit' => $request->user()->editableOrderFields($order) !== [],
        ]);
    }

    public function edit(Request $request, Order $order)
    {
        $this->ensureCanSee($request, $order);

        $editable = $request->user()->editableOrderFields($order);

        if ($editable === []) {
            abort(403, 'Your role cannot modify this order.');
        }

        return view('orders.edit', [
            'order' => $order->load('customer'),
            'customers' => $this->customersForForm(),
            'editable' => $editable,
            'allowedStatuses' => $request->user()->allowedOrderStatuses($order),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $this->ensureCanSee($request, $order);

        $user = $request->user();
        $editable = $user->editableOrderFields($order);

        if ($editable === []) {
            abort(403, 'Your role cannot modify this order.');
        }

        $rules = [];

        if (in_array('customer', $editable, true)) {
            $rules += $this->customerRules();
        }

        foreach (['ordered_at', 'delivery_address', 'notes'] as $field) {
            if (in_array($field, $editable, true)) {
                $rules[$field] = $this->orderRules()[$field];
            }
        }

        if (in_array('status', $editable, true)) {
            $rules['status'] = ['required', Rule::in([$order->status, ...$user->allowedOrderStatuses($order)])];
        }

        $validated = $request->validate($rules, $this->messages());

        DB::transaction(function () use ($order, $validated, $editable, $user) {
            $data = collect($validated)->only(['ordered_at', 'delivery_address', 'notes'])->all();

            if (in_array('customer', $editable, true)) {
                $data['customer_id'] = $this->saveCustomer($validated)->id;
            }

            if (isset($validated['status']) && $validated['status'] !== $order->status) {
                StatusHistory::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'previous_status' => $order->status,
                    'new_status' => $validated['status'],
                    'changed_at' => now(),
                ]);

                $data['status'] = $validated['status'];
            }

            $order->update($data);
        });

        return redirect()->route('admin.orders.show', $order)
            ->with('status', "Order {$order->invoice_number} updated.");
    }

    public function destroy(Request $request, Order $order)
    {
        $order->update(['deleted_by' => $request->user()->id]);
        $order->delete();

        return redirect()->route('admin.orders.index')
            ->with('status', "Order {$order->invoice_number} was deleted. You can restore it from Deleted Orders.");
    }

    public function restore(Order $order)
    {
        if (! $order->trashed()) {
            return redirect()->route('admin.orders.show', $order);
        }

        $order->restore();
        $order->update(['deleted_by' => null]);

        return redirect()->route('admin.orders.deleted')
            ->with('status', "Order {$order->invoice_number} was restored and is back in the active order list.");
    }

    private function ensureCanSee(Request $request, Order $order): void
    {
        if ($order->trashed() && ! $request->user()->canManageOrders()) {
            abort(404);
        }
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'invoice_number' => 'nullable|string|max:50',
            'customer_number' => 'nullable|string|max:50',
            'date' => 'nullable|date_format:Y-m-d',
            'status' => ['nullable', Rule::in(Order::STATUSES)],
        ]);
    }

    private function customerRules(): array
    {
        return [
            'customer_number' => 'required|string|max:50|regex:/^[A-Za-z0-9-]+$/',
            'customer_name' => 'required|string|max:255',
            'tax_id' => 'required|string|min:12|max:13',
            'fiscal_address' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
        ];
    }

    private function orderRules(): array
    {
        return [
            'ordered_at' => 'required|date',
            'delivery_address' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    private function messages(): array
    {
        return [
            'customer_number.regex' => 'The customer number can only contain letters, numbers and dashes.',
            'tax_id.min' => 'The RFC must have 12 or 13 characters.',
            'tax_id.max' => 'The RFC must have 12 or 13 characters.',
        ];
    }

    private function saveCustomer(array $validated): Customer
    {
        $customer = Customer::firstOrNew([
            'customer_number' => strtoupper(trim($validated['customer_number'])),
        ]);

        $customer->fill([
            'name' => $validated['customer_name'],
            'tax_id' => strtoupper(trim($validated['tax_id'])),
            'fiscal_address' => $validated['fiscal_address'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ])->save();

        return $customer;
    }

    private function customersForForm()
    {
        return Customer::orderBy('customer_number')
            ->get(['customer_number', 'name', 'tax_id', 'fiscal_address', 'email', 'phone'])
            ->keyBy('customer_number');
    }
}
