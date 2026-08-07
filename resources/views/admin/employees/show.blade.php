@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4" x-data="attendanceEditor()">
    @php
        $today = now()->toDateString();
    @endphp

    <div class="flex justify-between items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-1">Employee Detail</h1>
            <p class="text-ink-soft">View employee information and attendance history</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.employees.edit', $employee) }}" class="inline-flex items-center rounded-[10px] bg-amber-400 text-ink font-semibold px-4 py-2.5 hover:bg-amber-500 transition">
                Edit Employee
            </a>
            <a href="{{ route('admin.employees.index') }}" class="inline-flex items-center rounded-[10px] border border-slate-200 bg-white text-ink font-semibold px-4 py-2.5 hover:bg-slate-50 transition">
                Back
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-brand-lg bg-green-100 text-green-800 shadow-sm px-5 py-4 mb-4">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 mb-3 items-start">
        <div class="lg:col-span-4">
            <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 font-bold">
                    Employee Information
                </div>

                <div class="p-5">
                    <div class="text-center mb-4">
                        @if($employee->photo)
                            <img
                                src="{{ asset('files/' . $employee->photo) }}"
                                alt="Employee Photo"
                                class="w-[110px] h-[110px] object-cover rounded-full border-4 border-white shadow-[0_8px_20px_rgba(15,23,42,0.12)] mx-auto">
                        @else
                            <div class="w-[110px] h-[110px] mx-auto rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-4xl font-extrabold border-4 border-white shadow-[0_8px_20px_rgba(15,23,42,0.12)]">
                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                        <div>
                            <div class="text-ink-soft text-[11.5px] mb-0.5">Employee Code</div>
                            <div class="font-semibold">{{ $employee->employee_code }}</div>
                        </div>

                        <div>
                            <div class="text-ink-soft text-[11.5px] mb-0.5">Full Name</div>
                            <div class="font-semibold">{{ $employee->full_name }}</div>
                        </div>

                        <div>
                            <div class="text-ink-soft text-[11.5px] mb-0.5">Department</div>
                            <div class="font-semibold">{{ $employee->department->name ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-ink-soft text-[11.5px] mb-0.5">Shift</div>
                            <div class="font-semibold">{{ $employee->shift->name ?? '-' }}</div>
                        </div>

                        <div>
                            <div class="text-ink-soft text-[11.5px] mb-0.5">Position</div>
                            <div class="font-semibold">{{ $employee->position ?: '-' }}</div>
                        </div>

                        <div>
                            <div class="text-ink-soft text-[11.5px] mb-0.5">Phone</div>
                            <div class="font-semibold">{{ $employee->phone ?: '-' }}</div>
                        </div>

                        <div>
                            <div class="text-ink-soft text-[11.5px] mb-0.5">Email</div>
                            <div class="font-semibold">{{ $employee->email ?: '-' }}</div>
                        </div>

                        <div>
                            <div class="text-ink-soft text-[11.5px] mb-0.5">Status</div>
                            <div class="mt-1">
                                @if($employee->status === 'active')
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-red-100 text-red-800">Inactive</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                <div class="rounded-brand-lg shadow-card bg-white p-5">
                    <div class="text-ink-soft font-semibold text-sm mb-2">Total Attendance</div>
                    <h3 class="text-2xl font-extrabold leading-none">{{ $totalAttendance }}</h3>
                </div>

                <div class="rounded-brand-lg shadow-card bg-white p-5">
                    <div class="text-ink-soft font-semibold text-sm mb-2">Present</div>
                    <h3 class="text-2xl font-extrabold leading-none text-green-600">{{ $presentCount }}</h3>
                </div>

                <div class="rounded-brand-lg shadow-card bg-white p-5">
                    <div class="text-ink-soft font-semibold text-sm mb-2">Late</div>
                    <h3 class="text-2xl font-extrabold leading-none text-amber-500">{{ $lateCount }}</h3>
                </div>
            </div>

            <div class="rounded-brand-lg shadow-card bg-gradient-to-br from-white to-slate-50 p-5">
                <form method="GET" action="{{ route('admin.employees.show', $employee) }}" class="flex flex-wrap items-end gap-3.5">
                    <div class="w-11 h-11 rounded-brand-md bg-blue-100 text-blue-800 flex items-center justify-center text-lg shrink-0">
                        <i class="fas fa-calendar-week"></i>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-bold text-ink-soft uppercase tracking-wide">Month/Day/Year</label>
                        <input type="date" name="date" value="{{ request('date') }}"
                            class="border border-slate-200 rounded-[10px] px-3 py-2.5 font-semibold text-sm min-w-[150px] bg-white focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                    </div>

                    <button type="submit" class="inline-flex items-center gap-2 rounded-[10px] bg-brand-dark text-white font-bold text-sm px-[18px] py-2.5 hover:opacity-90 transition">
                        <i class="fas fa-magnifying-glass"></i> Filter
                    </button>

                    @if (request('date'))
                        <a href="{{ route('admin.employees.show', $employee) }}" class="inline-flex items-center gap-2 rounded-[10px] bg-red-100 text-red-800 font-bold text-sm px-4 py-2.5 hover:bg-red-200 transition">
                            <i class="fas fa-rotate-left"></i> Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <div class="rounded-brand-lg shadow-card bg-white mb-3 overflow-hidden">
        <div class="flex justify-between items-center px-5 py-4 border-b border-slate-200 font-bold">
            <span>Monthly Attendance Calendar</span>
            <span class="text-ink-soft text-[13px] font-normal">
                {{ \Carbon\Carbon::create($calendarYear, $calendarMonth, 1)->format('F Y') }}
            </span>
        </div>

        <div class="p-5">
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
                                @click="open({{ \Illuminate\Support\Js::from($cellPayload) }})">
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

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 font-bold">
            Attendance Detail Table
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Date</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Point</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Check In</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Check Out</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Status</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Late Time</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Note</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendanceRecords as $record)
                        <tr class="hover:bg-slate-50/80 align-middle">
                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $record->attendance_date }}</td>
                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $record->attendancePoint->name ?? '-' }}</td>
                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $record->check_in_time ?? '-' }}</td>
                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $record->check_out_time ?? '-' }}</td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                @if($record->status === 'late')
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-amber-100 text-amber-800">Late</span>
                                @elseif($record->status === 'present')
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-green-100 text-green-800">Present</span>
                                @else
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-red-100 text-red-800">{{ ucfirst($record->status) }}</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                @php
                                    $hours = floor($record->late_minutes / 60);
                                    $minutes = $record->late_minutes % 60;
                                @endphp

                                @if($record->late_minutes > 0)
                                    <span class="font-bold text-amber-700">
                                        {{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}
                                    </span>
                                @else
                                    <span class="text-ink-soft">00:00</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100 text-ink-soft text-[13px]">{{ $record->note ?: '-' }}</td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
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
                                    <button type="button" class="w-8 h-8 rounded-lg border border-slate-200 text-ink-soft hover:bg-slate-100 hover:text-ink transition inline-flex items-center justify-center" title="Edit attendance"
                                        @click="open({{ \Illuminate\Support\Js::from($rowPayload) }})">
                                        <i class="fas fa-pen text-xs"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-ink-soft">
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Shared edit modal for both the calendar cells and the table rows --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="modalOpen = false"></div>

        <form method="POST" action="{{ route('admin.employees.attendance.update', $employee) }}"
            class="relative bg-white rounded-brand-lg shadow-card-lg w-full max-w-md overflow-hidden" @click.outside="modalOpen = false">
            @csrf
            <input type="hidden" name="date" :value="form.date">

            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                <h5 class="font-bold">Edit Attendance &mdash; <span x-text="form.date"></span></h5>
                <button type="button" class="text-ink-soft hover:text-ink" @click="modalOpen = false" aria-label="Close">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="p-5 grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-sm mb-1.5">Check In</label>
                    <input type="time" name="check_in_time" x-model="form.checkIn"
                        class="w-full border border-slate-200 rounded-[10px] px-3 py-2 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                </div>
                <div>
                    <label class="block font-semibold text-sm mb-1.5">Check Out</label>
                    <input type="time" name="check_out_time" x-model="form.checkOut"
                        class="w-full border border-slate-200 rounded-[10px] px-3 py-2 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                </div>
                <div class="col-span-2">
                    <label class="block font-semibold text-sm mb-1.5">Attendance Point</label>
                    <select name="attendance_point_id" x-model="form.pointId" required
                        class="w-full border border-slate-200 rounded-[10px] px-3 py-2 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        @foreach ($attendancePoints as $point)
                            <option value="{{ $point->id }}">{{ $point->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block font-semibold text-sm mb-1.5">Note</label>
                    <textarea name="note" x-model="form.note" rows="2" maxlength="255" placeholder="Reason for correction (optional)"
                        class="w-full border border-slate-200 rounded-[10px] px-3 py-2 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2 px-5 py-4 border-t border-slate-200">
                <button type="button" class="inline-flex items-center rounded-[10px] border border-slate-200 bg-white text-ink font-semibold px-4 py-2 hover:bg-slate-50 transition" @click="modalOpen = false">Cancel</button>
                <button type="submit" class="inline-flex items-center rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2 hover:opacity-90 transition">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
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
</style>
@endpush

@push('scripts')
<script>
    function attendanceEditor() {
        return {
            modalOpen: false,
            form: { date: '', checkIn: '', checkOut: '', pointId: '', note: '' },
            open(data) {
                this.form = {
                    date: data.date || '',
                    checkIn: data.checkIn || '',
                    checkOut: data.checkOut || '',
                    pointId: data.pointId || '',
                    note: data.note || '',
                };
                this.modalOpen = true;
            },
        };
    }
</script>
@endpush
