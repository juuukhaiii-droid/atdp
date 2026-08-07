@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4">
    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-6">Create Attendance Point</h3>

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
        <div class="p-5 sm:p-6">
            <form method="POST" action="{{ route('admin.attendance-points.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="block font-semibold text-sm mb-1.5">Name <span class="text-brand-danger">*</span></label>
                    <input type="text" name="name" placeholder="Warehouse Entrance" required value="{{ old('name') }}"
                           class="w-full border rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-4 focus:ring-red-600/10 {{ $errors->has('name') ? 'border-red-400' : 'border-slate-200 focus:border-brand-primary' }}">
                    @error('name') <small class="text-brand-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold text-sm mb-1.5">Code <span class="text-brand-danger">*</span></label>
                    <input type="text" name="code" placeholder="WH001" required value="{{ old('code') }}"
                           class="w-full border rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-4 focus:ring-red-600/10 {{ $errors->has('code') ? 'border-red-400' : 'border-slate-200 focus:border-brand-primary' }}">
                    @error('code') <small class="text-brand-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold text-sm mb-1.5">Location</label>
                    <input type="text" name="location" placeholder="Main Warehouse" value="{{ old('location') }}"
                           class="w-full border rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-4 focus:ring-red-600/10 {{ $errors->has('location') ? 'border-red-400' : 'border-slate-200 focus:border-brand-primary' }}">
                    @error('location') <small class="text-brand-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-4">
                    <label class="block font-semibold text-sm mb-1.5">Department <span class="text-brand-danger">*</span></label>
                    <select name="department_id" required
                            class="w-full border rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:ring-4 focus:ring-red-600/10 {{ $errors->has('department_id') ? 'border-red-400' : 'border-slate-200 focus:border-brand-primary' }}">
                        <option value="">-- Select Department --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
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
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <small class="text-brand-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-5 p-4 bg-slate-50 rounded-[10px]">
                    <label class="font-bold block mb-1">QR Code</label>
                    <span class="text-ink-soft text-sm block">✓ QR Code will be generated automatically after saving</span>
                    <span class="text-ink-soft text-sm block">✓ You can download and print it for employees to scan</span>
                </div>

                <div class="flex gap-2">
                    <button class="inline-flex items-center rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2.5 hover:opacity-90 transition">Save & Generate QR Code</button>
                    <a href="{{ route('admin.attendance-points.index') }}" class="inline-flex items-center rounded-[10px] border border-slate-200 bg-white text-ink font-semibold px-4 py-2.5 hover:bg-slate-50 transition">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
