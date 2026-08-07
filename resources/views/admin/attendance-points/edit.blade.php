@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4">
    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-6">Edit Attendance Point</h3>

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
        <div class="p-5 sm:p-6">
            <form method="POST" action="{{ route('admin.attendance-points.update', $attendancePoint) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block font-semibold text-sm mb-1.5">Name <span class="text-brand-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $attendancePoint->name) }}" required
                           class="w-full border rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-4 focus:ring-red-600/10 {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200 focus:border-brand-primary' }}">
                    @error('name') <small class="text-brand-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold text-sm mb-1.5">Code <span class="text-brand-danger">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $attendancePoint->code) }}" required
                           class="w-full border rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-4 focus:ring-red-600/10 {{ $errors->has('code') ? 'border-red-400' : 'border-slate-200 focus:border-brand-primary' }}">
                    @error('code') <small class="text-brand-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold text-sm mb-1.5">Location</label>
                    <input type="text" name="location" value="{{ old('location', $attendancePoint->location) }}"
                           class="w-full border rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-4 focus:ring-red-600/10 {{ $errors->has('location') ? 'border-red-400' : 'border-slate-200 focus:border-brand-primary' }}">
                    @error('location') <small class="text-brand-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold text-sm mb-1.5">Department <span class="text-brand-danger">*</span></label>
                    <select name="department_id" required
                            class="w-full border rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-4 focus:ring-red-600/10 {{ $errors->has('department_id') ? 'border-red-400' : 'border-slate-200 focus:border-brand-primary' }}">
                        <option value="">-- Select Department --</option>
                        @foreach(App\Models\Department::all() as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $attendancePoint->department_id) == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id') <small class="text-brand-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold text-sm mb-1.5">Status <span class="text-brand-danger">*</span></label>
                    <select name="status" required
                            class="w-full border rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-4 focus:ring-red-600/10 {{ $errors->has('status') ? 'border-red-400' : 'border-slate-200 focus:border-brand-primary' }}">
                        <option value="active" {{ old('status', $attendancePoint->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $attendancePoint->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <small class="text-brand-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-5">
                    <label class="block font-semibold text-sm mb-1.5">QR Code</label>
                    @if($attendancePoint->qr_image)
                        <img src="{{ asset($attendancePoint->qr_image) }}" width="120" class="rounded-lg border border-slate-200 p-1 mb-2">
                        <p class="text-ink-soft text-sm">✓ QR Code is ready</p>
                    @else
                        <span class="text-ink-soft">No QR Code Available</span>
                    @endif
                </div>

                <div class="flex gap-2">
                    <button class="inline-flex items-center rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2.5 hover:opacity-90 transition">Update</button>
                    <a href="{{ route('admin.attendance-points.index') }}" class="inline-flex items-center rounded-[10px] border border-slate-200 bg-white text-ink font-semibold px-4 py-2.5 hover:bg-slate-50 transition">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
