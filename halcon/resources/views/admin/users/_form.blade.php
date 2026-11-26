@php
    $editingSelf = $user->exists && $user->is(auth()->user());
@endphp

<div class="form-grid">
    <div>
        <label for="name">Full name</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
               class="{{ $errors->has('name') ? 'is-invalid' : '' }}" required>
        @error('name') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}"
               class="{{ $errors->has('username') ? 'is-invalid' : '' }}" placeholder="e.g. pedro.gomez" required>
        @error('username') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
               class="{{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="name@halcon.com" required>
        @error('email') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="role_id">Role</label>
        @if ($editingSelf)
            {{-- The Administrator cannot change their own role --}}
            <input type="hidden" name="role_id" value="{{ $user->role_id }}">
            <select id="role_id" disabled>
                <option>{{ $user->roleName() }}</option>
            </select>
            <div class="help">You cannot change your own role.</div>
        @else
            <select id="role_id" name="role_id" class="{{ $errors->has('role_id') ? 'is-invalid' : '' }}" required>
                <option value="">Select a role…</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id) == $role->id)>
                        {{ $role->name }} — {{ $role->description }}
                    </option>
                @endforeach
            </select>
        @endif
        @error('role_id') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="password">{{ $user->exists ? 'New password' : 'Password' }}</label>
        <input type="password" id="password" name="password" autocomplete="new-password"
               class="{{ $errors->has('password') ? 'is-invalid' : '' }}" {{ $user->exists ? '' : 'required' }}>
        <div class="help">{{ $user->exists ? 'Leave empty to keep the current password.' : 'At least 8 characters.' }}</div>
        @error('password') <div class="field-error">{{ $message }}</div> @enderror
    </div>

    <div>
        <label for="password_confirmation">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
    </div>
</div>

<div style="margin-top:18px">
    @if ($editingSelf)
        <input type="hidden" name="is_active" value="1">
        <label class="checkbox"><input type="checkbox" checked disabled> Active user (you cannot deactivate your own account)</label>
    @else
        <input type="hidden" name="is_active" value="0">
        <label class="checkbox">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
            Active user (can log in)
        </label>
    @endif
</div>
