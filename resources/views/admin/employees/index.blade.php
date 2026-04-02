@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">Employees</h1>
            <p class="page-desc mb-0">Manage employee information and attendance access</p>
        </div>

        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm">
            + Add Employee
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card section-card">
        <div class="card-header">
            Employee List
        </div>

        <div class="card-body table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Shift</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th width="260">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $employee->full_name }}</span>
                                    <span class="muted-small">{{ $employee->employee_code }}</span>
                                </div>
                            </td>
                            <td>{{ $employee->department->name ?? '-' }}</td>
                            <td>{{ $employee->shift->name ?? '-' }}</td>
                            <td>{{ $employee->phone ?: '-' }}</td>
                            <td>
                                @if($employee->status === 'active')
                                    <span class="status-badge status-present">Active</span>
                                @else
                                    <span class="status-badge status-absent">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-info btn-sm rounded-3 px-3 fw-semibold text-white">
                                        View
                                    </a>
                                    <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-warning btn-sm rounded-3 px-3 fw-semibold">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.employees.destroy', $employee) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Delete this employee?')" class="btn btn-danger btn-sm rounded-3 px-3 fw-semibold">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <div class="fs-5 fw-semibold mb-1">No employees found</div>
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
