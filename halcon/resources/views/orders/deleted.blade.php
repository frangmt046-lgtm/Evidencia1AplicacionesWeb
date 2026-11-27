@extends('layouts.admin')

@section('title', 'Deleted orders')

@section('content')
    <div class="page-head">
        <div>
            <h1>Deleted orders</h1>
            <p class="subtitle">These orders were logically deleted. Their information is still stored in the database and they can be restored.</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Back to active orders</a>
    </div>

    @include('orders._filters', ['action' => route('admin.orders.deleted')])

    <div class="card table-wrap">
        <table>
            <thead>
            <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Ordered at</th>
                <th>Status</th>
                <th>Deleted at</th>
                <th>Deleted by</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td><a href="{{ route('admin.orders.show', $order) }}"><strong>{{ $order->invoice_number }}</strong></a></td>
                    <td>{{ $order->customer?->name }}<br><span class="muted">{{ $order->customer?->customer_number }}</span></td>
                    <td>{{ $order->ordered_at?->format('Y-m-d H:i') }}</td>
                    <td>@include('orders._status', ['status' => $order->status])</td>
                    <td>{{ $order->deleted_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $order->deletedBy?->name ?? '—' }}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary btn-small">View</a>
                            <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-secondary btn-small">Edit</a>
                            <form method="POST" action="{{ route('admin.orders.restore', $order) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-small">Restore</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">There are no deleted orders that match the search.</td></tr>
            @endforelse
            </tbody>
        </table>

        @include('partials.pagination', ['paginator' => $orders])
    </div>
@endsection
