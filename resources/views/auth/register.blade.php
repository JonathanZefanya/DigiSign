@extends('layouts.app')
@section('title', 'Register')

@section('styles')
    .ds-auth-wrapper {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem 0;
    }

    .ds-auth-card {
        width: 100%;
        max-width: 480px;
        background: #fff;
        border: 1px solid var(--ds-border);
        border-radius: 1rem;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,.08), 0 8px 10px -6px rgba(0,0,0,.05);
        overflow: hidden;
    }

    .ds-auth-header {
        background: linear-gradient(135deg, var(--ds-teal-dark) 0%, var(--ds-teal) 100%);
        padding: 2.5rem 2rem;
        text-align: center;
        color: #fff;
    }

    .ds-auth-header h2 {
        font-weight: 800;
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
    }

    .ds-auth-header p {
        color: rgba(255,255,255,0.8);
        margin: 0;
        font-size: 0.95rem;
    }

    .ds-auth-body {
        padding: 2rem;
    }

    .ds-password-hint {
        font-size: 0.8rem;
        color: var(--ds-gray);
        margin-top: 0.35rem;
    }
@endsection

@section('content')
<div class="ds-auth-wrapper">
    <div class="ds-auth-card ds-animate">
        <div class="ds-auth-header">
            <i class="bi bi-person-plus-fill" style="font-size: 2.5rem;"></i>
            <h2>Create Account</h2>
            <p>Join DigiSign and start signing documents</p>
        </div>

        <div class="ds-auth-body">
            <form action="{{ route('register.post') }}" method="POST" id="registerForm">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">
                        <i class="bi bi-person me-1"></i> Full Name
                    </label>
                    <input type="text"
                           class="form-control form-control-lg @error('name') is-invalid @enderror"
                           id="name"
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="Enter your full name"
                           required
                           autofocus>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope me-1"></i> Email Address
                    </label>
                    <input type="email"
                           class="form-control form-control-lg @error('email') is-invalid @enderror"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="you@example.com"
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock me-1"></i> Password
                    </label>
                    <input type="password"
                           class="form-control form-control-lg @error('password') is-invalid @enderror"
                           id="password"
                           name="password"
                           placeholder="Create a strong password"
                           required>
                    <div class="ds-password-hint">
                        <i class="bi bi-info-circle me-1"></i>
                        Minimum 8 characters
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">
                        <i class="bi bi-lock-fill me-1"></i> Confirm Password
                    </label>
                    <input type="password"
                           class="form-control form-control-lg"
                           id="password_confirmation"
                           name="password_confirmation"
                           placeholder="Re-enter your password"
                           required>
                </div>

                <button type="submit" class="btn btn-success btn-lg w-100" id="registerSubmitBtn">
                    <i class="bi bi-person-check"></i> Create Account
                </button>
            </form>

            <div class="text-center mt-4">
                <span class="text-muted">Already have an account?</span>
                <a href="{{ route('login') }}" class="fw-semibold text-decoration-none ms-1">
                    Sign In
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
