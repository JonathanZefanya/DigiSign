@if($documents->count() > 0)
    <div class="table-responsive">
        <table class="table ds-table mb-0">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>User</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Uploaded</th>
                    <th>Signed</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $doc)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:36px;height:36px;background:{{ $doc->isSigned() ? '#d1fae5' : '#fef3c7' }};border-radius:0.5rem;display:flex;align-items:center;justify-content:center;">
                                    <i class="bi {{ $doc->isSigned() ? 'bi-file-earmark-check text-success' : 'bi-file-earmark-pdf text-warning' }}"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ Str::limit($doc->title, 30) }}</div>
                                    <div class="text-muted small">{{ Str::limit($doc->original_filename, 35) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-muted">{{ $doc->user->name }}</td>
                        <td>
                            @if($doc->category)
                                <span class="badge px-2 py-1" 
                                      style="background:{{ $doc->category->color }};color:#fff;border-radius:50rem;font-size:0.8rem;cursor:pointer;"
                                      onclick="filterCategory('{{ $doc->category->id }}')"
                                      title="Filter by category: {{ $doc->category->name }}">
                                    {{ $doc->category->name }}
                                </span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="ds-badge ds-badge-{{ $doc->status }}" 
                                  onclick="filterStatus('{{ $doc->status }}')" 
                                  style="cursor:pointer;"
                                  title="Filter by status">
                                {{ ucfirst($doc->status) }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $doc->created_at->timezone($appTimezone)->format('M d, Y') }}</td>
                        <td class="text-muted small">
                            {{ $doc->signed_at ? $doc->signed_at->timezone($appTimezone)->format('M d, Y') : '—' }}
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('documents.show', $doc) }}"
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('documents.download', $doc) }}"
                                   class="btn btn-sm btn-outline-secondary" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                                @if($doc->isSigned())
                                    <a href="{{ route('verify.show', $doc->document_hash) }}"
                                       class="btn btn-sm btn-outline-success" title="Verify" target="_blank">
                                        <i class="bi bi-shield-check"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-3 d-flex justify-content-center">
        {{ $documents->links() }}
    </div>
@else
    <div class="ds-empty-state">
        <i class="bi bi-file-earmark-x"></i>
        <h5>No Documents Found</h5>
        <p>No documents match your search criteria.</p>
    </div>
@endif
