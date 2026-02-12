@extends('layouts.app')
@section('title', $user ? 'Edit User' : 'Create User')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
            <i class="bi bi-{{ $user ? 'pencil' : 'person-plus' }} text-primary me-2"></i>
            {{ $user ? 'Edit User' : 'Create New User' }}
        </h1>
        <p class="text-muted mb-0">{{ $user ? 'Update user account details' : 'Add a new user to the platform' }}</p>
    </div>
    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="ds-card ds-animate ds-animate-delay-1">
            <div class="card-header">
                <i class="bi bi-person-vcard me-2"></i>User Details
            </div>
            <div class="card-body">
                <form action="{{ $user ? route('admin.users.update', $user) : route('admin.users.store') }}"
                      method="POST" id="userForm">
                    @csrf
                    @if($user)
                        @method('PUT')
                    @endif

                    <div class="mb-4">
                        <label for="name" class="form-label">
                            <i class="bi bi-person me-1"></i> Full Name
                        </label>
                        <input type="text"
                               class="form-control form-control-lg @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $user?->name) }}"
                               placeholder="Enter full name"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-1"></i> Email Address
                        </label>
                        <input type="email"
                               class="form-control form-control-lg @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email', $user?->email) }}"
                               placeholder="user@example.com"
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-1"></i> Password
                            @if($user)
                                <small class="text-muted">(leave blank to keep current)</small>
                            @endif
                        </label>
                        <input type="password"
                               class="form-control form-control-lg @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               placeholder="{{ $user ? 'Enter new password (optional)' : 'Create a password' }}"
                               {{ $user ? '' : 'required' }}>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label">
                            <i class="bi bi-shield me-1"></i> Role
                        </label>
                        <select class="form-select form-select-lg @error('role') is-invalid @enderror"
                                id="role" name="role" required>
                            <option value="user" {{ old('role', $user?->role) === 'user' ? 'selected' : '' }}>
                                User — Standard document signing access
                            </option>
                            <option value="admin" {{ old('role', $user?->role) === 'admin' ? 'selected' : '' }}>
                                Admin — Full platform management
                            </option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($user)
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">
                                    Account is Active
                                </label>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex gap-3">
                        <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-x-lg"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success btn-lg flex-grow-1" id="saveUserBtn">
                            <i class="bi bi-check-circle"></i>
                            {{ $user ? 'Update User' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
