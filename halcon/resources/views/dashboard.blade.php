@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <section class="welcome">
        <h1>Welcome, {{ $user->name }}</h1>
        <p>
            Role: <span class="role-badge role-{{ strtolower($user->roleName() ?? '') }}">{{ $user->roleName() }}</span>
            &nbsp;·&nbsp; {{ $user->role?->description }}
        </p>
    </section>

    <div class="grid grid-4" style="margin-bottom:28px">
        <div class="stat"><div class="stat-value">{{ $stats['orders'] }}</div><div class="stat-label">Active orders</div></div>
        <div class="stat"><div class="stat-value">{{ $stats['in_route'] }}</div><div class="stat-label">Orders in route</div></div>
        <div class="stat"><div class="stat-value">{{ $stats['delivered'] }}</div><div class="stat-label">Delivered orders</div></div>
        @isset($stats['active_users'])
            <div class="stat"><div class="stat-value">{{ $stats['active_users'] }}</div><div class="stat-label">Active employees</div></div>
        @endisset
    </div>

    <h2 style="margin:0 0 12px">Your modules</h2>
    <div class="grid grid-modules">
        @forelse ($modules as $module)
            <a href="{{ $module['url'] }}" class="module-card" data-module="{{ $module['key'] }}">
                <div class="module-title">{{ $module['title'] }}</div>
                <p class="module-desc">{{ $module['description'] }}</p>
            </a>
        @empty
            <p class="muted">Your role has no modules assigned yet.</p>
        @endforelse
    </div>
@endsection
