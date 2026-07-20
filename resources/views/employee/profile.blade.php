@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12 col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i> My Profile
                    </h4>
                </div>
                <div class="card-body">
                    @if ($employee)
                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                @if ($employee->photo)
                                    <img src="{{ asset('storage/' . $employee->photo) }}"
                                         alt="Profile Photo"
                                         class="img-fluid rounded-circle"
                                         style="max-width: 200px;">
                                @else
                                    <div class="avatar-placeholder bg-light rounded-circle d-inline-flex align-items-center justify-content-center"
                                         style="width: 200px; height: 200px;">
                                        <i class="fas fa-user" style="font-size: 4rem; color: #999;"></i>
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-8">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted small">Full Name</label>
                                        <p class="h5 fw-bold">{{ $employee->full_name }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small">Employee Code</label>
                                        <p class="h5 fw-bold code-pill">{{ $employee->employee_code }}</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted small">Email</label>
                                        <p class="mb-0">
                                            <a href="mailto:{{ $employee->email }}">{{ $employee->email ?? 'N/A' }}</a>
                                        </p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small">Phone</label>
                                        <p class="mb-0">
                                            <a href="tel:{{ $employee->phone }}">{{ $employee->phone ?? 'N/A' }}</a>
                                        </p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted small">Department</label>
                                        <p class="mb-0 fw-bold">{{ $employee->department->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small">Position</label>
                                        <p class="mb-0 fw-bold">{{ $employee->position ?? 'N/A' }}</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label text-muted small">Shift</label>
                                        <p class="mb-0 fw-bold">{{ $employee->shift->name ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label text-muted small">Status</label>
                                        <p class="mb-0">
                                            @if ($employee->status === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                @if ($employee->shift)
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label text-muted small">Work Time</label>
                                            <p class="mb-0">
                                                {{ \Carbon\Carbon::parse($employee->shift->start_time)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('H:i') }}
                                            </p>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-muted small">Late After</label>
                                            <p class="mb-0">{{ \Carbon\Carbon::parse($employee->shift->late_after)->format('H:i') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <div class="col-12">
                                <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Employee record not found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
