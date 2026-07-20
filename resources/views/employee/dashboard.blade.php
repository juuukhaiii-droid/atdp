@extends('layouts.app')

@section('content')
<div class="container-fluid px-2 px-md-4 py-3 py-md-4">

    {{-- Welcome Header --}}
    <div class="row mb-3 mb-md-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body p-3 p-md-4">
                    <h2 class="fw-bold mb-1 fs-5 fs-md-4">
                        Welcome, {{ auth()->user()->name }}! 👋
                    </h2>
                    <p class="mb-0 opacity-75 small">
                        <i class="fas fa-calendar-day me-1"></i> {{ now()->format('l, F d, Y') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Today's Quick Status --}}
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 quick-status-card status-card">
                <div class="card-body p-3 p-md-4 text-center">
                    <div class="status-icon mb-3">
                        @if(isset($todayAttendance))
                            @if($todayAttendance->check_out_time)
                                <i class="fas fa-check-circle text-success" style="font-size: 2.5rem;"></i>
                                <span class="badge bg-success position-absolute top-0 end-0">Out</span>
                            @else
                                <i class="fas fa-sign-in-alt text-primary" style="font-size: 2.5rem;"></i>
                                <span class="badge bg-primary position-absolute top-0 end-0">In</span>
                            @endif
                        @else
                            <i class="fas fa-times-circle text-secondary" style="font-size: 2.5rem;"></i>
                            <span class="badge bg-secondary position-absolute top-0 end-0">Idle</span>
                        @endif
                    </div>
                    <h6 class="text-muted small mb-0 fw-semibold">Today's Status</h6>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 quick-status-card">
                <div class="card-body p-3 p-md-4 text-center">
                    <div class="time-display mb-3">
                        <i class="fas fa-clock text-success me-2" style="font-size: 1.5rem;"></i>
                        <h3 class="fw-bold text-success d-inline">
                            @if(isset($todayAttendance))
                                {{ \Carbon\Carbon::parse($todayAttendance->check_in_time)->format('H:i') }}
                            @else
                                <span style="color: #ccc;">--:--</span>
                            @endif
                        </h3>
                    </div>
                    <h6 class="text-muted small mb-0 fw-semibold">Check In</h6>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 quick-status-card">
                <div class="card-body p-3 p-md-4 text-center">
                    <div class="time-display mb-3">
                        <i class="fas fa-sign-out-alt text-warning me-2" style="font-size: 1.5rem;"></i>
                        <h3 class="fw-bold text-warning d-inline">
                            @if(isset($todayAttendance) && $todayAttendance->check_out_time)
                                {{ \Carbon\Carbon::parse($todayAttendance->check_out_time)->format('H:i') }}
                            @else
                                <span style="color: #ccc;">--:--</span>
                            @endif
                        </h3>
                    </div>
                    <h6 class="text-muted small mb-0 fw-semibold">Check Out</h6>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100 quick-status-card">
                <div class="card-body p-3 p-md-4 text-center">
                    <div class="total-display mb-3">
                        <i class="fas fa-calendar-check text-info me-2" style="font-size: 1.5rem;"></i>
                        <h3 class="fw-bold text-info d-inline">{{ $totalAttendance ?? 0 }}</h3>
                    </div>
                    <h6 class="text-muted small mb-0 fw-semibold">Total Days</h6>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly Statistics - Enhanced Design --}}
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100 stats-card gradient-success">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="text-muted small mb-2 fw-semibold">This Month</h6>
                            <div class="d-flex align-items-baseline">
                                <h2 class="fw-bold text-success mb-0">{{ $presentDays ?? 0 }}</h2>
                                <small class="text-success ms-2">days</small>
                            </div>
                            <small class="text-success fw-semibold">Present Days</small>
                        </div>
                        <div class="stat-icon-box bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-check-circle text-success" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                    <div class="progress" style="height: 5px; border-radius: 10px;">
                        @php
                            $daysInMonth = now()->daysInMonth;
                            $presentPercentage = $daysInMonth > 0 ? round(($presentDays ?? 0) / $daysInMonth * 100) : 0;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $presentPercentage }}%;" aria-valuenow="{{ $presentPercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100 stats-card gradient-warning">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="text-muted small mb-2 fw-semibold">This Month</h6>
                            <div class="d-flex align-items-baseline">
                                <h2 class="fw-bold text-warning mb-0">{{ $lateDays ?? 0 }}</h2>
                                <small class="text-warning ms-2">days</small>
                            </div>
                            <small class="text-warning fw-semibold">Late Days</small>
                        </div>
                        <div class="stat-icon-box bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-hourglass-end text-warning" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                    <div class="progress" style="height: 5px; border-radius: 10px;">
                        @php
                            $latePercentage = $daysInMonth > 0 ? round(($lateDays ?? 0) / $daysInMonth * 100) : 0;
                        @endphp
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $latePercentage }}%;" aria-valuenow="{{ $latePercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100 stats-card gradient-primary">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="text-muted small mb-2 fw-semibold">Attendance Rate</h6>
                            <div class="d-flex align-items-baseline">
                                <h2 class="fw-bold text-primary mb-0">
                                    @php
                                        $rate = $presentDays > 0 ? round(($presentDays / $daysInMonth) * 100) : 0;
                                    @endphp
                                    {{ $rate }}%
                                </h2>
                            </div>
                            <small class="text-primary fw-semibold">{{ $presentDays }}/{{ $daysInMonth }} days</small>
                        </div>
                        <div class="stat-icon-box bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="fas fa-chart-pie text-primary" style="font-size: 1.8rem;"></i>
                        </div>
                    </div>
                    <div class="progress" style="height: 5px; border-radius: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $rate }}%;" aria-valuenow="{{ $rate }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons - Enhanced with Better Visual Hierarchy --}}
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <div class="col-12 col-md-6">
            <a href="{{ route('employee.attendance.checkin.form') }}"
               class="card border-0 shadow-sm action-card action-primary h-100 text-decoration-none">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted small mb-1 fw-semibold">Quick Action</h6>
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-qrcode me-2"></i>Scan QR Code
                        </h5>
                        <small class="text-muted d-block mt-2">Check In / Out with location</small>
                    </div>
                    <i class="fas fa-arrow-right text-primary" style="font-size: 1.5rem; opacity: 0.5;"></i>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6">
            <a href="{{ route('employee.attendance.history') }}"
               class="card border-0 shadow-sm action-card action-secondary h-100 text-decoration-none">
                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted small mb-1 fw-semibold">Records</h6>
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-history me-2"></i>Attendance History
                        </h5>
                        <small class="text-muted d-block mt-2">View all your records</small>
                    </div>
                    <i class="fas fa-arrow-right text-primary" style="font-size: 1.5rem; opacity: 0.5;"></i>
                </div>
            </a>
        </div>
    </div>

    {{-- Employee Information Card - Enhanced --}}
    @if(auth()->user()->employee)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm employee-info-card">
                <div class="card-header bg-gradient-light border-0 py-3">
                    <h6 class="mb-0 fw-bold">
                        <i class="fas fa-id-badge me-2 text-primary"></i>Employee Details
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="info-item">
                                <small class="text-muted d-block mb-2 fw-semibold">
                                    <i class="fas fa-hashtag me-1"></i>Employee Code
                                </small>
                                <p class="mb-0 fw-bold fs-6 text-dark">{{ auth()->user()->employee->employee_code ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-item">
                                <small class="text-muted d-block mb-2 fw-semibold">
                                    <i class="fas fa-building me-1"></i>Department
                                </small>
                                <p class="mb-0 fw-bold fs-6 text-dark">{{ auth()->user()->employee->department->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-item">
                                <small class="text-muted d-block mb-2 fw-semibold">
                                    <i class="fas fa-briefcase me-1"></i>Position
                                </small>
                                <p class="mb-0 fw-bold fs-6 text-dark">{{ auth()->user()->employee->position ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-item">
                                <small class="text-muted d-block mb-2 fw-semibold">
                                    <i class="fas fa-clock me-1"></i>Shift
                                </small>
                                <p class="mb-0 fw-bold fs-6 text-dark">{{ auth()->user()->employee->shift->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@push('styles')
<style>
    /* ============ Enhanced Dashboard Styles ============ */
    
    /* Quick Status Cards */
    .quick-status-card {
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .quick-status-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 100%);
        pointer-events: none;
    }

    .quick-status-card:hover {
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12) !important;
        transform: translateY(-4px);
    }

    .status-icon {
        position: relative;
        display: inline-block;
    }

    .status-icon .badge {
        padding: 0.35rem 0.6rem;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .time-display, .total-display {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    /* Stats Cards with Gradients */
    .stats-card {
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0) 100%);
        pointer-events: none;
    }

    .stats-card:hover {
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.1) !important;
        transform: translateY(-6px);
    }

    .gradient-success {
        background: linear-gradient(135deg, #f8fffe 0%, #f0fffe 100%);
        border-left: 4px solid #28a745 !important;
    }

    .gradient-warning {
        background: linear-gradient(135deg, #fffcf8 0%, #fffaf0 100%);
        border-left: 4px solid #ffc107 !important;
    }

    .gradient-primary {
        background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
        border-left: 4px solid #007bff !important;
    }

    .stat-icon-box {
        transition: all 0.3s ease;
    }

    .stats-card:hover .stat-icon-box {
        transform: scale(1.1) rotate(5deg);
    }

    .progress {
        background-color: rgba(0, 0, 0, 0.05);
    }

    /* Action Cards */
    .action-card {
        border-radius: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        color: inherit !important;
        background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
    }

    .action-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 100%);
        pointer-events: none;
    }

    .action-primary {
        border-left: 5px solid #007bff !important;
    }

    .action-secondary {
        border-left: 5px solid #6c757d !important;
    }

    .action-card:hover {
        box-shadow: 0 16px 40px rgba(0, 123, 255, 0.15) !important;
        transform: translateY(-5px);
    }

    .action-card:hover i {
        opacity: 1 !important;
        transform: translateX(4px);
    }

    .action-card i {
        transition: all 0.3s ease;
    }

    .action-card h5 {
        color: #212529;
    }

    /* Employee Info Card */
    .employee-info-card {
        border-radius: 16px;
        overflow: hidden;
    }

    .bg-gradient-light {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    }

    .info-item {
        padding: 1rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 12px;
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
    }

    .info-item:hover {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-color: #dee2e6;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }

    .info-item small {
        display: flex;
        align-items: center;
    }

    /* Welcome Header Enhancement */
    .bg-gradient {
        position: relative;
        overflow: hidden;
    }

    .bg-gradient::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        animation: float 15s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translate(0px, 0px); }
        50% { transform: translate(30px, -30px); }
    }

    .card-body {
        position: relative;
        z-index: 1;
    }

    /* General Enhancements */
    .card {
        border-radius: 16px;
    }

    /* Smooth Scrolling and Transitions */
    * {
        scroll-behavior: smooth;
    }

    /* Mobile Optimizations */
    @media (max-width: 576px) {
        .quick-status-card,
        .stats-card,
        .action-card {
            border-radius: 12px;
        }

        .quick-status-card:hover,
        .stats-card:hover,
        .action-card:hover {
            transform: translateY(-2px);
        }

        .card-body {
            padding: 1rem !important;
        }

        .info-item {
            padding: 0.75rem;
        }

        h2 {
            font-size: 1.5rem;
        }

        .stat-icon-box {
            width: 50px !important;
            height: 50px !important;
        }

        .stat-icon-box i {
            font-size: 1.3rem !important;
        }

        .fs-md-4 {
            font-size: 1.3rem !important;
        }
    }

    @media (max-width: 768px) {
        .container-fluid {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }

        .row {
            --bs-gutter-x: 0.5rem;
        }

        .card-body {
            padding: 1.25rem !important;
        }
    }

    @media (min-width: 768px) {
        .row {
            --bs-gutter-x: 1rem;
        }

        .card-body {
            padding: 1.5rem !important;
        }
    }

    /* Touch-friendly sizing */
    .badge {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Better spacing */
    .row {
        --bs-gutter-y: 0.5rem;
    }

    @media (min-width: 768px) {
        .row {
            --bs-gutter-y: 1rem;
        }
    }

    /* Icon Styling */
    i {
        transition: all 0.3s ease;
    }

    /* Typography Improvements */
    h2, h3, h4, h5, h6 {
        letter-spacing: -0.5px;
    }

    .fw-semibold {
        font-weight: 600 !important;
    }

    /* Focus states for accessibility */
    .action-card:focus,
    a:focus {
        outline: 2px solid #007bff;
        outline-offset: 2px;
    }

    /* Button Action Legacy Support */
    .btn-action {
        border-radius: 12px;
        min-height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    .btn-action:hover {
        transform: translateY(-2px);
    }

    .btn-action small {
        font-size: 0.75rem;
        opacity: 0.8;
        margin-top: 2px;
    }

    /* Status Card Badge Position */
    .status-card .badge {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
    }
</style>
@endpush

@endsection
