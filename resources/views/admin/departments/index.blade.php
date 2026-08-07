@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center flex-wrap gap-3 mb-6">
        <div>
            <h1 class="font-khmer text-2xl sm:text-3xl font-extrabold tracking-tight mb-1">ផ្នែកការងារ</h1>
            <p class="text-ink-soft">Manage company departments and organize employee groups</p>
        </div>

        <a href="{{ route('admin.departments.create') }}" class="font-khmer inline-flex items-center rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2.5 shadow-sm hover:opacity-90 transition">
            + បន្ថែមផ្នែកការងារ
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-brand-lg bg-green-100 text-green-800 shadow-sm px-5 py-4 mb-5">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
        <div class="font-khmer px-5 sm:px-6 py-4 border-b border-slate-200 font-bold">
            បញ្ជីផ្នែកការងារ
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Name</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200">Description</th>
                        <th class="text-left font-bold text-slate-700 text-[14px] whitespace-nowrap px-3.5 py-4 border-b border-slate-200 w-[200px]">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr class="hover:bg-slate-50/80 align-middle">
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <div class="flex flex-col">
                                    <span class="font-semibold">{{ $department->name }}</span>
                                    <span class="text-ink-soft text-[13px]">Department unit</span>
                                </div>
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                @if($department->description)
                                    <span class="inline-block px-3 py-2 rounded-[10px] bg-slate-50 text-slate-700 text-[13px] font-semibold border border-slate-200">{{ $department->description }}</span>
                                @else
                                    <span class="text-ink-soft">No description</span>
                                @endif
                            </td>
                            <td class="px-3.5 py-4 border-b border-slate-100">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.departments.edit', $department) }}" class="inline-flex items-center rounded-[10px] bg-amber-400 text-ink text-sm font-semibold px-3 py-1.5 hover:bg-amber-500 transition">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.departments.destroy', $department) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Delete this department?')" class="inline-flex items-center rounded-[10px] bg-brand-danger text-white text-sm font-semibold px-3 py-1.5 hover:opacity-90 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-10">
                                <div class="text-ink-soft">
                                    <div class="text-lg font-semibold mb-1">No departments found</div>
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
