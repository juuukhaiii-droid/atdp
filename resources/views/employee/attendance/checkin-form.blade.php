@extends('layouts.app')
@push('styles')
    <style>
        .checkin-card {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            border-radius: 16px 16px 0 0 !important;
        }

        .card-body {
            padding: 2rem !important;
        }

        .alert-icon {
            border-left: 4px solid #0c63e4;
            background-color: #e7f1ff;
            border-radius: 8px;
        }

        .alert-icon i {
            color: #0c63e4;
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

        .checkin-btn:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 123, 255, 0.4);
        }

        .checkin-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .info-card {
            border-radius: 12px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
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

        @media (max-width: 576px) {
            .container-fluid {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            .card-body {
                padding: 1.5rem !important;
            }

            .card-header {
                padding: 1.25rem !important;
            }

            .card-header h4 {
                font-size: 1.25rem;
            }

            .checkin-btn {
                min-height: 48px;
                font-size: 0.95rem;
            }

            .alert-icon {
                font-size: 0.95rem;
            }

            .btn-outline-secondary {
                font-size: 0.95rem;
            }
        }

        button,
        a {
            transition: all 0.2s ease;
        }

        .btn:focus {
            outline: 2px solid #0c63e4;
            outline-offset: 2px;
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .spinner-border-sm {
            width: 1.2rem;
            height: 1.2rem;
        }
    </style>
@endpush
@section('content')
    <div class="container-fluid px-3 py-3">
        <div class="row">
            <div class="col-12 col-md-8 mx-auto">
                <div class="card shadow-sm border-0 checkin-card">
                    <div class="card-header bg-primary text-white rounded-top-3">
                        <h4 class="mb-0 fw-bold">
                            <i class="fas fa-wifi me-2"></i>
                            {{ $nextAction === 'checkin' ? 'Check In' : 'Check Out' }} (WiFi Verified)
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

                        <form id="checkinForm" method="POST"
                            action="{{ $nextAction === 'checkin' ? route('employee.attendance.checkin') : route('employee.attendance.checkout') }}">
                            @csrf
                            <input type="hidden" id="wifiVerified" name="wifi_verified" value="0">

                            <div class="mb-4">
                                <input type="hidden" id="wifiVerified" value="0">

                                <button type="button" id="checkinBtn" class="btn btn-primary w-100 py-3 fw-bold checkin-btn"
                                    disabled>
                                    <i class="fas fa-camera me-2"></i>
                                    {{ $nextAction === 'checkin' ? 'Check In' : 'Check Out' }}
                                </button>

                                <div id="connectionInfo" class="alert alert-secondary d-none mt-3">
                                    <small>
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong id="connectionStatus"></strong><br>
                                        <span id="connectionDetails"></span>
                                    </small>
                                </div>
                            </div>

                            <div id="connectionInfo" class="alert alert-secondary d-none" role="alert">
                                <small>
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong id="connectionStatus">Not Connected</strong>
                                    <br>
                                    <span id="connectionDetails">Please ensure you are connected to the office WiFi</span>
                                </small>
                            </div>
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
                            {{ $nextAction === 'checkin' ? 'check in' : 'check out' }}.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    @push('scripts')
            <script>

                let wifiPollId = null;

                async function checkWiFiConnection() {

            try {

                const response = await fetch("{{ route('api.verify-network') }}", {
                    cache: "no-cache",
                headers: {
                    "Accept": "application/json"
                    }
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
                </div>
                `;

                button.disabled = false;

                document.getElementById('wifiVerified').value = 1;

                document.getElementById('connectionInfo').classList.remove('d-none');
                document.getElementById('connectionStatus').innerHTML = "✓ Connected";
                document.getElementById('connectionDetails').innerHTML = data.network_info;

                } else {

                    statusCard.className = "alert alert-danger wifi-disconnected";

                statusCard.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-times-circle me-2"></i>
                    <strong>Office WiFi Not Detected</strong>
                </div>
                    `;

                    button.disabled = true;

                    document.getElementById('wifiVerified').value = 0;

                    document.getElementById('connectionInfo').classList.remove('d-none');
                    document.getElementById('connectionStatus').innerHTML = "✗ Not Connected";
                    document.getElementById('connectionDetails').innerHTML =
                        "Please connect to Office WiFi.";
                }

            } catch (e) {

                console.log(e);

            }

        }

        document.addEventListener("DOMContentLoaded", function () {

            checkWiFiConnection();

            wifiPollId = setInterval(checkWiFiConnection, 5000);

            document.getElementById("checkinBtn").addEventListener("click", function () {

                if (document.getElementById("wifiVerified").value != 1) {

                    alert("Please connect to Office WiFi.");

                    return;

                }

                clearInterval(wifiPollId);

                window.location.href = "{{ route('attendance.show.qr') }}";

            });

        });


        </script>
    @endpush