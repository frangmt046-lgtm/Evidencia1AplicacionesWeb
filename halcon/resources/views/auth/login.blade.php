<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HALCON | Employee Login</title>
    @include('partials.styles')
    <style>
        body { min-height: 100vh; display: grid; place-items: center; padding: 20px;
               background: linear-gradient(160deg, var(--navy) 0%, var(--navy-2) 100%); }
        .login-card { width: 100%; max-width: 420px; padding: 34px; }
        .login-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 22px; }
        .login-brand .brand-name { color: var(--navy); font-size: 24px; }
        .field { margin-bottom: 16px; }
        .btn-block { width: 100%; padding: 12px; font-size: 16px; margin-top: 6px; }
        .back { display: block; text-align: center; margin-top: 18px; font-size: 14px; }
    </style>
</head>
<body>
<div class="card login-card">
    <div class="login-brand">
        <span class="brand-mark">H</span>
        <div>
            <div class="brand-name">HALCON</div>
            <div class="muted" style="font-size:13px">Employee access</div>
        </div>
    </div>

    <h1 style="font-size:22px">Log in</h1>
    <p class="subtitle" style="margin-bottom:18px">Only registered company employees can access the Administrative Dashboard.</p>

    @if (session('status'))
        <div class="alert alert-ok">{{ session('status') }}</div>
    @endif

    @error('login')
        <div class="alert alert-error" role="alert">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('login.attempt') }}" novalidate>
        @csrf
        <div class="field">
            <label for="login">Username or email</label>
            <input type="text" id="login" name="login" value="{{ old('login') }}"
                   class="{{ $errors->has('login') ? 'is-invalid' : '' }}"
                   placeholder="e.g. admin or admin@halcon.com" autocomplete="username" autofocus required>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                   autocomplete="current-password" required>
            @error('password') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <label class="checkbox"><input type="checkbox" name="remember" value="1"> Remember me</label>

        <button type="submit" class="btn btn-primary btn-block">Log in</button>
    </form>

    <a href="{{ route('track') }}" class="back">Are you a customer? Track your order here →</a>
</div>
</body>
</html>
