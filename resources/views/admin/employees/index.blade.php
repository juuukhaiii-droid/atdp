@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center flex-wrap gap-3 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-1">Employees</h1>
            <p class="text-ink-soft">Manage employee information and attendance access</p>
        </div>

        <a href="{{ route('admin.employees.create') }}" class="inline-flex items-center rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2.5 shadow-sm hover:opacity-90 transition">
            + Add Employee
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-brand-lg bg-green-100 text-green-800 shadow-sm px-5 py-4 mb-5">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
        <div class="px-5 sm:px-6 py-4 border-b border-slate-200 font-bold">
            Employee List
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Employee</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Department</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Shift</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Phone</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Status</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200 w-[260px]">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr class="hover:bg-slate-50/80 align-middle">
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <div class="flex flex-col">
                                    <span class="font-semibold">{{ $employee->full_name }}</span>
                                    <span class="text-ink-soft text-[13px]">{{ $employee->employee_code }}</span>
                                </div>
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $employee->department->name ?? '-' }}</td>
                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $employee->shift->name ?? '-' }}</td>
                            <td class="px-3.5 py-4 border-b border-slate-100">{{ $employee->phone ?: '-' }}</td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                @if($employee->status === 'active')
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-green-100 text-green-800">Active</span>
                                @else
                                    <span class="inline-flex items-center justify-center min-w-[72px] px-3 py-1.5 rounded-full text-xs font-bold tracking-wide bg-red-100 text-red-800">Inactive</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <div class="flex gap-2 flex-wrap">
                                    <a href="{{ route('admin.employees.show', $employee) }}" class="inline-flex items-center rounded-[10px] bg-sky-500 text-white text-sm font-semibold px-3 py-1.5 hover:bg-sky-600 transition">
                                        View
                                    </a>
                                    <a href="{{ route('admin.employees.edit', $employee) }}" class="inline-flex items-center rounded-[10px] bg-amber-400 text-ink text-sm font-semibold px-3 py-1.5 hover:bg-amber-500 transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Delete this employee?')" class="inline-flex items-center rounded-[10px] bg-brand-danger text-white text-sm font-semibold px-3 py-1.5 hover:opacity-90 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10">
                                <div class="text-ink-soft">
                                    <div class="text-lg font-semibold mb-1">No employees found</div>
                                    <div>Create your first employee record.</div>
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
