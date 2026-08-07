@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-1">Edit Shift</h1>
        <p class="text-ink-soft">Update shift time and attendance rule settings</p>
    </div>

    @if ($errors->any())
        <div class="rounded-brand-lg bg-red-100 text-red-800 shadow-sm px-5 py-4 mb-5">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-slate-200 font-bold">
            Edit Shift Details
        </div>

        <div class="p-5 sm:p-6">
            <form method="POST" action="{{ route('admin.shifts.update', $shift) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-12">
                        <label class="block font-semibold text-sm mb-1.5">Shift Name</label>
                        <input type="text" name="name" value="{{ $shift->name }}" required
                            class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                    </div>

                    <div class="md:col-span-4">
                        <label class="block font-semibold text-sm mb-1.5">Start Time</label>
                        <input type="time" name="start_time" value="{{ $shift->start_time }}" required
                            class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        <div class="text-ink-soft text-xs mt-1.5">Employee normal work start time</div>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block font-semibold text-sm mb-1.5">End Time</label>
                        <input type="time" name="end_time" value="{{ $shift->end_time }}" required
                            class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        <div class="text-ink-soft text-xs mt-1.5">Employee normal work finish time</div>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block font-semibold text-sm mb-1.5">Late After</label>
                        <input type="time" name="late_after" value="{{ $shift->late_after }}" required
                            class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        <div class="text-ink-soft text-xs mt-1.5">Attendance after this time is late</div>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <button class="inline-flex items-center rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2.5 hover:opacity-90 transition">Update Shift</button>
                    <a href="{{ route('admin.shifts.index') }}" class="inline-flex items-center rounded-[10px] border border-slate-200 bg-white text-ink font-semibold px-4 py-2.5 hover:bg-slate-50 transition">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
