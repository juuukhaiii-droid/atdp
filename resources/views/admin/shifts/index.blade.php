@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center flex-wrap gap-3 mb-6">
        <div>
            <h1 class="font-khmer text-2xl sm:text-3xl font-extrabold tracking-tight mb-1">ម៉ោងធ្វើការ</h1>
            <p class="text-ink-soft">Manage working time, shift hours, and late attendance rules</p>
        </div>

        <a href="{{ route('admin.shifts.create') }}" class="font-khmer inline-flex items-center rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2.5 shadow-sm hover:opacity-90 transition">
            + បន្ថែមម៉ោងធ្វើការថ្មី
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-brand-lg bg-green-100 text-green-800 shadow-sm px-5 py-4 mb-5">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
        <div class="font-khmer px-5 sm:px-6 py-4 border-b border-slate-200 font-bold">
             បញ្ជីម៉ោងធ្វើការ (Shifts) - Define work schedules and attendance rules for employees
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Name</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Start Time</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">End Time</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Late After</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200 w-[200px]">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        <tr class="hover:bg-slate-50/80 align-middle">
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <div class="flex flex-col">
                                    <span class="font-semibold">{{ $shift->name }}</span>
                                    <span class="text-ink-soft text-[13px]">Working schedule</span>
                                </div>
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <span class="inline-block px-3 py-2 rounded-[10px] bg-blue-50 text-blue-700 text-[13px] font-bold">{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}</span>
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <span class="inline-block px-3 py-2 rounded-[10px] bg-blue-50 text-blue-700 text-[13px] font-bold">{{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</span>
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <span class="inline-block px-3 py-2 rounded-[10px] bg-amber-100 text-amber-800 text-[13px] font-bold">{{ \Carbon\Carbon::parse($shift->late_after)->format('h:i A') }}</span>
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.shifts.edit', $shift) }}" class="inline-flex items-center rounded-[10px] bg-amber-400 text-ink text-sm font-semibold px-3 py-1.5 hover:bg-amber-500 transition">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.shifts.destroy', $shift) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Delete this shift?')" class="inline-flex items-center rounded-[10px] bg-brand-danger text-white text-sm font-semibold px-3 py-1.5 hover:opacity-90 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10">
                                <div class="text-ink-soft">
                                    <div class="text-lg font-semibold mb-1">No shifts found</div>
                                    <div>Create your first shift to define attendance time rules.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
