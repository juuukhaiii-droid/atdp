@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4">
    <div class="mb-6">
        <h1 class="font-khmer text-2xl sm:text-3xl font-extrabold tracking-tight mb-1">កែប្រែផ្នែកការងារ</h1>
        <p class="text-ink-soft">Update department information and description</p>
    </div>

    @if ($errors->any())
        <div class="rounded-brand-lg bg-red-100 text-red-800 shadow-sm px-5 py-4 mb-5">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-brand-lg shadow-card bg-white overflow-hidden">
        <div class="font-khmer px-5 sm:px-6 py-4 border-b border-slate-200 font-bold">
            កែប្រែព័ត៌មានផ្នែកការងារ
        </div>

        <div class="p-5 sm:p-6">
            <form method="POST" action="{{ route('admin.departments.update', $department) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block font-semibold text-sm mb-1.5">Department Name</label>
                        <input type="text" name="name" value="{{ $department->name }}" required
                            class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                    </div>

                    <div>
                        <label class="block font-semibold text-sm mb-1.5">Description</label>
                        <input type="text" name="description" value="{{ $department->description }}"
                            class="w-full border border-slate-200 rounded-[10px] px-3.5 py-2.5 text-sm focus:outline-none focus:border-brand-primary focus:ring-4 focus:ring-red-600/10">
                        <div class="text-ink-soft text-xs mt-1.5">Optional short description for this department</div>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <button class="inline-flex items-center rounded-[10px] bg-brand-primary text-white font-semibold px-4 py-2.5 hover:opacity-90 transition">Update Department</button>
                    <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center rounded-[10px] border border-slate-200 bg-white text-ink font-semibold px-4 py-2.5 hover:bg-slate-50 transition">Back</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
