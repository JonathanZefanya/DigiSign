@if($documents->count() > 0)
    <div class="row g-4">
        @foreach($documents as $index => $document)
            <div class="col-lg-4 col-md-6 ds-animate ds-animate-delay-{{ ($index % 4) + 1 }}">
                <div class="ds-card h-100">
                    <div class="card-body d-flex flex-column">
                        {{-- Status Badge --}}
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="ds-badge ds-badge-{{ $document->status }}" onclick="filterStatus('{{ $document->status }}')" style="cursor:pointer;" title="Filter by status">
                                @if($document->status === 'signed')
                                    <i class="bi bi-check-circle-fill me-1"></i> Signed
                                @elseif($document->status === 'pending')
                                    <i class="bi bi-hourglass-split me-1"></i> Pending
                                @elseif($document->status === 'draft')
                                    <i class="bi bi-pencil me-1"></i> Draft
                                @else
                                    <i class="bi bi-x-circle me-1"></i> Revoked
                                @endif
                            </span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary border-0" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('documents.show', $document) }}">
                                            <i class="bi bi-eye me-2"></i> View Details
                                        </a>
                                    </li>
                                    @if(!$document->isSigned())
                                        <li>
                                            <a class="dropdown-item" href="{{ route('documents.sign', $document) }}">
                                                <i class="bi bi-pen me-2"></i> Sign Document
                                            </a>
                                        </li>
                                    @endif
                                    @if($document->signed_file_path || $document->file_path)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('documents.download', $document) }}">
                                                <i class="bi bi-download me-2"></i> Download
                                            </a>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item text-danger delete-doc-btn"
                                                data-doc-id="{{ $document->id }}"
                                                data-doc-title="{{ $document->title }}"
                                                type="button">
                                            <i class="bi bi-trash me-2"></i> Delete
                                        </button>
                                        <form id="delete-doc-form-{{ $document->id }}" 
                                              action="{{ route('documents.destroy', $document) }}" method="POST" class="d-none">
                                            @csrf @method('DELETE')
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Document Icon --}}
                        <div class="text-center mb-3">
                            <div style="width:64px;height:64px;margin:0 auto;background:{{ $document->isSigned() ? '#d1fae5' : '#fef3c7' }};border-radius:1rem;display:flex;align-items:center;justify-content:center;">
                                <i class="bi {{ $document->isSigned() ? 'bi-file-earmark-check text-success' : 'bi-file-earmark-pdf text-warning' }}" style="font-size:1.8rem;"></i>
                            </div>
                        </div>

                        {{-- Title --}}
                        <h5 class="fw-bold text-center mb-1" title="{{ $document->title }}">
                            {{ Str::limit($document->title, 30) }}
                        </h5>
                        <p class="text-muted text-center small mb-1">
                            {{ Str::limit($document->original_filename, 35) }}
                        </p>
                        @if($document->category)
                            <div class="text-center mb-3">
                                <span class="badge px-2 py-1 category-badge" 
                                      style="background:{{ $document->category->color }};color:#fff;border-radius:50rem;font-size:0.75rem;cursor:pointer;"
                                      onclick="filterCategory('{{ $document->category->id }}')"
                                      title="Filter by category: {{ $document->category->name }}">
                                    {{ $document->category->name }}
                                </span>
                            </div>
                        @else
                            <div class="mb-3"></div>
                        @endif

                        {{-- Meta --}}
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between text-muted small border-top pt-3">
                                <span>
                                    <i class="bi bi-calendar3 me-1"></i>
                                    {{ $document->created_at->timezone($appTimezone)->format('M d, Y') }}
                                </span>
                                @if($document->signed_at)
                                    <span class="text-success">
                                        <i class="bi bi-check2-all me-1"></i>
                                        Signed {{ $document->signed_at->timezone($appTimezone)->format('M d') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Action Buttons --}}
                            <div class="mt-3">
                                @if($document->isSigned())
                                    <a href="{{ route('documents.show', $document) }}" class="btn btn-success w-100">
                                        <i class="bi bi-eye"></i> View Signed Document
                                    </a>
                                @else
                                    <a href="{{ route('documents.sign', $document) }}" class="btn btn-primary w-100">
                                        <i class="bi bi-pen"></i> Sign Document
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $documents->links() }}
    </div>
@else
    <div class="ds-card ds-animate">
        <div class="ds-empty-state">
            <i class="bi bi-file-earmark-plus"></i>
            <h5>No Documents Found</h5>
            <p class="mb-4">Try adjusting your search or filters, or upload a new document.</p>
            <a href="{{ route('documents.create') }}" class="btn btn-primary btn-lg">
                <i class="bi bi-cloud-upload"></i> Upload New Document
            </a>
        </div>
    </div>
@endif
