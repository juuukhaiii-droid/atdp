@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-1">Edit Employee</h1>
            <p class="text-ink-soft">Update employee information and attendance settings</p>
        </div>

        @if ($errors->any())
            <div class="rounded-brand-lg bg-red-100 text-red-800 shadow-sm px-5 py-4 mb-5">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
            <div class="px-5 sm:px-6 py-4 border-b border-slate-200 font-bold">
                Edit Employee Information
            </div>

            <div class="p-5 sm:p-6">
                <form method="POST" action="{{ route('admin.employees.update', $employee) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5">Employee Code</label>
                            <input type="text" name="employee_code" value="{{ $employee->employee_code }}" required
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5">Full Name</label>
                            <input type="text" name="full_name" value="{{ $employee->full_name }}" required
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5">Phone</label>
                            <input type="text" name="phone" value="{{ $employee->phone }}"
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        </div>

                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5">Email</label>
                            <input type="email" name="email" value="{{ $employee->email }}"
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        </div>

                        <div class="md:col-span-4">
                            <label class="block font-semibold text-sm mb-1.5">New PIN (optional)</label>
                            <input type="password" name="pin"
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        </div>

                        <div class="md:col-span-4">
                            <label class="block font-semibold text-sm mb-1.5">Department</label>
                            <select name="department_id" required
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ $employee->department_id == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-4">
                            <label class="block font-semibold text-sm mb-1.5">Shift</label>
                            <select name="shift_id" required
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" {{ $employee->shift_id == $shift->id ? 'selected' : '' }}>
                                        {{ $shift->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5">Position</label>
                            <input type="text" name="position" value="{{ $employee->position }}"
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        </div>
                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5">Employee Photo</label>
                            <input type="file" name="photo" accept="image/*"
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                            @if($employee->photo)
                                <div class="mt-2">
                                    <img src="{{ asset('files/' . $employee->photo) }}" alt="Employee Photo" width="90"
                                        class="rounded-[10px] border border-slate-200">
                                </div>
                            @endif
                        </div>

                        <div class="md:col-span-6">
                            <label class="block font-semibold text-sm mb-1.5">Status</label>
                            <select name="status" required
                                class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                                <option value="active" {{ $employee->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $employee->status === 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-2">
                        <button class="inline-flex items-center rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2.5 hover:opacity-90 transition">Update Employee</button>
                        <a href="{{ route('admin.employees.index') }}"
                            class="inline-flex items-center rounded-[10px] border border-slate-200 bg-white text-ink font-semibold px-4 py-2.5 hover:bg-slate-50 transition">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
