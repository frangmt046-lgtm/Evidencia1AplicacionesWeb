<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EvidencePhoto;
use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Public order tracking used by the customers' main screen (Vue.js).
 * The customer does not log in: the pair Customer Number + Invoice Number
 * works as the "key" to see the order.
 */
class TrackingController extends Controller
{
    /**
     * GET /api/track?customer_number=CUS-12345&invoice_number=INV-00001
     *
     * Returns only the information the customer needs (no internal data such
     * as employee emails). The response is the same 404 when the invoice does
     * not exist or when it belongs to another customer, so nobody can guess
     * other customers' invoices.
     */
    public function show(Request $request)
    {
        $validated = $request->validate([
            'customer_number' => 'required|string|max:50',
            'invoice_number' => 'required|string|max:50',
        ], [
            'customer_number.required' => 'Please enter your Customer Number.',
            'invoice_number.required' => 'Please enter your Invoice Number.',
        ]);

        // The customer may type in lowercase or with extra spaces.
        $customerNumber = strtoupper(trim($validated['customer_number']));
        $invoiceNumber = strtoupper(trim($validated['invoice_number']));

        $order = Order::with([
            'customer:id,customer_number,name',
            'statusHistories' => fn ($query) => $query->orderBy('changed_at')->orderBy('id'),
            'evidencePhotos',
        ])
            ->where('invoice_number', $invoiceNumber)
            ->whereHas('customer', fn ($query) => $query->where('customer_number', $customerNumber))
            ->first();

        if (! $order) {
            return response()->json([
                'message' => 'We could not find an order with that Customer Number and Invoice Number. Please check the data and try again.',
            ], 404);
        }

        $deliveryPhoto = $order->status === Order::STATUS_DELIVERED
            ? $order->evidencePhotos->firstWhere('type', EvidencePhoto::TYPE_DELIVERY)
            : null;

        return response()->json([
            'invoice_number' => $order->invoice_number,
            'customer' => [
                'customer_number' => $order->customer->customer_number,
                'name' => $order->customer->name,
            ],
            'status' => $order->status,
            'status_step' => array_search($order->status, Order::STATUSES, true) + 1,
            'statuses' => Order::STATUSES,
            'ordered_at' => $order->ordered_at,
            'delivery_address' => $order->delivery_address,
            'notes' => $order->notes,
            'last_update' => $order->statusHistories->last()?->changed_at ?? $order->updated_at,
            'history' => $order->statusHistories->map(fn ($history) => [
                'status' => $history->new_status,
                'changed_at' => $history->changed_at,
            ])->values(),
            'delivery_photo' => $deliveryPhoto ? [
                'url' => asset('storage/'.$deliveryPhoto->image_path),
                'uploaded_at' => $deliveryPhoto->uploaded_at,
            ] : null,
        ]);
    }
}
