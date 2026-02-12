@extends('layouts.app')
@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
            <i class="bi bi-speedometer2 text-primary me-2"></i>Admin Dashboard
        </h1>
        <p class="text-muted mb-0">Overview of your DigiSign platform</p>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6 ds-animate ds-animate-delay-1">
        <div class="ds-stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="ds-stat-value">{{ number_format($stats['total_users']) }}</div>
                    <div class="ds-stat-label">Total Users</div>
                </div>
                <div class="ds-stat-icon" style="background:#dbeafe;color:#2563eb;">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 ds-animate ds-animate-delay-2">
        <div class="ds-stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="ds-stat-value">{{ number_format($stats['total_documents']) }}</div>
                    <div class="ds-stat-label">Total Documents</div>
                </div>
                <div class="ds-stat-icon" style="background:#fef3c7;color:#d97706;">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 ds-animate ds-animate-delay-3">
        <div class="ds-stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="ds-stat-value">{{ number_format($stats['signed_documents']) }}</div>
                    <div class="ds-stat-label">Signed Documents</div>
                </div>
                <div class="ds-stat-icon" style="background:#d1fae5;color:#059669;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 ds-animate ds-animate-delay-4">
        <div class="ds-stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="ds-stat-value">{{ number_format($stats['draft_documents']) }}</div>
                    <div class="ds-stat-label">Pending Drafts</div>
                </div>
                <div class="ds-stat-icon" style="background:#fecaca;color:#dc2626;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Recent Documents --}}
    <div class="col-lg-8 ds-animate ds-animate-delay-2">
        <div class="ds-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Recent Documents</span>
                <a href="{{ route('admin.documents') }}" class="btn btn-sm btn-outline-primary">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                @if($recentDocuments->count() > 0)
                    <div class="table-responsive">
                        <table class="table ds-table mb-0">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentDocuments as $doc)
                                    <tr>
                                        <td>
                                            <a href="{{ route('documents.show', $doc) }}" class="text-decoration-none fw-semibold">
                                                {{ Str::limit($doc->title, 30) }}
                                            </a>
                                        </td>
                                        <td class="text-muted">{{ $doc->user->name }}</td>
                                        <td>
                                            <span class="ds-badge ds-badge-{{ $doc->status }}">
                                                {{ ucfirst($doc->status) }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $doc->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="ds-empty-state py-4">
                        <i class="bi bi-file-earmark-x" style="font-size:2rem;"></i>
                        <p class="text-muted mt-2 mb-0">No documents yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Users --}}
    <div class="col-lg-4 ds-animate ds-animate-delay-3">
        <div class="ds-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Recent Users</span>
                <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                @foreach($recentUsers as $user)
                    <div class="d-flex align-items-center gap-3 {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                        <div class="ds-user-avatar" style="width:40px;height:40px;font-size:0.85rem;background:linear-gradient(135deg,{{ $user->isAdmin() ? '#7c3aed,#a855f7' : '#0d9488,#0d6efd' }});">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold" style="font-size:0.95rem;">{{ $user->name }}</div>
                            <div class="text-muted small">{{ $user->email }}</div>
                        </div>
                        <span class="ds-badge ds-badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="ds-card mt-4">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Quick Actions
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary">
                    <i class="bi bi-person-plus"></i> Add New User
                </a>
                <a href="{{ route('admin.settings') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-gear"></i> App Settings
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
