<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HALCON | @yield('title', 'Dashboard')</title>
    @include('partials.styles')
</head>
<body>
@php
    $authUser = auth()->user();
    $menu = \App\Http\Controllers\DashboardController::modulesFor($authUser);
@endphp
<div class="shell">
    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="brand">
            <span class="brand-mark">H</span>
            <span>
                <span class="brand-name">HALCON</span>
                <span class="brand-tag">Administrative Dashboard</span>
            </span>
        </a>

        <nav class="menu">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">Dashboard</a>
            @foreach ($menu as $item)
                @php
                    $isActive = isset($item['active'])
                        ? request()->routeIs(...$item['active'])
                        : request()->routeIs('modules.show') && request()->route('module') === $item['key'];
                @endphp
                <a href="{{ $item['url'] }}" class="{{ $isActive ? 'active' : '' }}">{{ $item['title'] }}</a>
            @endforeach
        </nav>

        <div class="sidebar-user">
            <div class="avatar">{{ mb_strtoupper(mb_substr($authUser->name, 0, 1)) }}</div>
            <div>
                <div class="sidebar-name">{{ $authUser->name }}</div>
                <span class="role-badge role-{{ strtolower($authUser->roleName() ?? '') }}">{{ $authUser->roleName() }}</span>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-logout">Log out</button>
        </form>
    </aside>

    <main class="content">
        @if (session('status'))
            <div class="alert alert-ok">{{ session('status') }}</div>
        @endif
        @if ($errors->has('status'))
            <div class="alert alert-error">{{ $errors->first('status') }}</div>
        @endif

        @yield('content')
    </main>
</div>
</body>
</html>
