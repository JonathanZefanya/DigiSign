@if($users->count() > 0)
    <div class="table-responsive">
        <table class="table ds-table mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Documents</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="ds-user-avatar" style="width:38px;height:38px;font-size:0.8rem;background:linear-gradient(135deg,{{ $user->isAdmin() ? '#7c3aed,#a855f7' : '#0d9488,#0d6efd' }});">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    <div class="text-muted small">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="ds-badge ds-badge-{{ $user->role }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $user->documents_count }}</span>
                            <span class="text-muted small">docs</span>
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="ds-badge ds-badge-signed">
                                    <i class="bi bi-check-circle me-1"></i> Active
                                </span>
                            @else
                                <span class="ds-badge ds-badge-revoked">
                                    <i class="bi bi-x-circle me-1"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $user->created_at->timezone($appTimezone)->format('M d, Y') }}
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit User">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                                class="btn btn-sm {{ $user->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi {{ $user->is_active ? 'bi-pause-circle' : 'bi-play-circle' }}"></i>
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Delete User"
                                            onclick="showDeleteModal('{{ $user->id }}', '{{ $user->name }}', {{ $user->documents_count }})">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                    <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-3 bg-light d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
        </div>
        <div>
            {{ $users->links() }}
        </div>
    </div>
@else
    <div class="text-center py-5">
        <i class="bi bi-person-x text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-3 mb-0">No users found matching your criteria.</p>
    </div>
@endif
