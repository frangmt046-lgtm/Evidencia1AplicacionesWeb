@extends('layouts.admin')

@section('title', 'Register employee')

@section('content')
    <h1>Register employee</h1>
    <p class="subtitle">The new employee will log in with their username or email and the password you set here.</p>

    <div class="card">
        <form method="POST" action="{{ route('admin.users.store') }}" novalidate>
            @csrf
            @include('admin.users._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Register employee</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
