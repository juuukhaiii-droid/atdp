@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-1">Create Employee</h1>
            <p class="text-ink-soft">Add a new employee and assign attendance settings</p>
        </div>

        @if ($errors->any())
            <div class="rounded-brand-lg bg-red-100 text-red-800 shadow-sm px-5 py-4 mb-5">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-200 font-bold">
                Employee Information
            </div>

            <div class="p-5 sm:p-6">
                <form method="POST" action="{{ route('admin.employees.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5" for="admin-field-employee_code-1">Employee Code</label>
                            <input id="admin-field-employee_code-1" type="text" name="employee_code" required
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10" value="{{ old('employee_code') }}">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5" for="admin-field-full_name-2">Full Name</label>
                            <input id="admin-field-full_name-2" type="text" name="full_name" required
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10" value="{{ old('full_name') }}">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5" for="admin-field-phone-3">Phone</label>
                            <input id="admin-field-phone-3" type="text" name="phone"
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10" value="{{ old('phone') }}">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5" for="admin-field-email-4">Email</label>
                            <input id="admin-field-email-4" type="email" name="email" required
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10" value="{{ old('email') }}">
                        </div>

                        <div class="md:col-span-4">
                            <label class="block font-semibold text-sm mb-1.5" for="admin-field-pin-5">PIN (4 digits)</label>
                            <input id="admin-field-pin-5" type="password" name="pin" required
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        </div>

                        <div class="md:col-span-4">
                            <label class="block font-semibold text-sm mb-1.5" for="admin-field-department_id-6">Department</label>
                            <select id="admin-field-department_id-6" name="department_id" required
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @include('admin.employees.attendance-mode')

                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5" for="admin-field-position-8">Position</label>
                            <input id="admin-field-position-8" type="text" name="position"
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10" value="{{ old('position') }}">
                        </div>
                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5" for="admin-field-photo-9">Employee Photo</label>
                            <input id="admin-field-photo-9" type="file" name="photo" accept="image/*"
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                            <p class="text-ink-soft text-sm mt-2">JPG, PNG or WebP. Maximum photo size: 10 MB.</p>
                        </div>

                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5" for="admin-field-status-10">Status</label>
                            <select id="admin-field-status-10" name="status" required
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-2">
                        <button class="inline-flex items-center rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2.5 hover:opacity-90 transition">Save Employee</button>
                        <a href="{{ route('admin.employees.index') }}"
                            class="inline-flex items-center rounded-[10px] border border-slate-200 bg-white text-ink font-semibold px-4 py-2.5 hover:bg-slate-50 transition">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
