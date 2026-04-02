@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-3">Create Attendance Point</h3>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.attendance-points.store') }}">
                @csrf

                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Warehouse Entrance" required>
                </div>

                <div class="mb-3">
                    <label>Code</label>
                    <input type="text" name="code" class="form-control" placeholder="WH001" required>
                </div>

                <div class="mb-3">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" placeholder="Main Warehouse">
                </div>

                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button class="btn btn-primary">Save</button>
                <a href="{{ route('admin.attendance-points.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </div>
</div>
@endsection
