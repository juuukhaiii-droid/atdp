@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('admin.attendances.index') }}" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left me-2"></i> Back to Records
            </a>
            <h2 class="page-title">
                <i class="fas fa-eye me-2"></i> Attendance Record Details
            </h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Record Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Employee Name</label>
                            <p class="h5 fw-bold">{{ $attendance->employee->full_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Employee Code</label>
                            <p class="h5 fw-bold code-pill">{{ $attendance->employee->employee_code }}</p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Department</label>
                            <p class="mb-0">{{ $attendance->employee->department->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Shift</label>
                            <p class="mb-0">{{ $attendance->employee->shift->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Attendance Date</label>
                            <p class="h5 fw-bold">{{ $attendance->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Status</label>
                            <p>
                                @if ($attendance->status === 'present')
                                    <span class="status-badge status-present">Present</span>
                                @elseif ($attendance->status === 'late')
                                    <span class="status-badge status-late">Late</span>
                                @else
                                    <span class="status-badge status-absent">Absent</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Check In Time</label>
                            @if ($attendance->check_in_time)
                                <p class="h5 fw-bold text-success">
                                    {{ \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s') }}
                                </p>
                            @else
                                <p class="text-muted">Not checked in</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Check Out Time</label>
                            @if ($attendance->check_out_time)
                                <p class="h5 fw-bold text-warning">
                                    {{ \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i:s') }}
                                </p>
                            @else
                                <p class="text-muted">Not checked out</p>
                            @endif
                        </div>
                    </div>

                    @if ($attendance->late_minutes > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-clock me-2"></i>
                            <strong>Late by {{ $attendance->late_minutes }} minutes</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
