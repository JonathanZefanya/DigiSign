@extends('layouts.app')
@section('title', 'Login')

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
        background: linear-gradient(135deg, var(--ds-darker) 0%, #1a365d 100%);
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
        color: rgba(255,255,255,0.7);
        margin: 0;
        font-size: 0.95rem;
    }

    .ds-auth-body {
        padding: 2rem;
    }

    .ds-auth-divider {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
        color: var(--ds-gray);
        font-size: 0.85rem;
    }

    .ds-auth-divider::before,
    .ds-auth-divider::after {
        content: '';
        flex: 1;
        border-top: 1px solid var(--ds-border);
    }

    .ds-auth-divider span {
        padding: 0 1rem;
    }

    .ds-sso-btn {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid var(--ds-border);
        border-radius: 0.5rem;
        background: #f8fafc;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: var(--ds-transition);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        color: var(--ds-dark);
    }

    .ds-sso-btn:hover {
        border-color: var(--ds-teal);
        background: #f0fdfa;
        color: var(--ds-teal-dark);
    }
@endsection

@section('content')
<div class="ds-auth-wrapper">
    <div class="ds-auth-card ds-animate">
        <div class="ds-auth-header">
            <i class="bi bi-shield-lock-fill" style="font-size: 2.5rem;"></i>
            <h2>Welcome Back</h2>
            <p>Sign in to your DigiSign account</p>
        </div>

        <div class="ds-auth-body">
            {{-- SSO Login --}}
            @if(!empty($ssoEnabled))
                <form action="{{ route('sso.login') }}" method="POST" id="ssoForm">
                    @csrf
                    <input type="hidden" name="sso_token" id="ssoToken">
                    <button type="button" class="ds-sso-btn" onclick="handleSsoLogin()" id="ssoLoginBtn">
                        <i class="bi bi-key-fill"></i>
                        Login with SSO
                    </button>
                </form>

                <div class="ds-auth-divider"><span>or sign in with email</span></div>
            @endif

            {{-- Standard Login Form --}}
            <form action="{{ route('login.post') }}" method="POST" id="loginForm">
                @csrf
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
                           required
                           autofocus>
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
                           placeholder="Enter your password"
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100" id="loginSubmitBtn">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>

            @if($registrationEnabled ?? true)
            <div class="text-center mt-4">
                <span class="text-muted">Don't have an account?</span>
                <a href="{{ route('register') }}" class="fw-semibold text-decoration-none ms-1">
                    Create Account
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function handleSsoLogin() {
    const token = prompt('Enter your SSO Token:');
    if (token) {
        document.getElementById('ssoToken').value = token;
        document.getElementById('ssoForm').submit();
    }
}
</script>
@endpush
