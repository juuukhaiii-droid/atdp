@extends('layouts.employee-app')

@section('title', 'Confirm Attendance')

@section('content')

    <div class="section-title" style="margin-top:0;">
        {{ $nextAction === 'in' ? 'Confirm Check In' : 'Confirm Check Out' }}
    </div>

    <div class="app-card confirm-hero mb-3">
        <div class="card-body text-center">
            <div class="confirm-icon {{ $nextAction === 'in' ? 'confirm-icon-in' : 'confirm-icon-out' }}">
                <i class="fas {{ $nextAction === 'in' ? 'fa-arrow-right-to-bracket' : 'fa-arrow-right-from-bracket' }}"></i>
            </div>
            <div class="confirm-point-name">{{ $attendancePoint->name }}</div>
            <div class="confirm-point-sub">{{ now()->format('l, d M Y \\a\\t h:i A') }}</div>
        </div>
    </div>

    <div class="app-card wifi-status-card mb-3" id="wifiStatusCard">
        <div class="card-body d-flex align-items-center gap-2">
            <i class="fas fa-spinner fa-spin" id="wifiStatusIcon"></i>
            <span id="wifiStatusText">Checking office WiFi&hellip;</span>
        </div>
    </div>

    <form method="POST" action="{{ route('attendance.submit', $token) }}">
        @csrf
        <button type="submit" id="checkinBtn" class="btn btn-scan-cta w-100 py-3 fw-bold confirm-btn" disabled>
            <i class="fas fa-check me-2"></i>
            Confirm {{ $nextAction === 'in' ? 'Check In' : 'Check Out' }}
        </button>
    </form>

    <a href="{{ route('employee.dashboard') }}" class="btn confirm-back-btn w-100 mt-3">
        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
    </a>

    <div class="app-card info-card mt-3 mb-2">
        <div class="card-body p-3">
            <small class="text-muted d-block mb-2">
                <i class="fas fa-wifi me-1"></i> <strong>Office WiFi Required</strong>
            </small>
            <small class="text-muted">
                You must be connected to the office WiFi network to {{ $nextAction === 'in' ? 'check in' : 'check out' }}.
            </small>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .confirm-hero {
        background: linear-gradient(160deg, #ffffff 0%, #fef7f7 100%);
    }

    .confirm-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin: 0 auto 12px;
    }

    .confirm-icon-in { background: #dcfce7; color: #16a34a; }
    .confirm-icon-out { background: #fef3c7; color: #f59e0b; }

    .confirm-point-name {
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .confirm-point-sub {
        font-size: 12.5px;
        color: var(--text-soft);
        margin-top: 2px;
    }

    .wifi-status-card .card-body {
        padding: 14px 16px;
        font-size: 13.5px;
        font-weight: 600;
    }

    .wifi-status-card.wifi-connected { background: #dcfce7; color: #166534; }
    .wifi-status-card.wifi-connected i { color: #16a34a; }

    .wifi-status-card.wifi-disconnected { background: #fee2e2; color: #991b1b; }
    .wifi-status-card.wifi-disconnected i { color: #dc2626; }

    .btn-scan-cta {
        background: linear-gradient(135deg, var(--brand-primary) 0%, #b91c1c 100%);
        color: #fff;
        font-weight: 700;
        border-radius: 14px;
        padding: 13px;
        border: 0;
        box-shadow: 0 10px 22px rgba(220, 38, 38, 0.25);
    }

    .btn-scan-cta:hover { color: #fff; opacity: 0.95; }

    .confirm-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        box-shadow: none;
    }

    .confirm-back-btn {
        border: 1px solid var(--border-soft);
        color: var(--text-soft);
        border-radius: 14px;
        padding: 12px;
        font-weight: 600;
        background: #fff;
    }

    .confirm-back-btn:hover { color: var(--text-main); background: #f8fafc; }

    .info-card .card-body { padding: 14px 16px; }
</style>
@endpush

@push('scripts')
<script>
    let wifiPollId = null;

    async function checkWiFiConnection() {
        try {
            const response = await fetch("{{ route('api.verify-network') }}", {
                cache: 'no-cache',
                headers: { 'Accept': 'application/json' },
            });

            const data = await response.json();

            const card = document.getElementById('wifiStatusCard');
            const icon = document.getElementById('wifiStatusIcon');
            const text = document.getElementById('wifiStatusText');
            const button = document.getElementById('checkinBtn');

            card.classList.remove('wifi-connected', 'wifi-disconnected');

            if (data.verified) {
                card.classList.add('wifi-connected');
                icon.className = 'fas fa-wifi';
                text.textContent = 'Connected to Office WiFi';
                button.disabled = false;
            } else {
                card.classList.add('wifi-disconnected');
                icon.className = 'fas fa-triangle-exclamation';
                text.textContent = 'Office WiFi Not Detected';
                button.disabled = true;
            }
        } catch (e) {
            console.log(e);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        checkWiFiConnection();
        wifiPollId = setInterval(checkWiFiConnection, 5000);
    });
</script>
@endpush
