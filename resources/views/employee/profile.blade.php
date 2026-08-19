@extends('layouts.employee-app')

@section('title', 'Profile')

@section('content')

    @if ($employee)
        <div class="app-card profile-hero mb-3">
            <div class="card-body">
                <div class="profile-hero-top">
                    @if ($employee->photo)
                        <img src="{{ asset('files/' . $employee->photo) }}" alt="Profile Photo" class="profile-avatar">
                    @else
                        <div class="profile-avatar profile-avatar-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif

                    <div class="profile-hero-info">
                        <div class="profile-name">{{ $employee->full_name }}</div>
                        <div class="profile-code">{{ $employee->employee_code }}</div>
                    </div>

                    @if ($employee->status === 'active')
                        <span class="status-pill status-pill-done"><i class="fas fa-check me-1"></i>Active</span>
                    @else
                        <span class="status-pill status-pill-idle"><i class="fas fa-pause me-1"></i>Inactive</span>
                    @endif
                </div>

                <div class="profile-hero-divider"></div>

                <div class="profile-since">
                    <i class="fas fa-calendar-days me-1"></i>Member since {{ $employee->created_at->format('M Y') }}
                </div>
            </div>
        </div>

        <div class="section-title" style="margin-top:0;">Contact</div>
        <div class="app-card mb-3">
            <div class="card-body p-0">
                <div class="profile-row">
                    <i class="fas fa-envelope profile-row-icon"></i>
                    <div>
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $employee->email ?? 'N/A' }}</div>
                    </div>
                </div>
                <div class="profile-row profile-row-border-top">
                    <i class="fas fa-phone profile-row-icon"></i>
                    <div>
                        <div class="info-label">Phone</div>
                        <div class="info-value">{{ $employee->phone ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title">Work Info</div>
        <div class="app-card mb-3">
            <div class="card-body p-0">
                <div class="profile-row">
                    <i class="fas fa-building profile-row-icon"></i>
                    <div>
                        <div class="info-label">Department</div>
                        <div class="info-value">{{ $employee->department->name ?? 'N/A' }}</div>
                    </div>
                </div>
                <div class="profile-row profile-row-border-top">
                    <i class="fas fa-briefcase profile-row-icon"></i>
                    <div>
                        <div class="info-label">Position</div>
                        <div class="info-value">{{ $employee->position ?? 'N/A' }}</div>
                    </div>
                </div>
                <div class="profile-row profile-row-border-top">
                    <i class="fas fa-user-clock profile-row-icon"></i>
                    <div>
                        <div class="info-label">Shift</div>
                        <div class="info-value">{{ $employee->shift->name ?? 'N/A' }}</div>
                    </div>
                </div>
                @if ($employee->shift)
                    <div class="profile-row profile-row-border-top">
                        <i class="fas fa-clock profile-row-icon"></i>
                        <div>
                            <div class="info-label">Work Time</div>
                            <div class="info-value">
                                {{ \Carbon\Carbon::parse($employee->shift->start_time)->format('h:i A') }} -
                                {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('h:i A') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="app-card">
            <div class="card-body text-center text-muted py-4">
                <i class="fas fa-triangle-exclamation mb-2" style="font-size:1.5rem;"></i>
                <div>Employee record not found.</div>
            </div>
        </div>
    @endif

@endsection

@push('styles')
<style>
    .profile-hero { background: linear-gradient(160deg, #ffffff 0%, #fef7f7 100%); }

    .profile-hero .card-body {
        padding: 16px 18px;
    }

    .profile-hero-top {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .profile-hero-info {
        flex: 1;
        min-width: 0;
    }

    .profile-avatar {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }

    .profile-avatar-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 1.4rem;
    }

    .profile-name {
        font-size: 1rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .profile-code {
        font-size: 12px;
        color: var(--text-soft);
        font-weight: 600;
        line-height: 1.3;
    }

    .profile-hero-divider {
        height: 1px;
        background: var(--border-soft);
        margin: 14px 0 10px;
    }

    .profile-since {
        font-size: 12px;
        color: var(--text-soft);
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.4;
        flex-shrink: 0;
    }

    .status-pill-done { background: #dcfce7; color: #166534; }
    .status-pill-idle { background: #f1f5f9; color: #64748b; }

    .profile-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
    }

    .profile-row-border-top { border-top: 1px solid var(--border-soft); }

    .profile-row-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f1f5f9;
        color: var(--brand-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }

    .info-label {
        font-size: 11.5px;
        color: var(--text-soft);
        font-weight: 600;
        margin-bottom: 3px;
    }

    .info-value { font-weight: 700; font-size: 14px; }

    .btn-logout {
        width: 100%;
        border: 1px solid #fecaca;
        background: #fff;
        color: var(--brand-primary);
        font-weight: 700;
        font-size: 14px;
        padding: 12px;
        border-radius: var(--radius-md);
        transition: background 0.15s ease;
    }

    .btn-logout:hover, .btn-logout:active {
        background: #fef2f2;
    }
</style>
@endpush
