@extends('layouts.app')

@section('content')
<div class="container">
    @php
        $today = now()->toDateString();
    @endphp

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
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

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3 mb-3 align-items-start">
        <div class="col-lg-4">
            <div class="card section-card">
                <div class="card-header">
                    Employee Information
                </div>

                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($employee->photo)
                            <img
                                src="{{ asset('files/' . $employee->photo) }}"
                                alt="Employee Photo"
                                class="employee-profile-img">
                        @else
                            <div class="employee-profile-placeholder">
                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="employee-info-grid">
                        <div>
                            <div class="muted-small">Employee Code</div>
                            <div class="fw-semibold">{{ $employee->employee_code }}</div>
                        </div>

                        <div>
                            <div class="muted-small">Full Name</div>
                            <div class="fw-semibold">{{ $employee->full_name }}</div>
                        </div>

                        <div>
                            <div class="muted-small">Department</div>
                            <div class="fw-semibold">{{ $employee->department->name ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="muted-small">Shift</div>
                            <div class="fw-semibold">{{ $employee->shift->name ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="muted-small">Position</div>
                            <div class="fw-semibold">{{ $employee->position ?: '-' }}</div>
                        </div>

                        <div>
                            <div class="muted-small">Phone</div>
                            <div class="fw-semibold">{{ $employee->phone ?: '-' }}</div>
                        </div>

                        <div>
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
        </div>

        <div class="col-lg-8">
            <div class="row g-3 mb-3">
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

            <div class="card section-card date-filter-card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.employees.show', $employee) }}" class="date-filter-form">
                        <div class="date-filter-icon">
                            <i class="fas fa-calendar-week"></i>
                        </div>

                        <div class="date-filter-input-group">
                            <label class="date-filter-label">Month/Day/Year</label>
                            <input type="date" name="date" class="date-filter-input" value="{{ request('date') }}">
                        </div>

                        <button type="submit" class="date-filter-apply">
                            <i class="fas fa-magnifying-glass"></i> Filter
                        </button>

                        @if (request('date'))
                            <a href="{{ route('admin.employees.show', $employee) }}" class="date-filter-today">
                                <i class="fas fa-rotate-left"></i> Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card section-card mb-3">
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
                        $cellDate = \Carbon\Carbon::create($calendarYear, $calendarMonth, $day)->toDateString();
                        $isEditable = $cellDate <= $today;

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
                        @if ($isEditable)
                            @php
                                $cellPayload = [
                                    'date' => $cellDate,
                                    'checkIn' => $record?->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '',
                                    'checkOut' => $record?->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '',
                                    'pointId' => $record?->attendancePoint->id ?? '',
                                    'note' => $record?->note ?? '',
                                ];
                            @endphp
                            <button type="button" class="attendance-day-edit-btn" title="Edit attendance"
                                onclick="openAttendanceEditor({{ \Illuminate\Support\Js::from($cellPayload) }})">
                                <i class="fas fa-pen"></i>
                            </button>
                        @endif

                        @if ($record && $record->note)
                            <i class="fas fa-note-sticky attendance-day-note-flag" title="{{ $record->note }}"></i>
                        @endif

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
                        <th>Note</th>
                        <th></th>
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
                            <td class="text-muted small">{{ $record->note ?: '-' }}</td>
                            <td>
                                @if ($record->attendance_date <= $today)
                                    @php
                                        $rowPayload = [
                                            'date' => $record->attendance_date,
                                            'checkIn' => $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') : '',
                                            'checkOut' => $record->check_out_time ? \Carbon\Carbon::parse($record->check_out_time)->format('H:i') : '',
                                            'pointId' => $record->attendancePoint->id ?? '',
                                            'note' => $record->note ?? '',
                                        ];
                                    @endphp
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Edit attendance"
                                        onclick="openAttendanceEditor({{ \Illuminate\Support\Js::from($rowPayload) }})">
                                        <i class="fas fa-pen"></i>
                                    </button>
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

{{-- Shared edit modal for both the calendar cells and the table rows --}}
<div class="modal fade" id="attendanceEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.employees.attendance.update', $employee) }}" class="modal-content">
            @csrf
            <input type="hidden" name="date" id="editDate">

            <div class="modal-header">
                <h5 class="modal-title">Edit Attendance &mdash; <span id="editDateLabel"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Check In</label>
                        <input type="time" name="check_in_time" id="editCheckIn" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Check Out</label>
                        <input type="time" name="check_out_time" id="editCheckOut" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Attendance Point</label>
                        <select name="attendance_point_id" id="editPoint" class="form-select" required>
                            @foreach ($attendancePoints as $point)
                                <option value="{{ $point->id }}">{{ $point->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Note</label>
                        <textarea name="note" id="editNote" class="form-control" rows="2" maxlength="255" placeholder="Reason for correction (optional)"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary fw-semibold">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    .employee-profile-img {
        width: 110px;
        height: 110px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
    }

    .employee-profile-placeholder {
        width: 110px;
        height: 110px;
        margin: 0 auto;
        border-radius: 50%;
        background: #dbeafe;
        color: #1d4ed8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 800;
        border: 4px solid #fff;
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
    }

    .employee-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 16px;
    }

    .employee-info-grid .muted-small {
        font-size: 11.5px;
        margin-bottom: 2px;
    }

    .attendance-calendar-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
    }

    .attendance-day-card {
        position: relative;
        border-radius: 14px;
        padding: 10px 8px;
        text-align: center;
        border: 1px solid #e5e7eb;
        min-height: 100px;
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
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 6px;
    }

    .attendance-day-mark {
        font-size: 24px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 6px;
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
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 3px;
    }

    .attendance-day-time {
        font-size: 11.5px;
        color: #64748b;
        font-weight: 600;
    }

    .attendance-day-edit-btn {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 0;
        background: rgba(15, 23, 42, 0.08);
        color: #334155;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s ease;
    }

    .attendance-day-edit-btn:hover {
        background: rgba(15, 23, 42, 0.16);
    }

    .attendance-day-note-flag {
        position: absolute;
        top: 8px;
        left: 8px;
        font-size: 11px;
        color: #b45309;
    }

    /* Reused from the admin dashboard's date filter bar for visual consistency */
    .date-filter-card {
        background: linear-gradient(160deg, #ffffff 0%, #f8fafc 100%);
    }

    .date-filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 14px;
    }

    .date-filter-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        background: #dbeafe;
        color: #1e40af;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .date-filter-input-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .date-filter-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-soft);
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .date-filter-input {
        border: 1px solid var(--border-soft);
        border-radius: 10px;
        padding: 9px 12px;
        font-weight: 600;
        font-size: 14px;
        min-width: 150px;
        color: var(--text-main);
        background: #fff;
    }

    .date-filter-input:focus {
        outline: none;
        border-color: var(--brand-primary);
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }

    .date-filter-apply {
        border: 0;
        border-radius: 10px;
        padding: 10px 18px;
        background: var(--brand-dark);
        color: #fff;
        font-weight: 700;
        font-size: 13.5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .date-filter-apply:hover {
        opacity: 0.9;
        color: #fff;
    }

    .date-filter-today {
        border: 0;
        border-radius: 10px;
        padding: 10px 16px;
        background: #fee2e2;
        color: #991b1b;
        font-weight: 700;
        font-size: 13.5px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }

    .date-filter-today:hover {
        background: #fecaca;
        color: #7f1d1d;
    }

    @media (max-width: 575.98px) {
        .date-filter-form {
            flex-direction: column;
            align-items: stretch;
        }

        .date-filter-input {
            min-width: 0;
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function openAttendanceEditor(data) {
        document.getElementById('editDate').value = data.date;
        document.getElementById('editDateLabel').textContent = data.date;
        document.getElementById('editCheckIn').value = data.checkIn || '';
        document.getElementById('editCheckOut').value = data.checkOut || '';
        document.getElementById('editNote').value = data.note || '';

        const pointSelect = document.getElementById('editPoint');
        if (data.pointId) {
            pointSelect.value = data.pointId;
        }

        new bootstrap.Modal(document.getElementById('attendanceEditModal')).show();
    }
</script>
@endpush
