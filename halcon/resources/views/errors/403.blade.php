<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HALCON | Access denied</title>
    @include('partials.styles')
</head>
<body style="min-height:100vh; display:grid; place-items:center; padding:20px">
<div class="card" style="max-width:480px; text-align:center">
    <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#b91c1c" stroke-width="2"
         stroke-linecap="round" aria-hidden="true" style="margin-bottom:8px">
        <circle cx="12" cy="12" r="10"/>
        <line x1="5" y1="5" x2="19" y2="19"/>
    </svg>
    <h1>Access denied (403)</h1>
    <p class="subtitle">{{ $exception->getMessage() ?: 'You do not have permission to open this page.' }}</p>
    @auth
        <p class="muted">You are logged in as <strong>{{ auth()->user()->name }}</strong>
            ({{ auth()->user()->roleName() }}).</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">Back to my dashboard</a>
    @else
        <a href="{{ route('login') }}" class="btn btn-primary">Log in</a>
    @endauth
</div>
</body>
</html>
