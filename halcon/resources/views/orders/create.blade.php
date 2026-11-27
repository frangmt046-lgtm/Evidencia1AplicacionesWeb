@extends('layouts.admin')

@section('title', 'New order')

@section('content')
    <h1>New order</h1>
    <p class="subtitle">The order is saved with the status <strong>Ordered</strong> and can be tracked by the customer with their customer number and invoice number.</p>

    <div class="card">
        <form method="POST" action="{{ route('admin.orders.store') }}" novalidate>
            @csrf
            @include('orders._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Create order</button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
