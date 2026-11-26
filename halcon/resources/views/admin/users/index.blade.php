@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
    <div class="page-head">
        <div>
            <h1>User Management</h1>
            <p class="subtitle">Company employees registered in the system. Customers do not have accounts.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Register employee</a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('admin.users.index') }}" class="filters">
            <div style="flex:1">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Name, username or email">
            </div>
            <div>
                <label for="role">Role</label>
                <select id="role" name="role">
                    <option value="">All roles</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected(request('role') == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card table-wrap">
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($users as $employee)
                <tr>
                    <td>{{ $employee->id }}</td>
                    <td>{{ $employee->name }} @if ($employee->is(auth()->user())) <span class="muted">(you)</span> @endif</td>
                    <td>{{ $employee->username ?? '—' }}</td>
                    <td>{{ $employee->email }}</td>
                    <td><span class="role-badge role-{{ strtolower($employee->roleName() ?? '') }}">{{ $employee->roleName() ?? 'No role' }}</span></td>
                    <td>
                        <span class="status {{ $employee->is_active ? 'status-active' : 'status-inactive' }}">
                            <svg class="status-dot" width="10" height="10" viewBox="0 0 10 10" aria-hidden="true"><circle cx="5" cy="5" r="5" fill="currentColor"/></svg>
                            {{ $employee->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.users.edit', $employee) }}" class="btn btn-secondary btn-small">Edit</a>
                            @unless ($employee->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.users.toggle', $employee) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-small {{ $employee->is_active ? 'btn-danger' : 'btn-success' }}">
                                        {{ $employee->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">No employees match the filters.</td></tr>
            @endforelse
            </tbody>
        </table>

        @include('partials.pagination', ['paginator' => $users])
    </div>
@endsection
