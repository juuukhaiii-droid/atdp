@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1 khmer-text">ផ្នែកការងារ</h1>
            <p class="page-desc mb-0">Manage company departments and organize employee groups</p>
        </div>

        <a href="{{ route('admin.departments.create') }}" class="btn btn-primary px-4 py-2 rounded-3 fw-semibold shadow-sm khmer-text">
            + បន្ថែមផ្នែកការងារ
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="card section-card">
        <div class="card-header khmer-text">
            បញ្ជីផ្នែកការងារ
        </div>

        <div class="card-body table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th width="200">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $department->name }}</span>
                                    <span class="muted-small">Department unit</span>
                                </div>
                            </td>
                            <td>
                                @if($department->description)
                                    <span class="department-desc">{{ $department->description }}</span>
                                @else
                                    <span class="text-muted">No description</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-warning btn-sm rounded-3 px-3 fw-semibold">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.departments.destroy', $department) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Delete this department?')" class="btn btn-danger btn-sm rounded-3 px-3 fw-semibold">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5">
                                <div class="text-muted">
                                    <div class="fs-5 fw-semibold mb-1">No departments found</div>
                                    <div>Create your first department to organize employees.</div>
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

@push('styles')
<style>
    .department-desc {
        display: inline-block;
        padding: 8px 12px;
        border-radius: 10px;
        background: #f8fafc;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid #e2e8f0;
    }
</style>
@endpush
