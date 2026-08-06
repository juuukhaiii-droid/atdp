@extends('layouts.employee-app')

@section('title', 'Dashboard')

@section('content')

    @php
        $durationLabel = null;
        if ($todayIn && $todayOut) {
            $minutes = $todayIn->scanned_at->diffInMinutes($todayOut->scanned_at);
            $durationLabel = sprintf('%dh %02dm worked today', intdiv($minutes, 60), $minutes % 60);
        } elseif ($todayIn) {
            $durationLabel = 'Checked in ' . $todayIn->scanned_at->diffForHumans();
        }

        $daysElapsed = min(now()->day, now()->daysInMonth);
        $attendanceRate = $daysElapsed > 0 ? min(100, round(($presentDays / $daysElapsed) * 100)) : 0;
    @endphp

    {{-- Employee Summary --}}
    <div class="section-title" style="margin-top:0;">Employee</div>
    <a href="{{ route('employee.profile') }}" class="app-card employee-summary mb-3">
        <div class="card-body d-flex align-items-center gap-3">
            @if ($employee->photo)
                <img src="{{ asset('files/' . $employee->photo) }}" alt="" class="dash-avatar">
            @else
                <div class="dash-avatar dash-avatar-placeholder"><i class="fas fa-user"></i></div>
            @endif

            <div class="flex-grow-1 min-w-0">
                <div class="dash-emp-name">{{ $employee->full_name }}</div>
                <div class="dash-emp-sub">{{ $employee->position ?? 'N/A' }} &middot; {{ $employee->department->name ?? 'N/A' }}</div>
            </div>

            <i class="fas fa-chevron-right dash-emp-chevron"></i>
        </div>
    </a>

    {{-- Status Hero --}}
    <div class="app-card status-hero mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="section-eyebrow">Today's Status</span>
                @if ($todayIn && !$todayOut)
                    <span class="status-pill status-pill-in"><i class="fas fa-circle-dot me-1"></i>Checked In</span>
                @elseif ($todayIn && $todayOut)
                    <span class="status-pill status-pill-done"><i class="fas fa-check me-1"></i>Complete</span>
                @else
                    <span class="status-pill status-pill-idle"><i class="fas fa-moon me-1"></i>Not Checked In</span>
                @endif
            </div>

            @if ($durationLabel)
                <div class="status-duration">{{ $durationLabel }}</div>
            @else
                <div class="status-duration status-duration-muted">You haven't checked in yet today</div>
            @endif

            <div class="row g-2 mt-2">
                <div class="col-6">
                    <div class="hero-time-box">
                        <div class="hero-time-label"><i class="fas fa-arrow-right-to-bracket text-success me-1"></i>Check In</div>
                        <div class="hero-time-value">{{ $todayIn ? $todayIn->scanned_at->format('h:i A') : '--:--' }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="hero-time-box">
                        <div class="hero-time-label"><i class="fas fa-arrow-right-from-bracket text-warning me-1"></i>Check Out</div>
                        <div class="hero-time-value">{{ $todayOut ? $todayOut->scanned_at->format('h:i A') : '--:--' }}</div>
                    </div>
                </div>
            </div>

            <a href="{{ route('attendance.show.qr') }}" class="btn btn-scan-cta w-100 mt-3">
                <i class="fas fa-qrcode me-2"></i>Scan QR to {{ $todayIn && !$todayOut ? 'Check Out' : 'Check In' }}
            </a>
        </div>
    </div>

    {{-- Monthly Stats --}}
    <div class="section-title" style="margin-top:0;">This Month</div>
    <div class="row g-2">
        <div class="col-4">
            <div class="app-card stat-tile">
                <div class="card-body text-center">
                    <div class="stat-icon stat-icon-green"><i class="fas fa-calendar-check"></i></div>
                    <div class="stat-tile-value">{{ $presentDays }}</div>
                    <div class="stat-tile-label">Present</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="app-card stat-tile">
                <div class="card-body text-center">
                    <div class="stat-icon stat-icon-amber"><i class="fas fa-clock"></i></div>
                    <div class="stat-tile-value">{{ $lateDays }}</div>
                    <div class="stat-tile-label">Late</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="app-card stat-tile">
                <div class="card-body text-center">
                    <div class="stat-icon stat-icon-blue"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-tile-value">{{ $totalAttendance }}</div>
                    <div class="stat-tile-label">Total Days</div>
                </div>
            </div>
        </div>
    </div>

    <div class="app-card rate-card mt-2 mb-3">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="rate-label">Attendance Rate</span>
                <span class="rate-value">{{ $attendanceRate }}%</span>
            </div>
            <div class="rate-track">
                <div class="rate-fill" style="width: {{ $attendanceRate }}%;"></div>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="section-title">Recent Activity</div>
    <div class="app-card">
        <div class="card-body p-0">
            @forelse ($recentActivity as $record)
                <div class="activity-row {{ !$loop->last ? 'activity-row-border' : '' }}">
                    <div class="activity-icon {{ $record->type === 'in' ? 'activity-icon-in' : 'activity-icon-out' }}">
                        <i class="fas {{ $record->type === 'in' ? 'fa-arrow-right-to-bracket' : 'fa-arrow-right-from-bracket' }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="activity-title">{{ $record->type === 'in' ? 'Checked In' : 'Checked Out' }}</div>
                        <div class="activity-sub">{{ $record->attendancePoint->name ?? 'Unknown location' }}</div>
                    </div>
                    <div class="text-end">
                        <div class="activity-time">{{ $record->scanned_at->format('h:i A') }}</div>
                        <div class="activity-date">
                            {{ $record->scanned_at->isToday() ? 'Today' : ($record->scanned_at->isYesterday() ? 'Yesterday' : $record->scanned_at->format('d M')) }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4 small">No attendance records yet.</div>
            @endforelse
        </div>
    </div>

@endsection

@push('styles')
<style>
    .section-eyebrow {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-soft);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .status-hero {
        background: linear-gradient(160deg, #ffffff 0%, #fef7f7 100%);
    }

    .status-duration {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text-main);
    }

    .status-duration-muted { color: var(--text-soft); }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 5px 11px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
    }

    .status-pill-in { background: #dbeafe; color: #1e40af; }
    .status-pill-done { background: #dcfce7; color: #166534; }
    .status-pill-idle { background: #f1f5f9; color: #64748b; }

    .hero-time-box {
        background: #f8fafc;
        border-radius: 14px;
        padding: 12px 14px;
    }

    .hero-time-label {
        font-size: 12px;
        color: var(--text-soft);
        font-weight: 600;
        margin-bottom: 4px;
    }

    .hero-time-value {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

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

    .stat-tile .card-body { padding: 16px 8px 14px; }

    .stat-icon {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        margin: 0 auto 8px;
    }

    .stat-icon-green { background: #dcfce7; color: #16a34a; }
    .stat-icon-amber { background: #fef3c7; color: #f59e0b; }
    .stat-icon-blue { background: #dbeafe; color: #2563eb; }

    .stat-tile-value {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1;
    }

    .stat-tile-label {
        font-size: 11px;
        color: var(--text-soft);
        font-weight: 600;
        margin-top: 4px;
    }

    .rate-card .card-body { padding: 14px 16px; }

    .rate-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-soft);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .rate-value {
        font-size: 13px;
        font-weight: 800;
        color: var(--brand-primary);
    }

    .rate-track {
        height: 8px;
        background: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
    }

    .rate-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--brand-primary) 0%, #f97316 100%);
        border-radius: 999px;
        transition: width 0.4s ease;
    }

    .activity-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
    }

    .activity-row-border { border-bottom: 1px solid var(--border-soft); }

    .activity-icon {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    .activity-icon-in { background: #dcfce7; color: #16a34a; }
    .activity-icon-out { background: #fef3c7; color: #f59e0b; }

    .activity-title { font-weight: 700; font-size: 13.5px; }
    .activity-sub { font-size: 12px; color: var(--text-soft); }
    .activity-time { font-weight: 700; font-size: 12.5px; }
    .activity-date { font-size: 11px; color: var(--text-soft); }

    .employee-summary {
        display: block;
        text-decoration: none;
        color: inherit;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .employee-summary:hover {
        color: inherit;
        transform: translateY(-1px);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.09);
    }

    .dash-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .dash-avatar-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 1.3rem;
    }

    .min-w-0 { min-width: 0; }

    .dash-emp-name {
        font-weight: 800;
        font-size: 14.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dash-emp-sub {
        font-size: 12px;
        color: var(--text-soft);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dash-emp-chevron {
        color: var(--text-soft);
        font-size: 13px;
        flex-shrink: 0;
    }
</style>
@endpush
