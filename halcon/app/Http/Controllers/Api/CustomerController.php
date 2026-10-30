<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

/**
 * Support endpoints so the frontend can choose the customer_id
 * that is required to create an order.
 */
class CustomerController extends Controller
{
    /**
     * GET /api/customers
     * Lists the customers ordered by name (15 per page).
     */
    public function index()
    {
        return Customer::orderBy('name')->paginate(15);
    }

    /**
     * POST /api/customers
     * Registers a new customer. Returns 201.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_number' => 'required|string|max:50|unique:customers,customer_number',
            'name' => 'required|string|max:255',
            'tax_id' => 'required|string|max:13',
            'fiscal_address' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
        ]);

        return response()->json(Customer::create($validated), 201);
    }

    /**
     * GET /api/customers/{id}
     * Returns one customer with the number of active orders it has.
     */
    public function show(string $id)
    {
        return response()->json(Customer::withCount('orders')->findOrFail($id));
    }
}
