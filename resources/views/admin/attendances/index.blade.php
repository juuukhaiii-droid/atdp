@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="page-title">
                <i class="fas fa-list me-2"></i> Attendance Records
            </h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h5 class="mb-0">Filter Records</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Employee</label>
                            <select name="employee_id" class="form-select">
                                <option value="">All Employees</option>
                                @foreach ($records->unique('employee_id') as $record)
                                    <option value="{{ $record->employee_id }}"
                                            {{ request('employee_id') == $record->employee_id ? 'selected' : '' }}>
                                        {{ $record->employee->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All</option>
                                <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                                <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                                <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i> Search
                            </button>
                            <a href="{{ route('admin.attendances.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo me-2"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover table-modern mb-0">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Status</th>
                                <th>Late Minutes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $record)
                                <tr>
                                    <td>
                                        <strong>{{ $record->employee->full_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $record->employee->employee_code }}</small>
                                    </td>
                                    <td>{{ $record->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if ($record->check_in_time)
                                            <span class="badge bg-success">
                                                {{ \Carbon\Carbon::parse($record->check_in_time)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="text-muted">--:--</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($record->check_out_time)
                                            <span class="badge bg-warning">
                                                {{ \Carbon\Carbon::parse($record->check_out_time)->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="text-muted">Not checked out</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($record->status === 'present')
                                            <span class="status-badge status-present">Present</span>
                                        @elseif ($record->status === 'late')
                                            <span class="status-badge status-late">Late</span>
                                        @else
                                            <span class="status-badge status-absent">Absent</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($record->late_minutes > 0)
                                            <span class="late-time-text">{{ $record->late_minutes }} min</span>
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.attendances.show', [$record->employee_id, $record->attendance_date]) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox text-muted" style="font-size: 2rem;"></i>
                                        <p class="text-muted mt-2">No attendance records found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($records->hasPages())
                <div class="mt-4">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
