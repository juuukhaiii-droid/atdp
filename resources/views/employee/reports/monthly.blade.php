@extends('layouts.app')

@section('content')
<div class="container">
    <div class="mb-4">
        <h1 class="page-title mb-1">Attendance Reports</h1>
        <p class="page-desc mb-0">Filter attendance by employee, day, month, or year</p>
    </div>

    <div class="card section-card mb-4">
        <div class="card-header">
            Report Filters
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.attendance') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Employee</label>
                        <select name="employee_id" class="form-select form-select-lg rounded-3">
                            <option value="">All Employees</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->employee_code }} - {{ $employee->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Day</label>
                        <input type="date" name="date" class="form-control form-control-lg rounded-3" value="{{ request('date') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Month</label>
                        <input type="month" name="month" class="form-control form-control-lg rounded-3" value="{{ request('month') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Year</label>
                        <input type="number" name="year" class="form-control form-control-lg rounded-3" value="{{ request('year') }}" placeholder="2026">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-primary px-4 py-2 rounded-3 fw-semibold">Filter</button>
                    <a href="{{ route('admin.reports.attendance') }}" class="btn btn-light border px-4 py-2 rounded-3 fw-semibold">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="summary-label">Total Records</div>
                    <h3 class="summary-value">{{ $totalRecords }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="summary-label">Present</div>
                    <h3 class="summary-value" style="color:#16a34a;">{{ $presentCount }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="summary-label">Late</div>
                    <h3 class="summary-value" style="color:#f59e0b;">{{ $lateCount }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <div class="summary-label">Total Late Time</div>
                    @php
                        $lateHours = floor($totalLateMinutes / 60);
                        $lateMinutes = $totalLateMinutes % 60;
                    @endphp
                    <h3 class="summary-value" style="color:#ea580c;">
                        {{ str_pad($lateHours, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($lateMinutes, 2, '0', STR_PAD_LEFT) }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card section-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Attendance Result</span>
            <span class="muted-small">{{ $records->count() }} result(s)</span>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Point</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                        <th>Late Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ $record->attendance_date }}</td>

                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $record->employee->full_name ?? '-' }}</span>
                                    <span class="muted-small">{{ $record->employee->employee_code ?? '-' }}</span>
                                </div>
                            </td>

                            <td>{{ $record->employee->department->name ?? '-' }}</td>
                            <td>{{ $record->attendancePoint->name ?? '-' }}</td>
                            <td>{{ $record->check_in_time ?? '-' }}</td>
                            <td>{{ $record->check_out_time ?? '-' }}</td>

                            <td>
                                @if($record->status === 'late')
                                    <span class="status-badge status-late">Late</span>
                                @elseif($record->status === 'present')
                                    <span class="status-badge status-present">Present</span>
                                @else
                                    <span class="status-badge status-absent">{{ ucfirst($record->status) }}</span>
                                @endif
                            </td>

                            <td>
                                @php
                                    $hours = floor($record->late_minutes / 60);
                                    $minutes = $record->late_minutes % 60;
                                @endphp

                                @if($record->late_minutes > 0)
                                    <span class="late-time-text">
                                        {{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @else
                                    <span class="text-muted">00:00</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
