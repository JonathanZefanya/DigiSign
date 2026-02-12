@extends('layouts.app')
@section('title', 'Verify Email - OTP')

@section('styles')
<style>
    .otp-container {
        max-width: 500px;
        margin: 80px auto;
        padding: 40px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    }

    .otp-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .otp-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: white;
    }

    .otp-input-group {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin: 30px 0;
    }

    .otp-input {
        width: 60px;
        height: 70px;
        text-align: center;
        font-size: 32px;
        font-weight: bold;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        transition: all 0.3s;
        font-family: 'Courier New', monospace;
    }

    .otp-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .otp-info {
        background: #f0f9ff;
        border-left: 4px solid #3b82f6;
        padding: 15px;
        border-radius: 8px;
        margin: 20px 0;
    }

    .otp-timer {
        text-align: center;
        margin: 20px 0;
        font-size: 14px;
        color: #6b7280;
    }

    .otp-timer.expired {
        color: #dc2626;
        font-weight: 600;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .loading {
        animation: pulse 1.5s ease-in-out infinite;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="otp-container">
        <div class="otp-header">
            <div class="otp-icon">
                🔐
            </div>
            <h2 class="fw-bold mb-2">Email Verification</h2>
            <p class="text-muted">Enter the 6-digit code sent to your email</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="otp-info">
            <div class="d-flex align-items-start">
                <i class="bi bi-envelope-check me-3" style="font-size: 1.5rem; color: #3b82f6;"></i>
                <div>
                    <p class="mb-1"><strong>📄 Document:</strong> {{ $document->title }}</p>
                    <p class="mb-1"><strong>📧 Email:</strong> {{ $recipient->email }}</p>
                    <p class="mb-0"><strong>🔔 OTP sent to your email</strong></p>
                </div>
            </div>
        </div>

        <form action="{{ route('documents.sign.token.verify-otp', $token) }}" method="POST" id="otpForm">
            @csrf
            <input type="hidden" name="otp" id="otpValue">
            
            <div class="otp-input-group">
                <input type="text" maxlength="1" class="otp-input" id="otp1" autocomplete="off" autofocus>
                <input type="text" maxlength="1" class="otp-input" id="otp2" autocomplete="off">
                <input type="text" maxlength="1" class="otp-input" id="otp3" autocomplete="off">
                <input type="text" maxlength="1" class="otp-input" id="otp4" autocomplete="off">
                <input type="text" maxlength="1" class="otp-input" id="otp5" autocomplete="off">
                <input type="text" maxlength="1" class="otp-input" id="otp6" autocomplete="off">
            </div>

            @error('otp')
                <div class="alert alert-danger text-center">{{ $message }}</div>
            @enderror

            <div class="otp-timer" id="timer">
                ⏱️ Code expires in <span id="countdown">10:00</span>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3" id="verifyBtn">
                <i class="bi bi-check-circle me-2"></i>Verify & Continue
            </button>
        </form>

        <form action="{{ route('documents.sign.token.resend-otp', $token) }}" method="POST" id="resendForm">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100" id="resendBtn">
                <i class="bi bi-arrow-clockwise me-2"></i>Resend OTP
            </button>
        </form>

        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="bi bi-shield-check me-1"></i>This verification ensures only you can access this document
            </small>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
const inputs = document.querySelectorAll('.otp-input');
const form = document.getElementById('otpForm');
const otpValue = document.getElementById('otpValue');
const verifyBtn = document.getElementById('verifyBtn');
const resendBtn = document.getElementById('resendBtn');
const timerEl = document.getElementById('timer');
const countdownEl = document.getElementById('countdown');

// Auto-focus and navigation
inputs.forEach((input, index) => {
    input.addEventListener('input', function(e) {
        // Only allow numbers
        this.value = this.value.replace(/[^0-9]/g, '');
        
        if (this.value.length === 1 && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }
        
        // Auto submit when all 6 digits entered
        checkComplete();
    });
    
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && this.value === '' && index > 0) {
            inputs[index - 1].focus();
        }
    });
    
    input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
        
        for (let i = 0; i < pastedData.length && i < inputs.length; i++) {
            inputs[i].value = pastedData[i];
        }
        
        if (pastedData.length >= inputs.length) {
            inputs[inputs.length - 1].focus();
            checkComplete();
        }
    });
});

function checkComplete() {
    let otp = '';
    inputs.forEach(input => otp += input.value);
    
    if (otp.length === 6) {
        otpValue.value = otp;
        verifyBtn.disabled = false;
        // Auto submit
        setTimeout(() => form.submit(), 300);
    } else {
        verifyBtn.disabled = true;
    }
}

// Countdown timer (10 minutes = 600 seconds)
let timeLeft = 600;
const countdownInterval = setInterval(() => {
    timeLeft--;
    
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    countdownEl.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
    
    if (timeLeft <= 60) {
        timerEl.classList.add('expired');
    }
    
    if (timeLeft <= 0) {
        clearInterval(countdownInterval);
        countdownEl.textContent = 'EXPIRED';
        timerEl.innerHTML = '⚠️ <strong>Code expired!</strong> Please resend OTP.';
        verifyBtn.disabled = true;
        inputs.forEach(input => input.disabled = true);
    }
}, 1000);

// Resend form loading state
resendBtn.addEventListener('click', function() {
    this.disabled = true;
    this.classList.add('loading');
    this.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Sending...';
});
</script>
@endsection
