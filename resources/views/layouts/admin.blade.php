@php
    $navigation = [
        ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Dashboard', 'khmer' => 'ផ្ទាំងគ្រប់គ្រង', 'icon' => 'fa-chart-line', 'group' => 'Overview'],
        ['route' => 'admin.employees.index', 'match' => 'admin.employees.*', 'label' => 'Employees', 'khmer' => 'បុគ្គលិក', 'icon' => 'fa-users', 'group' => 'Team management'],
        ['route' => 'admin.departments.index', 'match' => 'admin.departments.*', 'label' => 'Departments', 'khmer' => 'ផ្នែកការងារ', 'icon' => 'fa-sitemap', 'group' => 'Team management'],
        ['route' => 'admin.shifts.index', 'match' => 'admin.shifts.*', 'label' => 'Work shifts', 'khmer' => 'ម៉ោងធ្វើការ', 'icon' => 'fa-clock', 'group' => 'Team management'],
        ['route' => 'admin.attendance-points.index', 'match' => 'admin.attendance-points.*', 'label' => 'QR checkpoints', 'khmer' => 'ទីតាំងចុះវត្តមាន', 'icon' => 'fa-qrcode', 'group' => 'Attendance'],
        ['route' => 'admin.attendances.index', 'match' => 'admin.attendances.*', 'label' => 'Attendance records', 'khmer' => 'បញ្ជីវត្តមាន', 'icon' => 'fa-calendar-check', 'group' => 'Attendance'],
        ['route' => 'admin.attendance.history', 'match' => 'admin.attendance.history', 'label' => 'Reports', 'khmer' => 'របាយការណ៍', 'icon' => 'fa-chart-pie', 'group' => 'Attendance'],
    ];
    $current = collect($navigation)->first(fn ($item) => request()->routeIs($item['match'])) ?? $navigation[0];
    $action = last(explode('.', request()->route()?->getName() ?? ''));
    $actionLabel = ['create' => 'Create', 'edit' => 'Edit', 'show' => 'Details'][$action] ?? null;
    $pageTitle = ($actionLabel ? $actionLabel . ' · ' : '') . $current['label'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="PizzaHappyFamily">
    <title>@yield('title', $pageTitle . ' · PizzaHappyFamily')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Khmer:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?v={{ filemtime(public_path('css/admin-dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">
    @stack('styles')
</head>
<body class="phf-dashboard phf-admin font-body">
    <a href="#admin-content" class="admin-skip">Skip to content</a>
    <aside id="admin-sidebar" class="admin-sidebar" aria-label="Administration sidebar">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}"><span class="admin-brand-mark"><img src="{{ asset('images/pizzahappyfamily-logo.jpg') }}" alt="PizzaHappyFamily logo" width="44" height="44"></span><span><strong>PizzaHappyFamily<span>.</span></strong><small>TEAM & ATTENDANCE</small></span></a>
        <nav class="admin-menu" aria-label="Admin navigation">
            @foreach(collect($navigation)->groupBy('group') as $group => $items)
                <div class="admin-menu-label">{{ $group }}</div>
                @foreach($items as $item)
                    <a href="{{ route($item['route']) }}" class="admin-menu-item {{ request()->routeIs($item['match']) ? 'is-active' : '' }}" @if(request()->routeIs($item['match'])) aria-current="page" @endif>
                        <i class="fas {{ $item['icon'] }}" aria-hidden="true"></i><span>{{ $item['label'] }}<small lang="km">{{ $item['khmer'] }}</small></span>@if(request()->routeIs($item['match']))<span class="nav-active-dot"></span>@endif
                    </a>
                @endforeach
            @endforeach
        </nav>
        <div class="admin-sidebar-bottom"><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="sidebar-logout"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Sign out <i class="fas fa-arrow-right" aria-hidden="true"></i></button></form></div>
    </aside>
    <div class="admin-frame">
        <header class="admin-topbar">
            <div class="topbar-left"><div class="admin-crumb"><a href="{{ route($current['route']) }}">{{ $current['label'] }}</a>@if($actionLabel)<span>/</span><strong>{{ $actionLabel }}</strong>@endif</div></div>
            <div class="topbar-right"><time datetime="{{ now()->toDateString() }}"><i class="far fa-calendar" aria-hidden="true"></i>{{ now()->format('D, d M Y') }}</time><div class="admin-account"><span class="account-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span><strong>{{ auth()->user()->name }}</strong><small>Administrator</small></span></div></div>
        </header>
        <nav class="admin-mobile-nav" aria-label="Admin navigation">
            @foreach($navigation as $item)
                <a href="{{ route($item['route']) }}" @if(request()->routeIs($item['match'])) aria-current="page" @endif><i class="fas {{ $item['icon'] }}" aria-hidden="true"></i>{{ $item['label'] }}</a>
            @endforeach
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Sign out</button></form>
        </nav>
        <main id="admin-content" class="admin-content {{ request()->routeIs('admin.dashboard') ? 'is-dashboard' : 'admin-standard-page' }} {{ in_array($action, ['create', 'edit']) ? 'admin-form-page' : '' }}" tabindex="-1">
            @if($errors->any())<div class="admin-alert error" role="alert"><i class="fas fa-user-xmark" aria-hidden="true"></i><div><strong>Please check your information</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
            {{-- Page templates already display success messages. Keep a fallback for other flash types. --}}
            @foreach(['error' => 'error', 'warning' => 'warning'] as $key => $tone)
                @if(session($key))<div class="admin-alert {{ $tone }}" role="alert">{{ session($key) }}</div>@endif
            @endforeach
            @yield('content')
        </main>
        <footer class="admin-footer"><span>© {{ now()->year }} <strong>PizzaHappyFamily</strong><span class="footer-divider">·</span>Attendance Management</span><span>Happy team, happy family.</span></footer>
    </div>
    <script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}" defer></script>
    @stack('scripts')
</body>
</html>
