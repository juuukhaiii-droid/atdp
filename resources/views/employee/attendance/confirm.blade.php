@extends('layouts.app')
@push('styles')
    <style>
        .checkin-card {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
        }

        .card-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            border-radius: 16px 16px 0 0 !important;
        }

        .card-body {
            padding: 2rem !important;
        }

        .checkin-btn {
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .checkin-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .wifi-connected {
            background-color: #d4edda !important;
            border-left-color: #28a745 !important;
        }

        .wifi-connected i {
            color: #28a745 !important;
        }

        .wifi-disconnected {
            background-color: #f8d7da !important;
            border-left-color: #dc3545 !important;
        }

        .wifi-disconnected i {
            color: #dc3545 !important;
        }
    </style>
@endpush
@section('content')
    <div class="container-fluid px-3 py-3">
        <div class="row">
            <div class="col-12 col-md-8 mx-auto">
                <div class="card shadow-sm border-0 checkin-card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0 fw-bold">
                            <i class="fas fa-qrcode me-2"></i>
                            {{ $nextAction === 'in' ? 'Check In' : 'Check Out' }} — {{ $attendancePoint->name }}
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong><i class="fas fa-exclamation-circle me-2"></i>Error:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="alert alert-info alert-icon mb-4" role="alert" id="wifiStatusCard">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-spinner fa-spin me-2"></i>
                                <span>Checking WiFi connection...</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('attendance.submit', $token) }}">
                            @csrf
                            <button type="submit" id="checkinBtn" class="btn btn-primary w-100 py-3 fw-bold checkin-btn" disabled>
                                <i class="fas fa-check me-2"></i>
                                Confirm {{ $nextAction === 'in' ? 'Check In' : 'Check Out' }}
                            </button>
                        </form>

                        <div class="mt-4 text-center">
                            <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-secondary px-4 py-2">
                                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mt-3 info-card">
                    <div class="card-body p-3">
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-wifi me-1"></i> <strong>Office WiFi Required</strong>
                        </small>
                        <small class="text-muted">
                            You must be connected to the office WiFi network to
                            {{ $nextAction === 'in' ? 'check in' : 'check out' }}.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let wifiPollId = null;

        async function checkWiFiConnection() {
            try {
                const response = await fetch("{{ route('api.verify-network') }}", {
                    cache: "no-cache",
                    headers: { "Accept": "application/json" }
                });

                const data = await response.json();

                const statusCard = document.getElementById('wifiStatusCard');
                const button = document.getElementById('checkinBtn');

                if (data.verified) {
                    statusCard.className = "alert alert-success wifi-connected";
                    statusCard.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Connected to Office WiFi</strong>
                        </div>`;
                    button.disabled = false;
                } else {
                    statusCard.className = "alert alert-danger wifi-disconnected";
                    statusCard.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-times-circle me-2"></i>
                            <strong>Office WiFi Not Detected</strong>
                        </div>`;
                    button.disabled = true;
                }
            } catch (e) {
                console.log(e);
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            checkWiFiConnection();
            wifiPollId = setInterval(checkWiFiConnection, 5000);
        });
    </script>
@endpush
