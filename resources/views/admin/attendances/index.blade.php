@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-4">
    <div class="mb-6">
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight flex items-center gap-2">
            <i class="fas fa-list"></i> Attendance Records
        </h2>
    </div>

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden mb-6">
        <div class="px-5 sm:px-6 py-4 border-b border-slate-200 font-bold">Filter Records</div>
        <div class="p-5 sm:p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block font-semibold text-sm mb-1.5">Employee</label>
                    <select name="employee_id" class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        <option value="">All Employees</option>
                        @foreach ($records->unique('employee_id') as $record)
                            <option value="{{ $record->employee_id }}"
                                    {{ request('employee_id') == $record->employee_id ? 'selected' : '' }}>
                                {{ $record->employee->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-sm mb-1.5">Status</label>
                    <select name="status" class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        <option value="">All</option>
                        <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-sm mb-1.5">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                </div>

                <div>
                    <label class="block font-semibold text-sm mb-1.5">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                </div>

                <div class="md:col-span-4 flex gap-2">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2.5 hover:opacity-90 transition">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="{{ route('admin.attendances.index') }}" class="inline-flex items-center gap-2 rounded-[10px] border border-slate-200 bg-white text-ink font-semibold px-4 py-2.5 hover:bg-slate-50 transition">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Employee</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Date</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Check In</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Check Out</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Status</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Late Minutes</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr class="hover:bg-slate-50/80 align-middle">
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <div class="font-semibold">{{ $record->employee->full_name }}</div>
                                <small class="text-ink-soft">{{ $record->employee->employee_code }}</small>
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $record->created_at->format('M d, Y') }}</td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                @if ($record->check_in_time)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        {{ \Carbon\Carbon::parse($record->check_in_time)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-ink-soft">--:--</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                @if ($record->check_out_time)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        {{ \Carbon\Carbon::parse($record->check_out_time)->format('H:i') }}
                                    </span>
                                @else
                                    <span class="text-ink-soft">Not checked out</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                @if ($record->status === 'present')
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-green-100 text-green-800">Present</span>
                                @elseif ($record->status === 'late')
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-amber-100 text-amber-800">Late</span>
                                @else
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-red-100 text-red-800">Absent</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                @if ($record->late_minutes > 0)
                                    <span class="font-bold text-amber-700">{{ $record->late_minutes }} min</span>
                                @else
                                    <span class="text-ink-soft">--</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <a href="{{ route('admin.attendances.show', [$record->employee_id, $record->attendance_date]) }}" class="inline-flex items-center gap-1.5 rounded-[10px] bg-sky-500 text-white text-sm font-semibold px-3 py-1.5 hover:bg-sky-600 transition">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8">
                                <i class="fas fa-inbox text-ink-soft text-3xl"></i>
                                <p class="text-ink-soft mt-2">No attendance records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($records->hasPages())
        <div class="mt-6">
            {{ $records->links() }}
        </div>
    @endif
</div>
@endsection
