<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ $appName ?? 'DigiSign' }}</title>

    @if(!empty($appFavicon))
        <link rel="icon" href="{{ $appFavicon }}" type="image/png">
    @else
        <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>✍️</text></svg>">
    @endif

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Bootstrap 5.3 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --ds-primary: #0d6efd;
            --ds-primary-dark: #0a58ca;
            --ds-teal: #0d9488;
            --ds-teal-dark: #0f766e;
            --ds-success: #059669;
            --ds-danger: #dc2626;
            --ds-warning: #d97706;
            --ds-dark: #1e293b;
            --ds-darker: #0f172a;
            --ds-gray: #64748b;
            --ds-light: #f8fafc;
            --ds-border: #e2e8f0;
            --ds-card-shadow: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
            --ds-card-shadow-hover: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -4px rgba(0,0,0,.1);
            --ds-radius: 0.75rem;
            --ds-transition: all 0.2s ease;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: var(--ds-light);
            color: var(--ds-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-size: 1rem;
            line-height: 1.6;
        }

        /* ── Navbar ─────────────────────────────────────── */
        .ds-navbar {
            background: linear-gradient(135deg, var(--ds-darker) 0%, #1a365d 100%);
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding: 0.75rem 0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.15);
        }

        .ds-navbar .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: #fff !important;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .ds-navbar .navbar-brand img {
            height: 36px;
            width: auto;
        }

        .ds-navbar .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem;
            transition: var(--ds-transition);
        }

        .ds-navbar .nav-link:hover,
        .ds-navbar .nav-link.active {
            color: #fff !important;
            background: rgba(255,255,255,0.1);
        }

        .ds-navbar .nav-link .bi {
            margin-right: 0.35rem;
        }

        .ds-user-badge {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 50rem;
            padding: 0.35rem 1rem 0.35rem 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .ds-user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--ds-teal), var(--ds-primary));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            color: #fff;
        }

        /* ── Main Content ───────────────────────────────── */
        .ds-main {
            flex: 1;
            padding: 2rem 0;
        }

        /* ── Cards ──────────────────────────────────────── */
        .ds-card {
            background: #fff;
            border: 1px solid var(--ds-border);
            border-radius: var(--ds-radius);
            box-shadow: var(--ds-card-shadow);
            transition: var(--ds-transition);
        }

        .ds-card:hover {
            box-shadow: var(--ds-card-shadow-hover);
        }

        .ds-card .card-header {
            background: transparent;
            border-bottom: 1px solid var(--ds-border);
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .ds-card .card-body {
            padding: 1.5rem;
        }

        /* ── Buttons ───────────────────────────────────── */
        .btn {
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.625rem 1.5rem;
            border-radius: 0.5rem;
            transition: var(--ds-transition);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-lg {
            padding: 0.875rem 2rem;
            font-size: 1.1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--ds-primary) 0%, var(--ds-primary-dark) 100%);
            border: none;
            box-shadow: 0 2px 4px rgba(13,110,253,0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--ds-primary-dark) 0%, #084298 100%);
            box-shadow: 0 4px 8px rgba(13,110,253,0.4);
            transform: translateY(-1px);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--ds-success) 0%, #047857 100%);
            border: none;
            box-shadow: 0 2px 4px rgba(5,150,105,0.3);
        }

        .btn-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(5,150,105,0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--ds-danger) 0%, #b91c1c 100%);
            border: none;
        }

        .btn-outline-light {
            border-color: rgba(255,255,255,0.3);
            color: #fff;
        }

        .btn-outline-light:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.5);
        }

        /* ── Status Badges ─────────────────────────────── */
        .ds-badge {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.35rem 0.85rem;
            border-radius: 50rem;
            letter-spacing: 0.02em;
        }

        .ds-badge-signed {
            background: #d1fae5;
            color: #065f46;
        }

        .ds-badge-pending {
            background: #e0e7ff;
            color: #3730a3;
        }

        .ds-badge-draft {
            background: #fef3c7;
            color: #92400e;
        }

        .ds-badge-revoked {
            background: #fecaca;
            color: #991b1b;
        }

        .ds-badge-admin {
            background: #ede9fe;
            color: #5b21b6;
        }

        .ds-badge-user {
            background: #dbeafe;
            color: #1e40af;
        }

        /* ── Stats Cards ───────────────────────────────── */
        .ds-stat-card {
            background: #fff;
            border: 1px solid var(--ds-border);
            border-radius: var(--ds-radius);
            padding: 1.5rem;
            transition: var(--ds-transition);
        }

        .ds-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--ds-card-shadow-hover);
        }

        .ds-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .ds-stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--ds-dark);
            line-height: 1;
        }

        .ds-stat-label {
            font-size: 0.85rem;
            color: var(--ds-gray);
            font-weight: 500;
            margin-top: 0.25rem;
        }

        /* ── Tables ────────────────────────────────────── */
        .ds-table {
            font-size: 0.95rem;
        }

        .ds-table thead th {
            background: #f1f5f9;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--ds-gray);
            border-bottom: 2px solid var(--ds-border);
            padding: 0.875rem 1rem;
        }

        .ds-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--ds-border);
        }

        .ds-table tbody tr:hover {
            background: #f8fafc;
        }

        /* ── Footer ────────────────────────────────────── */
        .ds-footer {
            background: var(--ds-darker);
            color: rgba(255,255,255,0.6);
            padding: 1.5rem 0;
            font-size: 0.85rem;
            margin-top: auto;
        }

        /* ── Alerts ────────────────────────────────────── */
        .ds-alert {
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* ── Form Controls ─────────────────────────────── */
        .form-control, .form-select {
            border: 1.5px solid var(--ds-border);
            border-radius: 0.5rem;
            padding: 0.65rem 1rem;
            font-size: 1rem;
            transition: var(--ds-transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--ds-primary);
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--ds-dark);
            margin-bottom: 0.5rem;
        }

        /* ── Empty State ───────────────────────────────── */
        .ds-empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .ds-empty-state i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }

        .ds-empty-state h5 {
            color: var(--ds-gray);
            font-weight: 600;
        }

        .ds-empty-state p {
            color: #94a3b8;
        }

        /* ── Animation ─────────────────────────────────── */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .ds-animate {
            animation: fadeInUp 0.4s ease forwards;
        }

        .ds-animate-delay-1 { animation-delay: 0.1s; opacity: 0; }
        .ds-animate-delay-2 { animation-delay: 0.2s; opacity: 0; }
        .ds-animate-delay-3 { animation-delay: 0.3s; opacity: 0; }
        .ds-animate-delay-4 { animation-delay: 0.4s; opacity: 0; }

        /* ── Scrollbar ─────────────────────────────────── */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        @yield('styles')
    </style>

    @stack('head')
</head>
<body>
    {{-- ── Navbar ────────────────────────────────── --}}
    <nav class="navbar navbar-expand-lg ds-navbar" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                @if(!empty($appLogo))
                    <img src="{{ $appLogo }}" alt="{{ $appName ?? 'DigiSign' }}">
                @else
                    <i class="bi bi-shield-check"></i>
                @endif
                {{ $appName ?? 'DigiSign' }}
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>

            <div class="collapse navbar-collapse" id="navContent">
                @auth
                    <ul class="navbar-nav me-auto ms-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}"
                               href="{{ route('documents.index') }}">
                                <i class="bi bi-file-earmark-pdf"></i> My Documents
                                @if(($pendingSignaturesCount ?? 0) > 0)
                                    <span class="badge bg-danger rounded-pill ms-1" 
                                          style="font-size: 0.7rem; padding: 0.25rem 0.5rem;"
                                          title="{{ $pendingSignaturesCount }} document(s) pending your signature">
                                        {{ $pendingSignaturesCount }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('documents.create') ? 'active' : '' }}"
                               href="{{ route('documents.create') }}">
                                <i class="bi bi-cloud-upload"></i> Upload & Sign
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"
                               href="{{ route('categories.index') }}">
                                <i class="bi bi-bookmarks"></i> My Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('plan.*') ? 'active' : '' }}"
                               href="{{ route('plan.index') }}">
                                <i class="bi bi-box-seam"></i> My Plan
                            </a>
                        </li>
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                                   href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear"></i> Admin
                                </a>
                                <ul class="dropdown-menu dropdown-menu-dark">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.users') }}">
                                            <i class="bi bi-people me-2"></i> Manage Users
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.plans.index') }}">
                                            <i class="bi bi-card-list"></i> Manage Plans
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.categories.index') }}">
                                            <i class="bi bi-bookmarks me-2"></i> Document Categories
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.documents') }}">
                                            <i class="bi bi-files me-2"></i> All Documents
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.settings') }}">
                                            <i class="bi bi-sliders me-2"></i> Settings
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.smtp.index') }}">
                                            <i class="bi bi-journal-text me-2"></i> SMTP
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>

                    <div class="d-flex align-items-center gap-3">
                        <div class="ds-user-badge">
                            <div class="ds-user-avatar">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            {{ auth()->user()->name }}
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm" title="Logout">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>c
                    </div>
                @else
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                        @if($registrationEnabled ?? true)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">
                                <i class="bi bi-person-plus"></i> Register
                            </a>
                        </li>
                        @endif
                    </ul>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ── Flash Messages ────────────────────────── --}}
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success ds-alert ds-animate" role="alert" id="flashSuccess">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info ds-alert ds-animate" role="alert" id="flashInfo">
                <i class="bi bi-info-circle-fill"></i>
                {{ session('info') }}
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger ds-alert ds-animate" role="alert" id="flashError">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- ── Main Content ──────────────────────────── --}}
    <main class="ds-main">
        <div class="container">
            @yield('content')
        </div>
    </main>

    {{-- ── Footer ────────────────────────────────── --}}
    <footer class="ds-footer" id="mainFooter">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <strong>{{ $appName ?? 'DigiSign' }}</strong> — Digital Signature Platform
                </div>
                <div>
                    &copy; {{ date('Y') }} All rights reserved.
                </div>
            </div>
        </div>
    </footer>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Auto-dismiss alerts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.ds-alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    if (bsAlert) bsAlert.close();
                }, 5000);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
