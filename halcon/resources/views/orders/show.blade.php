@extends('layouts.admin')

@section('title', 'Order '.$order->invoice_number)

@section('content')
    <div class="page-head">
        <div>
            <h1>Order {{ $order->invoice_number }}</h1>
            <p class="subtitle">
                @include('orders._status', ['status' => $order->status])
                @if ($order->trashed())
                    @include('orders._status', ['status' => 'Deleted'])
                    Deleted {{ $order->deleted_at->format('Y-m-d H:i') }} by {{ $order->deletedBy?->name ?? 'unknown' }}
                @endif
            </p>
        </div>
        <div class="actions">
            <a href="{{ $order->trashed() ? route('admin.orders.deleted') : route('admin.orders.index') }}" class="btn btn-secondary">Back</a>
            @if ($canEdit)
                <a href="{{ route('admin.orders.edit', $order) }}" class="btn btn-secondary">Edit</a>
            @endif
            @if ($canManage)
                @if ($order->trashed())
                    <form method="POST" action="{{ route('admin.orders.restore', $order) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success">Restore order</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.orders.destroy', $order) }}"
                          onsubmit="return confirm('Delete order {{ $order->invoice_number }}? It can be restored later.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete order</button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    <div class="card">
        <h2 class="section-title">Order information</h2>
        <div class="detail-grid">
            <div><div class="detail-label">Invoice number</div><div class="detail-value">{{ $order->invoice_number }}</div></div>
            <div><div class="detail-label">Date and time</div><div class="detail-value">{{ $order->ordered_at?->format('Y-m-d H:i') }}</div></div>
            <div><div class="detail-label">Current status</div><div class="detail-value">{{ $order->status }}</div></div>
            <div><div class="detail-label">Registered by</div><div class="detail-value">{{ $order->user?->name }}</div></div>
            <div><div class="detail-label">Delivery address</div><div class="detail-value">{{ $order->delivery_address }}</div></div>
            <div><div class="detail-label">Notes</div><div class="detail-value">{{ $order->notes ?: '—' }}</div></div>
        </div>
    </div>

    <div class="card">
        <h2 class="section-title">Customer</h2>
        <div class="detail-grid">
            <div><div class="detail-label">Customer number</div><div class="detail-value">{{ $order->customer?->customer_number }}</div></div>
            <div><div class="detail-label">Name or company</div><div class="detail-value">{{ $order->customer?->name }}</div></div>
            <div><div class="detail-label">RFC</div><div class="detail-value">{{ $order->customer?->tax_id }}</div></div>
            <div><div class="detail-label">Fiscal address</div><div class="detail-value">{{ $order->customer?->fiscal_address }}</div></div>
            <div><div class="detail-label">Email</div><div class="detail-value">{{ $order->customer?->email }}</div></div>
            <div><div class="detail-label">Phone</div><div class="detail-value">{{ $order->customer?->phone }}</div></div>
        </div>
    </div>

    <div class="card">
        <h2 class="section-title">Status history</h2>
        <ul class="timeline">
            @forelse ($order->statusHistories as $history)
                <li>
                    <strong>{{ $history->new_status }}</strong>
                    <span class="muted">· {{ $history->changed_at?->format('Y-m-d H:i') }} · {{ $history->user?->name }}</span>
                </li>
            @empty
                <li class="muted">No status changes recorded.</li>
            @endforelse
        </ul>
        <p class="muted" style="margin:14px 0 0">Evidence photos: {{ $order->evidencePhotos->count() }}</p>
    </div>
@endsection
