@extends('layouts.admin')

@section('title', 'Orders')

@section('content')
    <div class="page-head">
        <div>
            <h1>Active orders</h1>
            <p class="subtitle">Every order that has not been deleted, from newest to oldest.</p>
        </div>
        @if ($canManage)
            <div class="actions">
                <a href="{{ route('admin.orders.deleted') }}" class="btn btn-secondary">Deleted orders</a>
                <a href="{{ route('admin.orders.create') }}" class="btn btn-primary">+ New order</a>
            </div>
        @endif
    </div>

    @include('orders._filters', ['action' => route('admin.orders.index')])

    <div class="card table-wrap">
        <table>
            <thead>
            <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th>Ordered at</th>
                <th>Status</th>
                <th>Registered by</th>
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
                    <td>{{ $order->user?->name }}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary btn-small">View</a>
                            @if (auth()->user()->editableOrderFields($order) !== [])
                                <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-secondary btn-small">Edit</a>
                            @endif
                            @if ($canManage)
                                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}"
                                      onsubmit="return confirm('Delete order {{ $order->invoice_number }}? It can be restored later.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-small">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="muted">No orders match the search.</td></tr>
            @endforelse
            </tbody>
        </table>

        @include('partials.pagination', ['paginator' => $orders])
    </div>
@endsection
