@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">Employee Detail</h1>
            <p class="page-desc mb-0">View employee information and attendance history</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-warning px-4 py-2 rounded-3 fw-semibold">
                Edit Employee
            </a>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-light border px-4 py-2 rounded-3 fw-semibold">
                Back
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card section-card h-100">
                <div class="card-header">
                    Employee Information
                </div>

                <div class="card-body">
                    <div class="text-center mb-4">
                        @if($employee->photo)
                            <img
                                src="{{ asset('storage/' . $employee->photo) }}"
                                alt="Employee Photo"
                                class="employee-profile-img">
                        @else
                            <div class="employee-profile-placeholder">
                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <div class="muted-small">Employee Code</div>
                        <div class="fw-semibold">{{ $employee->employee_code }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="muted-small">Full Name</div>
                        <div class="fw-semibold">{{ $employee->full_name }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="muted-small">Department</div>
                        <div class="fw-semibold">{{ $employee->department->name ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="muted-small">Shift</div>
                        <div class="fw-semibold">{{ $employee->shift->name ?? '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="muted-small">Position</div>
                        <div class="fw-semibold">{{ $employee->position ?: '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="muted-small">Phone</div>
                        <div class="fw-semibold">{{ $employee->phone ?: '-' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="muted-small">Email</div>
                        <div class="fw-semibold">{{ $employee->email ?: '-' }}</div>
                    </div>

                    <div>
                        <div class="muted-small">Status</div>
                        <div class="mt-1">
                            @if($employee->status === 'active')
                                <span class="status-badge status-present">Active</span>
                            @else
                                <span class="status-badge status-absent">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <div class="summary-label">Total Attendance</div>
                            <h3 class="summary-value">{{ $totalAttendance }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <div class="summary-label">Present</div>
                            <h3 class="summary-value" style="color:#16a34a;">{{ $presentCount }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <div class="summary-label">Late</div>
                            <h3 class="summary-value" style="color:#f59e0b;">{{ $lateCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card section-card">
                <div class="card-header">
                    Attendance Filter
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.employees.show', $employee) }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Day</label>
                                <input type="date" name="date" class="form-control rounded-3" value="{{ request('date') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Month</label>
                                <input type="month" name="month" class="form-control rounded-3" value="{{ request('month') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Year</label>
                                <input type="number" name="year" class="form-control rounded-3" value="{{ request('year') }}" placeholder="2026">
                            </div>
                        </div>

                        <div class="mt-3 d-flex gap-2">
                            <button class="btn btn-primary px-4 rounded-3 fw-semibold">Filter</button>
                            <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-light border px-4 rounded-3 fw-semibold">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card section-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Monthly Attendance Calendar</span>
            <span class="muted-small">
                {{ \Carbon\Carbon::create($calendarYear, $calendarMonth, 1)->format('F Y') }}
            </span>
        </div>

        <div class="card-body">
            <div class="attendance-calendar-grid">
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $record = $monthlyAttendance[$day] ?? null;

                        $cardClass = 'absent';
                        $mark = '✗';
                        $label = 'Absent';

                        if ($record) {
                            if ($record->status === 'late') {
                                $cardClass = 'late-day';
                                $mark = '!';
                                $label = 'Late';
                            } else {
                                $cardClass = 'attended';
                                $mark = '✓';
                                $label = 'Attend';
                            }
                        }
                    @endphp

                    <div class="attendance-day-card {{ $cardClass }}">
                        <div class="attendance-day-number">{{ $day }}</div>

                        <div class="attendance-day-mark">{{ $mark }}</div>

                        <div class="attendance-day-status">{{ $label }}</div>

                        @if($record)
                            <div class="attendance-day-time">
                                {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '-' }}
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <div class="card section-card">
        <div class="card-header">
            Attendance Detail Table
        </div>

        <div class="card-body table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Point</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Status</th>
                        <th>Late Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendanceRecords as $record)
                        <tr>
                            <td>{{ $record->attendance_date }}</td>
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
                            <td colspan="6" class="text-center py-5 text-muted">
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

@push('styles')
<style>
    .employee-profile-img {
        width: 130px;
        height: 130px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
    }

    .employee-profile-placeholder {
        width: 130px;
        height: 130px;
        margin: 0 auto;
        border-radius: 50%;
        background: #dbeafe;
        color: #1d4ed8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 800;
        border: 4px solid #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
    }

    .attendance-calendar-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 14px;
    }

    .attendance-day-card {
        border-radius: 16px;
        padding: 14px 12px;
        text-align: center;
        border: 1px solid #e5e7eb;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
    }

    .attendance-day-card.attended {
        background: #ecfdf5;
        border-color: #bbf7d0;
    }

    .attendance-day-card.late-day {
        background: #fff7ed;
        border-color: #fdba74;
    }

    .attendance-day-card.absent {
        background: #fef2f2;
        border-color: #fecaca;
    }

    .attendance-day-number {
        font-size: 14px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 8px;
    }

    .attendance-day-mark {
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 8px;
    }

    .attendance-day-card.attended .attendance-day-mark {
        color: #16a34a;
    }

    .attendance-day-card.late-day .attendance-day-mark {
        color: #ea580c;
    }

    .attendance-day-card.absent .attendance-day-mark {
        color: #dc2626;
    }

    .attendance-day-status {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .attendance-day-time {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
    }
</style>
@endpush
