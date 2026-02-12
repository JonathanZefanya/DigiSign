@extends('layouts.app')
@section('title', 'Document Processed')

@section('content')
<div class="container py-5" style="max-width: 600px;">
    <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
            @if($recipient->hasSigned())
                <i class="bi bi-check-circle-fill text-success d-block mb-3" style="font-size: 5rem;"></i>
                <h1 class="h3 fw-bold text-success mb-3">Document Signed!</h1>
                <p class="text-muted">You have successfully signed this document.</p>
            @elseif($recipient->hasViewed())
                <i class="bi bi-eye-fill text-info d-block mb-3" style="font-size: 5rem;"></i>
                <h1 class="h3 fw-bold text-info mb-3">Document Viewed</h1>
                <p class="text-muted">This document has been marked as viewed.</p>
            @elseif($recipient->hasRejected())
                <i class="bi bi-x-circle-fill text-danger d-block mb-3" style="font-size: 5rem;"></i>
                <h1 class="h3 fw-bold text-danger mb-3">Document Rejected</h1>
                <p class="text-muted">You have rejected this document.</p>
            @else
                <i class="bi bi-info-circle-fill text-secondary d-block mb-3" style="font-size: 5rem;"></i>
                <h1 class="h3 fw-bold text-secondary mb-3">Already Processed</h1>
                <p class="text-muted">This document has already been processed.</p>
            @endif

            <div class="bg-light rounded p-4 my-4">
                <p class="small text-muted mb-1">
                    <strong>Document:</strong> {{ $document->title }}
                </p>
                <p class="small text-muted mb-0">
                    <strong>Processed:</strong> {{ $recipient->signed_at?->format('M d, Y H:i') ?? 'N/A' }}
                </p>
            </div>

            <p class="text-muted small">
                You can close this window now.
            </p>
        </div>
    </div>
</div>
@endsection
