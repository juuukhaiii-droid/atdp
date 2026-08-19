<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="PizzaHappyFamily Attendance Management System">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Attendance')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Khmer:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    <style>
        :root {
            --brand-dark: #111827;
            --brand-primary: #dc2626;
            --brand-primary-soft: #fee2e2;
            --brand-success: #16a34a;
            --brand-warning: #f59e0b;
            --text-main: #0f172a;
            --text-soft: #64748b;
            --border-soft: #e5e7eb;
            --bg-soft: #f8fafc;
            --card-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            --radius-lg: 20px;
            --radius-md: 14px;
            --app-bar-h: 64px;
            --tab-bar-h: 68px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            background: var(--bg-soft);
            color: var(--text-main);
            font-family: 'Inter', 'Noto Sans Khmer', sans-serif;
            min-height: 100vh;
            padding-top: calc(var(--app-bar-h) + env(safe-area-inset-top));
            padding-bottom: calc(var(--tab-bar-h) + env(safe-area-inset-bottom) + 12px);
        }

        .khmer-text { font-family: 'Noto Sans Khmer', sans-serif; }

        /* ---- App Bar ---- */
        .app-bar {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: calc(var(--app-bar-h) + env(safe-area-inset-top));
            padding-top: env(safe-area-inset-top);
            background: linear-gradient(90deg, #111827 0%, #1f2937 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-left: 18px;
            padding-right: 10px;
            z-index: 1030;
            box-shadow: 0 4px 18px rgba(0,0,0,0.12);
        }

        .app-bar-greeting {
            font-weight: 700;
            font-size: 15px;
            line-height: 1.2;
        }

        .app-bar-sub {
            font-size: 11.5px;
            color: rgba(255,255,255,0.6);
            margin-top: 1px;
        }

        .app-bar-actions {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .app-bar-icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 0;
            background: rgba(255,255,255,0.08);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            transition: background 0.2s ease;
        }

        .app-bar-icon-btn:hover, .app-bar-icon-btn:active {
            background: rgba(255,255,255,0.16);
            color: #fff;
        }

        /* ---- Content ---- */
        .app-content {
            padding: 16px 14px 4px;
            max-width: 560px;
            margin: 0 auto;
        }

        .alert { border-radius: var(--radius-md); border: 0; margin-bottom: 14px; }
        .alert-success { background: #dcfce7; color: #166534; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
        .alert-warning { background: #fef3c7; color: #92400e; }
        .alert-info { background: #dbeafe; color: #1e40af; }

        /* ---- Bottom Tab Bar ---- */
        .tab-bar {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            height: calc(var(--tab-bar-h) + env(safe-area-inset-bottom));
            padding-bottom: env(safe-area-inset-bottom);
            background: #ffffff;
            border-top: 1px solid var(--border-soft);
            box-shadow: 0 -6px 24px rgba(15, 23, 42, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-around;
            z-index: 1030;
            max-width: 560px;
            margin: 0 auto;
        }

        .tab-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3px;
            color: var(--text-soft);
            text-decoration: none;
            font-size: 10.5px;
            font-weight: 600;
            height: 100%;
            transition: color 0.2s ease;
        }

        .tab-item i {
            font-size: 19px;
        }

        .tab-item.active {
            color: var(--brand-primary);
        }

        .tab-item:hover {
            color: var(--brand-primary);
        }

        .tab-scan-wrap {
            flex: 1;
            display: flex;
            justify-content: center;
            position: relative;
        }

        .tab-scan-btn {
            position: absolute;
            top: -26px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-primary) 0%, #b91c1c 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            box-shadow: 0 10px 24px rgba(220, 38, 38, 0.4);
            border: 4px solid var(--bg-soft);
            text-decoration: none;
            transition: transform 0.2s ease;
        }

        .tab-scan-btn:hover, .tab-scan-btn:active {
            color: #fff;
            transform: translateY(-2px) scale(1.03);
        }

        .tab-scan-label {
            position: absolute;
            top: 32px;
            font-size: 10.5px;
            font-weight: 700;
            color: var(--brand-primary);
            white-space: nowrap;
        }

        /* ---- Shared app-style cards ---- */
        .app-card {
            background: #fff;
            border: 0;
            border-radius: var(--radius-lg);
            box-shadow: var(--card-shadow);
        }

        .app-card .card-body { padding: 18px; }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-soft);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin: 22px 2px 10px;
        }

        @media (min-width: 576px) {
            .app-content { padding-top: 20px; }
        }
    </style>
</head>
<body>

    {{-- Content --}}
    <main class="app-content">
        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-circle-exclamation me-2"></i>
                @foreach ($errors->all() as $error)
                    {{ $error }}@if (!$loop->last)<br>@endif
                @endforeach
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success" role="alert"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger" role="alert"><i class="fas fa-times-circle me-2"></i>{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    {{-- Bottom Tab Bar --}}
    <nav class="tab-bar">
        <a href="{{ route('employee.dashboard') }}" class="tab-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
            <i class="fas fa-house"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('employee.attendance.history') }}" class="tab-item {{ request()->routeIs('employee.attendance.history') ? 'active' : '' }}">
            <i class="fas fa-clock-rotate-left"></i>
            <span>History</span>
        </a>

        <div class="tab-scan-wrap">
            <a href="{{ route('attendance.show.qr') }}" class="tab-scan-btn" title="Scan QR">
                <i class="fas fa-qrcode"></i>
            </a>
            <span class="tab-scan-label">Scan</span>
        </div>

        <a href="{{ route('employee.profile') }}" class="tab-item {{ request()->routeIs('employee.profile') ? 'active' : '' }}">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
