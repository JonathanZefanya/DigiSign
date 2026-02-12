<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f8fafc;
        }
        .success-icon {
            font-size: 5rem;
            color: #198754;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 600px; margin-top: 3rem;">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="bi bi-check-circle-fill success-icon d-block mb-4"></i>
                <h1 class="h3 fw-bold text-success mb-4">{{ $message }}</h1>

                <div class="bg-light rounded p-4 mb-4">
                    <p class="small text-muted mb-1">
                        <strong>Document:</strong> {{ $document->title }}
                    </p>
                    <p class="small text-muted mb-0">
                        <strong>Status:</strong> 
                        @if($recipient->hasSigned())
                            <span class="badge bg-success">Signed</span>
                        @elseif($recipient->hasViewed())
                            <span class="badge bg-info">Viewed</span>
                        @elseif($recipient->hasRejected())
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </p>
                </div>

                <p class="text-muted small">
                    Thank you! You can close this window now.
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
