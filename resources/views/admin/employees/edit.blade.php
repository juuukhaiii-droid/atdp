@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="mb-4">
            <h1 class="page-title mb-1">Edit Employee</h1>
            <p class="page-desc mb-0">Update employee information and attendance settings</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="card section-card">
            <div class="card-header">
                Edit Employee Information
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('admin.employees.update', $employee) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Employee Code</label>
                            <input type="text" name="employee_code" class="form-control form-control-lg rounded-3"
                                value="{{ $employee->employee_code }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name</label>
                            <input type="text" name="full_name" class="form-control form-control-lg rounded-3"
                                value="{{ $employee->full_name }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control form-control-lg rounded-3"
                                value="{{ $employee->phone }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control form-control-lg rounded-3"
                                value="{{ $employee->email }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">New PIN (optional)</label>
                            <input type="password" name="pin" class="form-control form-control-lg rounded-3">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Department</label>
                            <select name="department_id" class="form-select form-select-lg rounded-3" required>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ $employee->department_id == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Shift</label>
                            <select name="shift_id" class="form-select form-select-lg rounded-3" required>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" {{ $employee->shift_id == $shift->id ? 'selected' : '' }}>
                                        {{ $shift->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Position</label>
                            <input type="text" name="position" class="form-control form-control-lg rounded-3"
                                value="{{ $employee->position }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Employee Photo</label>
                            <input type="file" name="photo" class="form-control form-control-lg rounded-3" accept="image/*">
                            @if($employee->photo)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $employee->photo) }}" alt="Employee Photo" width="90"
                                        class="rounded-3 border">
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-lg rounded-3" required>
                                <option value="active" {{ $employee->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $employee->status === 'inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button class="btn btn-primary px-4 py-2 rounded-3 fw-semibold">Update Employee</button>
                        <a href="{{ route('admin.employees.index') }}"
                            class="btn btn-light border px-4 py-2 rounded-3 fw-semibold">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
