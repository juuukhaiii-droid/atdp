<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PizzaHappyFamily Attendance Management System">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Attendance System')</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome Icons --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Khmer:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Custom Styles Stack --}}
    @stack('styles')

    <style>
        :root {
            --brand-dark: #111827;
            --brand-primary: #dc2626;
            --brand-primary-soft: #fee2e2;
            --brand-success: #16a34a;
            --brand-warning: #f59e0b;
            --brand-danger: #ef4444;
            --text-main: #0f172a;
            --text-soft: #64748b;
            --border-soft: #e5e7eb;
            --bg-soft: #f8fafc;
            --card-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
            --radius-lg: 18px;
            --radius-md: 14px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: var(--bg-soft);
            color: var(--text-main);
            font-family: 'Inter', 'Noto Sans Khmer', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .khmer-text {
            font-family: 'Noto Sans Khmer', sans-serif;
        }

        .main-navbar {
            background: linear-gradient(90deg, #111827 0%, #1f2937 100%);
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .main-navbar .navbar-toggler {
            border-color: rgba(255,255,255,0.2);
        }

        .main-navbar .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(220, 38, 38, 0.25);
        }

        .brand-title {
            font-weight: 800;
            letter-spacing: -0.02em;
            color: white !important;
            font-size: 1.2rem;
        }

        .brand-subtitle {
            font-size: 11px;
            color: rgba(255,255,255,0.75);
            margin-top: 2px;
            display: block;
        }

        .nav-link-custom {
            color: rgba(255,255,255,0.88);
            text-decoration: none;
            font-weight: 500;
            padding: 10px 12px;
            border-radius: 10px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link-custom:hover {
            color: #fff;
            background: rgba(255,255,255,0.08);
        }

        .nav-link-custom.active {
            background: rgba(220, 38, 38, 0.2);
            color: #fff;
            border-bottom: 2px solid var(--brand-primary);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: rgba(255,255,255,0.05);
            border-radius: 10px;
        }

        .user-info-text {
            color: rgba(255,255,255,0.88);
            font-size: 14px;
            font-weight: 500;
        }

        .user-role {
            color: rgba(255,255,255,0.65);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .logout-btn {
            border-radius: 10px;
            padding: 8px 14px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.1) !important;
            color: #fff;
        }

        .page-header {
            margin-bottom: 26px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 8px;
        }

        .page-desc {
            color: var(--text-soft);
            margin-bottom: 0;
        }

        .dashboard-card {
            border: 0;
            border-radius: var(--radius-lg);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            height: 100%;
            transition: all 0.3s ease;
        }

        .dashboard-card:hover {
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.1);
            transform: translateY(-2px);
        }

        .dashboard-card .card-body {
            padding: 22px 24px;
        }

        .summary-label {
            color: var(--text-soft);
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .summary-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 0;
        }

        .summary-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
        }

        .summary-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .section-card {
            border: 0;
            border-radius: var(--radius-lg);
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .section-card .card-header {
            background: #fff;
            border-bottom: 1px solid var(--border-soft);
            padding: 18px 22px;
            font-weight: 700;
        }

        .table-modern {
            margin-bottom: 0;
        }

        .table-modern thead th {
            border-bottom: 1px solid var(--border-soft);
            color: #334155;
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
            padding: 16px 14px;
            background: #f8fafc;
        }

        .table-modern tbody td {
            vertical-align: middle;
            padding: 16px 14px;
            border-color: #eef2f7;
        }

        .table-modern tbody tr:hover {
            background: #fafafa;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 72px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .status-present {
            background: #dcfce7;
            color: #166534;
        }

        .status-late {
            background: #fef3c7;
            color: #92400e;
        }

        .status-absent {
            background: #fee2e2;
            color: #991b1b;
        }

        .code-pill {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 13px;
            font-weight: 700;
        }

        .late-time-text {
            font-weight: 700;
            color: #b45309;
        }

        .muted-small {
            color: var(--text-soft);
            font-size: 13px;
        }

        .content-wrap {
            padding-top: 28px;
            padding-bottom: 28px;
            flex: 1;
        }

        .alert {
            border-radius: var(--radius-lg);
            border: 0;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
        }

        .footer {
            background: var(--brand-dark);
            color: rgba(255,255,255,0.75);
            padding: 20px 0;
            margin-top: auto;
            text-align: center;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .footer a {
            color: rgba(255,255,255,0.88);
            text-decoration: none;
        }

        .footer a:hover {
            color: white;
        }

        @media (max-width: 992px) {
            .nav-flex {
                flex-wrap: wrap;
                gap: 10px !important;
                justify-content: flex-start;
                padding-top: 10px;
                border-top: 1px solid rgba(255,255,255,0.1);
            }

            .page-title {
                font-size: 1.6rem;
            }

            .brand-title {
                font-size: 1rem;
            }

            .user-info {
                margin-top: 10px;
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .page-title {
                font-size: 1.3rem;
            }

            .summary-value {
                font-size: 1.5rem;
            }

            .content-wrap {
                padding-top: 16px;
                padding-bottom: 16px;
            }
        }
    </style>
</head>
<body>
    {{-- Navigation Bar --}}
    <nav class="navbar navbar-expand-md main-navbar navbar-dark" role="navigation" aria-label="Main navigation">
        <div class="container-fluid">
            <div class="d-flex align-items-center gap-3">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <a class="navbar-brand brand-title mb-0" href="{{ route('admin.dashboard') }}" title="Home">
                    ភីហ្សា គ្រួសាររីករាយ
                </a>
            </div>

            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="d-flex gap-2 align-items-center nav-flex ms-auto">
                    @auth
                        {{-- Navigation Links --}}
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="nav-link-custom">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                            <a href="{{ route('admin.employees.index') }}" class="nav-link-custom">
                                <i class="fas fa-users"></i> បុគ្គលិក
                            </a>
                            <a href="{{ route('admin.departments.index') }}" class="nav-link-custom">
                                <i class="fas fa-sitemap"></i> ផ្នែកការងារ
                            </a>
                            <a href="{{ route('admin.shifts.index') }}" class="nav-link-custom">
                                <i class="fas fa-clock"></i> ម៉ោងធ្វើការ
                            </a>
                            <a href="{{ route('admin.attendance-points.index') }}" class="nav-link-custom">
                                <i class="fas fa-location-dot"></i> ប្រភេទវត្តមាន
                            </a>
                        @elseif(auth()->user()->role === 'employee')
                            <a href="{{ route('employee.dashboard') }}" class="nav-link-custom">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                            <a href="{{ route('employee.attendance.history') }}" class="nav-link-custom">
                                <i class="fas fa-history"></i> ប្រវត្តិ
                            </a>
                            <a href="{{ route('employee.profile') }}" class="nav-link-custom">
                                <i class="fas fa-user"></i> ប្រវត្តិលេខ
                            </a>
                        @endif

                        {{-- User Info & Logout --}}
                        <div class="user-info">
                            <i class="fas fa-user-circle"></i>
                            <div>
                                <div class="user-info-text">{{ auth()->user()->name }}</div>
                                <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('logout') }}" class="ms-1">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm logout-btn" title="Logout">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Alert Messages --}}
    <main class="content-wrap">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong><i class="fas fa-exclamation-circle me-2"></i>Errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-times-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        {{-- Page Content --}}
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="footer">
        <div class="container-fluid">
            <p class="mb-0">
                &copy; {{ date('Y') }} <strong>PizzaHappyFamily</strong> - Attendance Management System
                <span class="ms-2">v1.0</span>
            </p>
        </div>
    </footer>

    {{-- Scripts Stack --}}
    @stack('scripts')

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Auto-hide alerts after 5 seconds --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>
</html>
