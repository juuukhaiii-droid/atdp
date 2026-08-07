<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PizzaHappyFamily Attendance Management System">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Attendance System')</title>

    {{-- Font Awesome Icons --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Khmer:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Custom Styles Stack --}}
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-ink font-body" x-data="{ navOpen: false }">
    {{-- Navigation Bar --}}
    <nav class="sticky top-0 z-50 bg-gradient-to-r from-brand-dark to-slate-800 shadow-lg" role="navigation" aria-label="Main navigation">
        <div class="px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <button type="button"
                    class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg border border-white/20 text-white"
                    @click="navOpen = !navOpen" aria-label="Toggle navigation">
                    <i class="fas" :class="navOpen ? 'fa-xmark' : 'fa-bars'"></i>
                </button>
                <a class="font-extrabold tracking-tight text-white text-lg" href="{{ route('admin.dashboard') }}" title="Home">
                    ភីហ្សា គ្រួសាររីករាយ
                </a>
            </div>

            {{-- Desktop nav --}}
            <div class="hidden md:flex items-center gap-2 ml-auto">
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:text-white hover:bg-white/10 transition">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a>
                        <a href="{{ route('admin.employees.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:text-white hover:bg-white/10 transition">
                            <i class="fas fa-users"></i> បុគ្គលិក
                        </a>
                        <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:text-white hover:bg-white/10 transition">
                            <i class="fas fa-sitemap"></i> ផ្នែកការងារ
                        </a>
                        <a href="{{ route('admin.shifts.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:text-white hover:bg-white/10 transition">
                            <i class="fas fa-clock"></i> ម៉ោងធ្វើការ
                        </a>
                        <a href="{{ route('admin.attendance-points.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:text-white hover:bg-white/10 transition">
                            <i class="fas fa-location-dot"></i> ប្រភេទវត្តមាន
                        </a>
                    @elseif(auth()->user()->role === 'employee')
                        <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:text-white hover:bg-white/10 transition">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        <a href="{{ route('employee.attendance.history') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:text-white hover:bg-white/10 transition">
                            <i class="fas fa-history"></i> ប្រវត្តិ
                        </a>
                        <a href="{{ route('employee.profile') }}" class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:text-white hover:bg-white/10 transition">
                            <i class="fas fa-user"></i> ប្រវត្តិលេខ
                        </a>
                    @endif

                    <div class="flex items-center gap-2.5 px-3 py-2 rounded-lg bg-white/5 ml-1">
                        <i class="fas fa-user-circle text-white/90"></i>
                        <div>
                            <div class="text-sm font-medium text-white/90 leading-tight">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] uppercase tracking-wide text-white/65 leading-tight">{{ ucfirst(auth()->user()->role) }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-white/30 px-3.5 py-2 text-sm font-semibold text-white/90 hover:bg-white/10 hover:text-white transition" title="Logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                @endauth
            </div>
        </div>

        {{-- Mobile nav --}}
        <div class="md:hidden border-t border-white/10" x-show="navOpen" x-transition x-cloak>
            <div class="px-4 py-3 flex flex-col gap-1.5">
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:bg-white/10">
                            <i class="fas fa-chart-line w-4"></i> Dashboard
                        </a>
                        <a href="{{ route('admin.employees.index') }}" class="inline-flex items-center gap-2 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:bg-white/10">
                            <i class="fas fa-users w-4"></i> បុគ្គលិក
                        </a>
                        <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center gap-2 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:bg-white/10">
                            <i class="fas fa-sitemap w-4"></i> ផ្នែកការងារ
                        </a>
                        <a href="{{ route('admin.shifts.index') }}" class="inline-flex items-center gap-2 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:bg-white/10">
                            <i class="fas fa-clock w-4"></i> ម៉ោងធ្វើការ
                        </a>
                        <a href="{{ route('admin.attendance-points.index') }}" class="inline-flex items-center gap-2 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:bg-white/10">
                            <i class="fas fa-location-dot w-4"></i> ប្រភេទវត្តមាន
                        </a>
                    @elseif(auth()->user()->role === 'employee')
                        <a href="{{ route('employee.dashboard') }}" class="inline-flex items-center gap-2 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:bg-white/10">
                            <i class="fas fa-home w-4"></i> Dashboard
                        </a>
                        <a href="{{ route('employee.attendance.history') }}" class="inline-flex items-center gap-2 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:bg-white/10">
                            <i class="fas fa-history w-4"></i> ប្រវត្តិ
                        </a>
                        <a href="{{ route('employee.profile') }}" class="inline-flex items-center gap-2 px-3 py-2.5 rounded-lg font-medium text-white/90 hover:bg-white/10">
                            <i class="fas fa-user w-4"></i> ប្រវត្តិលេខ
                        </a>
                    @endif

                    <div class="flex items-center gap-2.5 px-3 py-2.5 mt-1 rounded-lg bg-white/5">
                        <i class="fas fa-user-circle text-white/90"></i>
                        <div>
                            <div class="text-sm font-medium text-white/90 leading-tight">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] uppercase tracking-wide text-white/65 leading-tight">{{ ucfirst(auth()->user()->role) }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg border border-white/30 px-3.5 py-2.5 text-sm font-semibold text-white/90 hover:bg-white/10">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Alert Messages --}}
    <main class="flex-1 py-5 sm:py-7">
        <div class="px-4">
            @if ($errors->any())
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                    class="rounded-brand-lg bg-red-100 text-red-800 px-5 py-4 mb-5 flex items-start justify-between gap-3">
                    <div>
                        <strong class="inline-flex items-center gap-2"><i class="fas fa-exclamation-circle"></i>Errors:</strong>
                        <ul class="mt-2 ml-6 list-disc space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" @click="show = false" class="text-red-800/70 hover:text-red-800" aria-label="Close">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                    class="rounded-brand-lg bg-green-100 text-green-800 px-5 py-4 mb-5 flex items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-2"><i class="fas fa-check-circle"></i>{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="text-green-800/70 hover:text-green-800" aria-label="Close">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                    class="rounded-brand-lg bg-red-100 text-red-800 px-5 py-4 mb-5 flex items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-2"><i class="fas fa-times-circle"></i>{{ session('error') }}</span>
                    <button type="button" @click="show = false" class="text-red-800/70 hover:text-red-800" aria-label="Close">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if (session('warning'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                    class="rounded-brand-lg bg-amber-100 text-amber-800 px-5 py-4 mb-5 flex items-center justify-between gap-3">
                    <span class="inline-flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i>{{ session('warning') }}</span>
                    <button type="button" @click="show = false" class="text-amber-800/70 hover:text-amber-800" aria-label="Close">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
            @endif
        </div>

        {{-- Page Content --}}
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-brand-dark text-white/75 py-5 mt-auto border-t border-white/10 text-center">
        <p class="text-sm">
            &copy; {{ date('Y') }} <strong class="text-white/90">PizzaHappyFamily</strong> - Attendance Management System
            <span class="ml-2">v1.0</span>
        </p>
    </footer>

    {{-- Scripts Stack --}}
    @stack('scripts')
</body>
</html>
