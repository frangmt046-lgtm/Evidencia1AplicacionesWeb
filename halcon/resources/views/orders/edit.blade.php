@extends('layouts.admin')

@section('title', 'Edit order '.$order->invoice_number)

@section('content')
    <h1>Edit order {{ $order->invoice_number }}</h1>
    <p class="subtitle">
        @if ($order->trashed())
            @include('orders._status', ['status' => 'Deleted']) This order is deleted. You can correct its information and restore it from its detail page.
        @else
            Your role ({{ auth()->user()->roleName() }}) can modify only the enabled fields.
        @endif
    </p>

    <div class="card">
        <form method="POST" action="{{ route('admin.orders.update', $order) }}" novalidate>
            @csrf
            @method('PUT')
            @include('orders._form', ['nextInvoice' => null])

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
