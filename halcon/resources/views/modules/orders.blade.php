@extends('layouts.admin')

@section('title', $module['title'])

@section('content')
    <h1>{{ $module['title'] }}</h1>
    <p class="subtitle">{{ $module['description'] }}</p>

    <div class="alert alert-info">
        @if ($module['statuses'])
            Showing orders with status: <strong>{{ implode(', ', $module['statuses']) }}</strong>.
        @else
            Showing all active orders.
        @endif
        The actions of this department are available in the REST API and will be added to this screen in the next activities.
    </div>

    <div class="card table-wrap">
        <table>
            <thead>
            <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Ordered at</th>
                <th>Status</th>
                <th>Registered by</th>
                @if ($module['key'] === 'route')
                    <th>Evidence photos</th>
                @endif
                <th>Delivery address</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td><strong>{{ $order->invoice_number }}</strong></td>
                    <td>{{ $order->customer?->name }}<br><span class="muted">{{ $order->customer?->customer_number }}</span></td>
                    <td>{{ $order->ordered_at?->format('Y-m-d H:i') }}</td>
                    <td><span class="role-badge">{{ $order->status }}</span></td>
                    <td>{{ $order->user?->name }}</td>
                    @if ($module['key'] === 'route')
                        <td>{{ $order->evidence_photos_count }}</td>
                    @endif
                    <td>{{ $order->delivery_address }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">There are no orders for this module.</td></tr>
            @endforelse
            </tbody>
        </table>

        @include('partials.pagination', ['paginator' => $orders])
    </div>
@endsection
