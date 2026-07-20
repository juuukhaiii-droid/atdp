@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between mb-3">
            <h3>Attendance Points</h3>
            <a href="{{ route('admin.attendance-points.create') }}" class="btn btn-primary">Add Attendance Point</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Department</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>QR Code</th>
                            <th width="280">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendancePoints as $point)
                            <tr>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $point->department->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td><strong>{{ $point->name }}</strong></td>
                                <td>{{ $point->code }}</td>
                                <td>{{ $point->location }}</td>
                                <td>
                                    <span class="badge bg-{{ $point->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($point->status) }}
                                    </span>
                                </td>

                                <td>
                                    @if($point->qr_image)
                                        <img src="{{ asset($point->qr_image) }}" width="60" class="img-thumbnail cursor-pointer"
                                             data-bs-toggle="modal" data-bs-target="#qrModal{{ $point->id }}">
                                    @else
                                        <span class="text-muted text-sm">No QR</span>
                                    @endif
                                </td>

                                <td class="d-flex gap-2">
                                    @if($point->qr_image)
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#qrModal{{ $point->id }}"
                                                title="Print QR Code">
                                            <i class="fas fa-print"></i> Print
                                        </button>
                                        <a href="{{ asset($point->qr_image) }}" download class="btn btn-sm btn-info" title="Download QR Code">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.attendance-points.edit', $point) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('admin.attendance-points.destroy', $point) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')

                                        <button onclick="return confirm('Delete this attendance point?')" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- QR Code Print Modal -->
                            <div class="modal fade" id="qrModal{{ $point->id }}" tabindex="-1">
                                <div class="modal-dialog modal-sm modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">QR Code - {{ $point->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-center p-4">
                                            <img src="{{ asset($point->qr_image) }}" class="img-fluid" alt="QR Code">
                                            <p class="mt-3 text-muted"><small>Department: <strong>{{ $point->department->name ?? 'N/A' }}</strong></small></p>
                                            <p class="text-muted"><small>Location: <strong>{{ $point->location }}</strong></small></p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary" onclick="window.print()">
                                                <i class="fas fa-print"></i> Print
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No attendance points found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
