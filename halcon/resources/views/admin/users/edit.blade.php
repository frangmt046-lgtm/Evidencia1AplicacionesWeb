@extends('layouts.admin')

@section('title', 'Edit employee')

@section('content')
    <h1>Edit employee</h1>
    <p class="subtitle">{{ $user->name }} · registered {{ $user->created_at?->format('Y-m-d') }}</p>

    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" novalidate>
            @csrf
            @method('PUT')
            @include('admin.users._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
