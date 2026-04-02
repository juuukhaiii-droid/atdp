@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between mb-3">
        <h3>Attendance Points</h3>
        <a href="{{ route('admin.attendance-points.create') }}" class="btn btn-primary">Add Attendance Point</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Attendance URL</th>
                        <th width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendancePoints as $point)
                        <tr>
                            <td>{{ $point->name }}</td>
                            <td>{{ $point->code }}</td>
                            <td>{{ $point->location }}</td>
                            <td>{{ ucfirst($point->status) }}</td>
                            <td>
                                <a href="{{ url('/attendance/' . $point->qr_token) }}" target="_blank">
                                    Open Attendance Page
                                </a>
                            </td>
                            <td class="d-flex gap-2">
                                <a href="{{ route('admin.attendance-points.edit', $point) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.attendance-points.destroy', $point) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Delete this attendance point?')" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No attendance points found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
