@extends('layouts.app')
@section('title', 'App Settings')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 ds-animate">
    <div>
        <h1 class="fw-bold mb-1" style="font-size: 1.75rem;">
            <i class="bi bi-sliders text-primary me-2"></i>Application Settings
        </h1>
        <p class="text-muted mb-0">Configure your DigiSign application</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
</div>

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
    @csrf
    <div class="row g-4">
        {{-- General Settings --}}
        <div class="col-lg-6 ds-animate ds-animate-delay-1">
            <div class="ds-card">
                <div class="card-header">
                    <i class="bi bi-gear me-2"></i>General Settings
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label for="app_name" class="form-label">
                            <i class="bi bi-app me-1"></i> Application Name
                        </label>
                        <input type="text"
                               class="form-control form-control-lg @error('app_name') is-invalid @enderror"
                               id="app_name"
                               name="app_name"
                               value="{{ $settings['app_name'] ?? config('app.name') }}"
                               placeholder="DigiSign">
                        @error('app_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="app_timezone" class="form-label">
                            <i class="bi bi-globe me-1"></i> Timezone
                        </label>
                        <select class="form-select form-select-lg @error('app_timezone') is-invalid @enderror"
                                id="app_timezone" name="app_timezone">
                            @php
                                $timezones = [
                                    'UTC' => 'UTC',
                                    'Asia/Jakarta' => 'Asia/Jakarta (WIB)',
                                    'Asia/Makassar' => 'Asia/Makassar (WITA)',
                                    'Asia/Jayapura' => 'Asia/Jayapura (WIT)',
                                    'Asia/Singapore' => 'Asia/Singapore',
                                    'Asia/Tokyo' => 'Asia/Tokyo',
                                    'America/New_York' => 'America/New York (EST)',
                                    'America/Chicago' => 'America/Chicago (CST)',
                                    'America/Denver' => 'America/Denver (MST)',
                                    'America/Los_Angeles' => 'America/Los Angeles (PST)',
                                    'Europe/London' => 'Europe/London (GMT)',
                                    'Europe/Berlin' => 'Europe/Berlin (CET)',
                                    'Australia/Sydney' => 'Australia/Sydney (AEST)',
                                ];
                                $currentTz = $settings['app_timezone'] ?? config('app.timezone', 'UTC');
                            @endphp
                            @foreach($timezones as $tz => $label)
                                <option value="{{ $tz }}" {{ $currentTz === $tz ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('app_timezone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="max_upload_size" class="form-label">
                            <i class="bi bi-cloud-arrow-up me-1"></i> Max Upload Size (MB)
                        </label>
                        <input type="number" 
                               class="form-control form-control-lg @error('max_upload_size') is-invalid @enderror"
                               id="max_upload_size" 
                               name="max_upload_size" 
                               value="{{ $settings['max_upload_size'] ?? '10' }}" 
                               min="1" max="50">
                        <div class="form-text">Maximum file size allowed for document uploads (in Megabytes).</div>
                        @error('max_upload_size')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label d-block">
                            <i class="bi bi-person-plus me-1"></i> Public Registration
                        </label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="registration_enabled" name="registration_enabled" value="1"
                                   {{ ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                                   style="width:3rem;height:1.5rem;cursor:pointer;"
                                   onchange="document.getElementById('regStatusText').textContent = this.checked ? 'Enabled' : 'Disabled'; document.getElementById('regStatusText').className = this.checked ? 'text-success' : 'text-danger';">
                            <label class="form-check-label fw-semibold ms-2" for="registration_enabled" style="cursor:pointer;line-height:1.5rem;">
                                <span id="regStatusText" class="{{ ($settings['registration_enabled'] ?? '1') === '1' ? 'text-success' : 'text-danger' }}">
                                    {{ ($settings['registration_enabled'] ?? '1') === '1' ? 'Enabled' : 'Disabled' }}
                                </span>
                            </label>
                        </div>
                        <div class="form-text mt-1">
                            <i class="bi bi-info-circle me-1"></i>
                            When disabled, new users can only be added by an administrator.
                            The register page and links will be hidden.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pricing & Currency Settings --}}
        <div class="col-lg-6 ds-animate ds-animate-delay-2">
            <div class="ds-card">
                <div class="card-header">
                    <i class="bi bi-currency-exchange me-2"></i>Pricing & Currency
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="form-label d-block">
                            <i class="bi bi-cash-stack me-1"></i> Show Pricing
                        </label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="show_pricing" name="show_pricing" value="1"
                                   {{ ($settings['show_pricing'] ?? '1') === '1' ? 'checked' : '' }}
                                   style="width:3rem;height:1.5rem;cursor:pointer;"
                                   onchange="document.getElementById('pricingStatusText').textContent = this.checked ? 'Enabled' : 'Disabled'; document.getElementById('pricingStatusText').className = this.checked ? 'text-success' : 'text-danger';">
                            <label class="form-check-label fw-semibold ms-2" for="show_pricing" style="cursor:pointer;line-height:1.5rem;">
                                <span id="pricingStatusText" class="{{ ($settings['show_pricing'] ?? '1') === '1' ? 'text-success' : 'text-danger' }}">
                                    {{ ($settings['show_pricing'] ?? '1') === '1' ? 'Enabled' : 'Disabled' }}
                                </span>
                            </label>
                        </div>
                        <div class="form-text mt-1">
                            <i class="bi bi-info-circle me-1"></i>
                            When disabled, plan prices will be hidden from users. Useful if you want to show only features without pricing.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="currency_symbol" class="form-label">
                            <i class="bi bi-currency-dollar me-1"></i> Currency Symbol
                        </label>
                        <select class="form-select form-select-lg @error('currency_symbol') is-invalid @enderror"
                                id="currency_symbol" name="currency_symbol">
                            @php
                                $currencies = [
                                    'Rp' => 'Rp - Indonesian Rupiah',
                                    '$' => '$ - US Dollar',
                                    '€' => '€ - Euro',
                                    '£' => '£ - British Pound',
                                    '¥' => '¥ - Japanese Yen / Chinese Yuan',
                                    '₹' => '₹ - Indian Rupee',
                                    'RM' => 'RM - Malaysian Ringgit',
                                    '฿' => '฿ - Thai Baht',
                                ];
                                $currentCurrency = $settings['currency_symbol'] ?? 'Rp';
                            @endphp
                            @foreach($currencies as $symbol => $label)
                                <option value="{{ $symbol }}" {{ $currentCurrency === $symbol ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Select the currency symbol to display for plan prices.</div>
                        @error('currency_symbol')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Branding --}}
        <div class="col-lg-6 ds-animate ds-animate-delay-3">
            <div class="ds-card">
                <div class="card-header">
                    <i class="bi bi-palette me-2"></i>Branding
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label for="app_logo" class="form-label">
                            <i class="bi bi-image me-1"></i> Application Logo
                        </label>
                        @if(!empty($settings['app_logo']))
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings['app_logo']) }}"
                                     alt="Current Logo"
                                     style="max-height:48px;border-radius:0.375rem;border:1px solid var(--ds-border);padding:4px;">
                                <span class="text-muted small ms-2">Current logo</span>
                            </div>
                        @endif
                        <input type="file"
                               class="form-control @error('app_logo') is-invalid @enderror"
                               id="app_logo"
                               name="app_logo"
                               accept="image/png,image/jpeg,image/svg+xml">
                        <div class="form-text">PNG, JPG, or SVG. Max 2MB.</div>
                        @error('app_logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="app_favicon" class="form-label">
                            <i class="bi bi-window me-1"></i> Favicon
                        </label>
                        @if(!empty($settings['app_favicon']))
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $settings['app_favicon']) }}"
                                     alt="Current Favicon"
                                     style="max-height:32px;border-radius:0.25rem;border:1px solid var(--ds-border);padding:2px;">
                                <span class="text-muted small ms-2">Current favicon</span>
                            </div>
                        @endif
                        <input type="file"
                               class="form-control @error('app_favicon') is-invalid @enderror"
                               id="app_favicon"
                               name="app_favicon"
                               accept="image/png,image/x-icon">
                        <div class="form-text">PNG or ICO. Max 1MB.</div>
                        @error('app_favicon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- SSO Settings --}}
        <div class="col-lg-12 ds-animate ds-animate-delay-4">
            <div class="ds-card">
                <div class="card-header">
                    <i class="bi bi-key me-2"></i>SSO Integration
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="sso_api_url" class="form-label">
                                <i class="bi bi-link-45deg me-1"></i> SSO API URL
                            </label>
                            <input type="url"
                                   class="form-control form-control-lg @error('sso_api_url') is-invalid @enderror"
                                   id="sso_api_url"
                                   name="sso_api_url"
                                   value="{{ $settings['sso_api_url'] ?? '' }}"
                                   placeholder="https://sso.example.com/api">
                            <div class="form-text">The base URL of your SSO provider API.</div>
                            @error('sso_api_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="sso_api_key" class="form-label">
                                <i class="bi bi-shield-lock me-1"></i> SSO API Key
                            </label>
                            <input type="password"
                                   class="form-control form-control-lg @error('sso_api_key') is-invalid @enderror"
                                   id="sso_api_key"
                                   name="sso_api_key"
                                   value="{{ $settings['sso_api_key'] ?? '' }}"
                                   placeholder="Enter API key">
                            <div class="form-text">Your secret API key for SSO authentication.</div>
                            @error('sso_api_key')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            When both SSO API URL and Key are configured, a "Login with SSO" button will appear on the login page.
                            Leave these fields empty to disable SSO.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- SSO Provider Settings --}}
        <div class="col-lg-12 ds-animate ds-animate-delay-5">
            <div class="ds-card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-broadcast me-2"></i>SSO Provider (Integration for Other Apps)
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Allow other applications to use DigiSign for authentication.</p>
                    
                    <div class="alert alert-info">
                        <strong><i class="bi bi-info-circle me-1"></i> How to integrate with Jonathan Software:</strong>
                        <ol class="mb-0 mt-2 small">
                            <li>In Jonathan Software, go to <strong>Settings → SSO</strong></li>
                            <li>Enable SSO and create a new website entry</li>
                            <li>Set <strong>URL</strong> to the Base URL below</li>
                            <li>Set <strong>API Key</strong> to the Shared Secret API Key below</li>
                            <li>Save settings - users will now see DigiSign in their SSO menu</li>
                        </ol>
                        <div class="mt-2 p-2 bg-white rounded border">
                            <small class="text-muted"><strong>Technical Details:</strong></small><br>
                            <small class="text-muted">Jonathan Software will send POST to: <code>{Base_URL}/admin-api/sso/login</code> with Bearer token and user info (email, name). DigiSign will create/login the user and return a signed URL for auto-login.</small>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                         <div class="col-md-6">
                            <label class="form-label">Base URL</label>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ url('/') }}" readonly id="apiUrlInput">
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('apiUrlInput')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <div class="form-text">Use this as the <strong>URL</strong> field in Jonathan Software SSO settings.</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Shared Secret API Key</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="provider_api_key" value="{{ $settings['provider_api_key'] ?? '' }}" readonly id="apiKeyInput">
                                <button class="btn btn-warning" type="button" onclick="generateApiKey()">
                                    <i class="bi bi-arrow-clockwise"></i> Generate
                                </button>
                                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('apiKeyInput')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <div class="form-text">Use this as the <strong>API Key</strong> field in Jonathan Software SSO settings.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="mt-5 mb-5 ds-animate ds-animate-delay-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 d-flex flex-wrap gap-3 justify-content-between align-items-center bg-light rounded">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Save Configuration</h5>
                    <p class="mb-0 text-muted">Review your changes carefully before saving.</p>
                </div>
                <button type="submit" class="btn btn-success btn-lg px-5" id="saveSettingsBtn">
                    <i class="bi bi-check-circle me-2"></i> Save All Settings
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function generateApiKey() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < 64; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('apiKeyInput').value = result;
}

function copyToClipboard(elementId) {
    const copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999); 
    navigator.clipboard.writeText(copyText.value).then(() => {
        // Optional: Show tooltip or toast
    });
}
</script>
@endpush
@endsection
