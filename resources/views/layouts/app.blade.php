<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Khmer:wght@400;500;600;700&display=swap" rel="stylesheet">

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

        body {
            background: var(--bg-soft);
            color: var(--text-main);
            font-family: 'Inter', 'Noto Sans Khmer', sans-serif;
        }

        .khmer-text {
            font-family: 'Noto Sans Khmer', sans-serif;
        }

        .main-navbar {
            background: linear-gradient(90deg, #111827 0%, #1f2937 100%);
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        }

        .brand-title {
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            font-size: 12px;
            color: rgba(255,255,255,0.75);
            margin-top: 2px;
        }

        .nav-link-custom {
            color: rgba(255,255,255,0.88);
            text-decoration: none;
            font-weight: 500;
            padding: 10px 12px;
            border-radius: 10px;
            transition: 0.2s ease;
        }

        .nav-link-custom:hover {
            color: #fff;
            background: rgba(255,255,255,0.08);
        }

        .logout-btn {
            border-radius: 10px;
            padding: 8px 14px;
            font-weight: 600;
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
        }

        @media (max-width: 992px) {
            .nav-flex {
                flex-wrap: wrap;
                gap: 10px !important;
                justify-content: flex-end;
            }

            .page-title {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg main-navbar navbar-dark">
        <div class="container">
            <div>
                <a class="navbar-brand brand-title mb-0" href="{{ route('admin.dashboard') }}">
                    ភីហ្សា គ្រួសាររីករាយ | PizzaHappyFamily
                </a>
                <div class="brand-subtitle">PizzaHappyFamily Attendance Management System</div>
            </div>

            <div class="d-flex gap-2 align-items-center nav-flex">
                <a href="{{ route('admin.dashboard') }}" class="nav-link-custom">Dashboard</a>
                <a href="{{ route('admin.departments.index') }}" class="nav-link-custom">ផ្នែកការងារ</a>
                <a href="{{ route('admin.shifts.index') }}" class="nav-link-custom">ម៉ោងធ្វើការ</a>
                <a href="{{ route('admin.employees.index') }}" class="nav-link-custom">បុគ្គលិក</a>
                <a href="{{ route('admin.attendance-points.index') }}" class="nav-link-custom">ប្រភេទវត្តមាន</a>
                <a href="{{ route('admin.reports.attendance') }}" class="nav-link-custom">របាយការណ៍</a>

                <form method="POST" action="{{ route('logout') }}" class="ms-1">
                    @csrf
                    <button class="btn btn-outline-light btn-sm logout-btn">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="content-wrap">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
