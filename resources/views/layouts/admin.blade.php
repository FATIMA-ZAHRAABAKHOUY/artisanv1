<!DOCTYPE html>
<html lang="fr" data-theme="dark" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') — Tissu Artisanal</title>
    {{-- Appliquer le thème avant le rendu (évite le flash) --}}
    <script>
        (function () {
            var t = localStorage.getItem('admin-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-w: 260px;
            --gold: #9B4A3A;
            --gold-light: #C47362;
            --gold-dark: #7A3829;
            --success: #22c55e;
            --danger: #ef4444;
            --info: #3b82f6;
            --warning: #f59e0b;
        }

        /* ── Mode nuit (défaut) ── */
        [data-theme="dark"] {
            --bg-body: #0f1117;
            --bg-card: #1a1d27;
            --bg-sidebar: #141720;
            --bg-topbar: rgba(15,17,23,.88);
            --bg-input: #12151e;
            --border: rgba(255,255,255,.08);
            --text-primary: #e2e8f0;
            --text-secondary: #94a3b8;
            --text-muted: #8b93a7;
            --nav-hover-bg: rgba(255,255,255,.04);
            --nav-active-bg: rgba(155,74,58,.12);
            --table-hover: rgba(255,255,255,.03);
            --shadow-card: 0 12px 32px rgba(0,0,0,.35);
            --progress-bg: rgba(255,255,255,.08);
            --kpi-gradient: linear-gradient(135deg, rgba(155,74,58,.12), rgba(155,74,58,.04));
            --kpi-border: rgba(155,74,58,.28);
            --chart-grid: rgba(255,255,255,.05);
            --chart-text: #64748b;
            --chart-label: #94a3b8;
            --dropdown-bg: #1a1d27;
            --overlay: rgba(0,0,0,.6);
            --pagination-hover: #252836;
        }

        /* ── Mode jour (éclairage) ── */
        [data-theme="light"] {
            --bg-body: #f5f0e8;
            --bg-card: #ffffff;
            --bg-sidebar: #2A2624;
            --bg-topbar: rgba(255,255,255,.92);
            --bg-input: #ffffff;
            --border: rgba(42,38,36,.12);
            --text-primary: #1e293b;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --nav-hover-bg: rgba(255,255,255,.08);
            --nav-active-bg: rgba(155,74,58,.18);
            --table-hover: rgba(155,74,58,.06);
            --shadow-card: 0 8px 24px rgba(42,38,36,.1);
            --progress-bg: rgba(42,38,36,.08);
            --kpi-gradient: linear-gradient(135deg, rgba(155,74,58,.14), rgba(255,255,255,.8));
            --kpi-border: rgba(155,74,58,.35);
            --chart-grid: rgba(42,38,36,.08);
            --chart-text: #64748b;
            --chart-label: #475569;
            --dropdown-bg: #ffffff;
            --overlay: rgba(26,39,68,.35);
            --pagination-hover: #f1ede6;
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'Source Sans 3', sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            transition: background .3s, color .3s;
        }

        /* Sidebar */
        .admin-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border);
            display: flex; flex-direction: column;
            z-index: 1040;
            overflow-y: auto;
            transition: transform .3s;
        }
        .admin-sidebar .brand {
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid var(--border);
        }
        .admin-sidebar .brand img {
            max-width: 100%;
            height: auto;
            max-height: 64px;
            margin-bottom: .5rem;
            display: block;
        }
        .admin-sidebar .brand h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.15rem; margin: 0; color: var(--gold-light);
        }
        .admin-sidebar .brand small { color: var(--text-muted); font-size: .72rem; }
        .admin-sidebar .section-lbl {
            padding: 1rem 1.25rem .35rem;
            font-size: .65rem; text-transform: uppercase;
            letter-spacing: .08em; color: var(--text-muted);
        }
        .admin-sidebar .nav-link {
            display: flex; align-items: center; gap: .65rem;
            padding: .55rem 1.25rem;
            color: rgba(255,255,255,.68); text-decoration: none;
            font-size: .875rem; transition: all .2s;
            border-left: 3px solid transparent;
        }
        .admin-sidebar .nav-link:hover { color: #fff; background: var(--nav-hover-bg); }
        .admin-sidebar .nav-link.active {
            color: var(--gold-light);
            background: var(--nav-active-bg);
            border-left-color: var(--gold);
        }
        .admin-sidebar .nav-link i { width: 18px; text-align: center; }

        /* Main */
        .admin-main { margin-left: var(--sidebar-w); min-height: 100vh; }
        .admin-topbar {
            position: sticky; top: 0; z-index: 1030;
            background: var(--bg-topbar);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            padding: .75rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            transition: background .3s;
        }
        .admin-content { padding: 1.5rem; }

        /* KPI Cards */
        .kpi-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.15rem 1.25rem;
            height: 100%;
            transition: transform .25s, box-shadow .25s, background .3s;
            position: relative; overflow: hidden;
        }
        .kpi-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: var(--accent, var(--gold));
        }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-card); }
        .kpi-card .kpi-icon {
            width: 42px; height: 42px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; margin-bottom: .75rem;
            background: rgba(155,74,58,.15); color: var(--gold-light);
        }
        .kpi-card .kpi-value {
            font-size: 1.65rem; font-weight: 700; line-height: 1.1;
            animation: countUp .6s ease-out;
        }
        .kpi-card .kpi-label { font-size: .78rem; color: var(--text-muted); margin-top: .2rem; }

        @keyframes countUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Chart & widget cards */
        .dash-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1.25rem;
            height: 100%;
            transition: background .3s, border-color .3s;
        }
        .dash-card .card-title {
            font-size: .9rem; font-weight: 600; margin-bottom: 1rem;
            display: flex; align-items: center; gap: .5rem;
        }
        .dash-card .card-title i { color: var(--gold); }

        /* Tables */
        .table-dash { --bs-table-bg: transparent; --bs-table-color: var(--text-primary); font-size: .82rem; }
        .table-dash thead th {
            border-bottom: 1px solid var(--border);
            color: var(--text-muted); font-weight: 500; font-size: .72rem;
            text-transform: uppercase; letter-spacing: .04em;
        }
        .table-dash tbody tr { border-color: var(--border); }
        .table-dash tbody tr:hover { background: var(--table-hover); }

        /* Badges */
        .badge-statut {
            font-size: .68rem; padding: .25rem .55rem;
            border-radius: 20px; font-weight: 500;
        }

        /* Timeline */
        .timeline { list-style: none; padding: 0; margin: 0; }
        .timeline li {
            display: flex; gap: .75rem; padding: .65rem 0;
            border-bottom: 1px solid var(--border);
            font-size: .82rem;
        }
        .timeline li:last-child { border-bottom: none; }
        .timeline .tl-icon {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: .75rem;
        }
        .timeline .tl-time { font-size: .7rem; color: var(--text-muted); }

        /* KPI advanced strip */
        .kpi-advanced {
            background: var(--kpi-gradient);
            border: 1px solid var(--kpi-border);
            border-radius: 12px; padding: .85rem 1rem; text-align: center;
            transition: background .3s;
        }
        .kpi-advanced .val { font-size: 1.25rem; font-weight: 700; color: var(--gold-dark); }
        [data-theme="dark"] .kpi-advanced .val { color: var(--gold-light); }
        .kpi-advanced .lbl { font-size: .7rem; color: var(--text-muted); }

        /* Mobile sidebar */
        @media (max-width: 991.98px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-main { margin-left: 0; }
            .sidebar-overlay {
                display: none; position: fixed; inset: 0;
                background: var(--overlay); z-index: 1035;
            }
            .sidebar-overlay.show { display: block; }
        }

        .progress-thin { height: 6px; border-radius: 3px; background: var(--progress-bg); }
        .progress-thin .progress-bar { background: var(--gold); }

        .btn-gold {
            background: var(--gold); border: none; color: #fff;
        }
        .btn-gold:hover { background: var(--gold-light); color: #1a1d27; }

        /* ── Pages CRUD admin ── */
        .admin-page-title { font-size: 1.25rem; font-weight: 700; margin: 0; }
        .admin-filter {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.25rem;
        }
        .admin-filter label { font-size: .72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: .25rem; display: block; }
        .admin-filter .form-control, .admin-filter .form-select,
        .dash-card .form-control, .dash-card .form-select {
            background: var(--bg-input); border-color: var(--border);
            color: var(--text-primary); font-size: .875rem;
        }
        .admin-filter .form-control:focus, .admin-filter .form-select:focus,
        .dash-card .form-control:focus, .dash-card .form-select:focus {
            background: var(--bg-input); border-color: var(--gold);
            color: var(--text-primary); box-shadow: 0 0 0 .2rem rgba(155,74,58,.15);
        }
        .admin-table-wrap {
            background: var(--bg-card); border: 1px solid var(--border);
            border-radius: 12px; overflow: hidden;
        }
        .admin-tabs { display: flex; gap: .25rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border); }
        .admin-tabs a {
            padding: .6rem 1rem; font-size: .875rem; text-decoration: none; color: var(--text-muted);
            border-bottom: 2px solid transparent; margin-bottom: -1px;
        }
        .admin-tabs a.active { color: var(--gold-light); border-bottom-color: var(--gold); }
        .admin-tabs a.danger.active { color: var(--danger); border-bottom-color: var(--danger); }
        .avatar-circle {
            width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: .8rem; font-weight: 700;
        }
        .badge-role, .badge-statut-sm {
            font-size: .68rem; padding: .2rem .5rem; border-radius: 20px; font-weight: 500;
        }
        .badge-pending { background: rgba(249,115,22,.15); color: #fb923c; }
        .badge-confirmed, .badge-verified { background: rgba(59,130,246,.15); color: #60a5fa; }
        .badge-processing, .badge-shipped { background: rgba(139,92,246,.15); color: #a78bfa; }
        .badge-delivered, .badge-actif { background: rgba(34,197,94,.15); color: #4ade80; }
        .badge-cancelled, .badge-inactif, .badge-suspendu { background: rgba(100,116,139,.2); color: #94a3b8; }
        .badge-actif-user { background: rgba(34,197,94,.15); color: #4ade80; }
        .btn-admin-sm { padding: .3rem .65rem; font-size: .75rem; border-radius: 6px; }
        .btn-outline-danger-sm { border: 1px solid var(--danger); color: var(--danger); background: transparent; }
        .btn-outline-success-sm { border: 1px solid var(--success); color: var(--success); background: transparent; }
        .btn-admin-primary { background: var(--gold); color: #fff; border: none; }
        .btn-admin-primary:hover { background: var(--gold-light); color: #1a1d27; }
        .mini-stat {
            background: var(--bg-card); border: 1px solid var(--border); border-radius: 12px;
            padding: 1rem; border-top: 3px solid var(--accent, var(--gold));
        }
        .mini-stat .val { font-size: 1.5rem; font-weight: 700; }
        .mini-stat .lbl { font-size: .75rem; color: var(--text-muted); }
        .pagination { --bs-pagination-bg: var(--bg-card); --bs-pagination-border-color: var(--border);
            --bs-pagination-color: var(--text-secondary); --bs-pagination-hover-bg: var(--pagination-hover);
            --bs-pagination-active-bg: var(--gold); --bs-pagination-active-border-color: var(--gold); }

        /* ── Bouton thème jour / nuit ── */
        .theme-toggle-btn {
            width: 38px; height: 38px; border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--bg-card);
            color: var(--gold);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all .25s;
            font-size: 1rem;
        }
        .theme-toggle-btn:hover {
            background: var(--gold); color: #fff; border-color: var(--gold);
            transform: rotate(15deg);
        }
        [data-theme="dark"] .theme-toggle-btn .icon-sun { display: inline; }
        [data-theme="dark"] .theme-toggle-btn .icon-moon { display: none; }
        [data-theme="light"] .theme-toggle-btn .icon-sun { display: none; }
        [data-theme="light"] .theme-toggle-btn .icon-moon { display: inline; }

        [data-theme="light"] .admin-topbar .breadcrumb-item.active { color: var(--text-primary) !important; }
        [data-theme="light"] .dropdown-menu { background: var(--dropdown-bg); border-color: var(--border); }
        [data-theme="light"] .dropdown-item { color: var(--text-primary); }
        [data-theme="light"] .dropdown-item:hover { background: var(--pagination-hover); }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="brand">
        <img src="{{ asset('images/logo-lame-du-fil.png') }}" alt="L'Âme du Fil">
        <small>Administration — Coopérative textile</small>
    </div>

    @php $enAttente = $sidebarArtisansEnAttente ?? \App\Models\Artisan::where('is_verified', false)->count(); @endphp

    <div class="section-lbl">Principal</div>
    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
        <i class="fa-solid fa-gauge-high"></i> Tableau de bord
    </a>
    <a href="{{ url('/admin') }}" class="nav-link" target="_blank">
        <i class="fa-solid fa-table-columns"></i> Panel Filament
    </a>

    <div class="section-lbl">Gestion</div>
    @if(Route::has('admin.users'))
    <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
        <i class="fa-solid fa-users"></i> Utilisateurs
    </a>
    @endif
    @if(Route::has('admin.artisans'))
    <a href="{{ route('admin.artisans') }}" class="nav-link {{ request()->routeIs('admin.artisans*') ? 'active' : '' }}">
        <i class="fa-solid fa-palette"></i> Artisans
        @if($enAttente > 0)<span class="badge bg-danger ms-auto">{{ $enAttente }}</span>@endif
    </a>
    @endif
    @if(Route::has('admin.produits'))
    <a href="{{ route('admin.produits') }}" class="nav-link {{ request()->routeIs('admin.produits*') ? 'active' : '' }}">
        <i class="fa-solid fa-shirt"></i> Produits
    </a>
    @endif
    @if(Route::has('admin.commandes'))
    <a href="{{ route('admin.commandes') }}" class="nav-link {{ request()->routeIs('admin.commandes*') ? 'active' : '' }}">
        <i class="fa-solid fa-bag-shopping"></i> Commandes
    </a>
    @endif
    @if(Route::has('admin.livraisons'))
    <a href="{{ route('admin.livraisons') }}" class="nav-link {{ request()->routeIs('admin.livraisons*') ? 'active' : '' }}">
        <i class="fa-solid fa-truck"></i> Livraisons
    </a>
    @endif

    <div class="section-lbl">Formations</div>
    @if(Route::has('admin.formations'))
    <a href="{{ route('admin.formations') }}" class="nav-link {{ request()->routeIs('admin.formations*') ? 'active' : '' }}">
        <i class="fa-solid fa-graduation-cap"></i> Formations
    </a>
    @endif
    @if(Route::has('admin.fournisseurs.index'))
    <a href="{{ route('admin.fournisseurs.index') }}" class="nav-link {{ request()->routeIs('admin.fournisseurs*') ? 'active' : '' }}">
        <i class="fa-solid fa-building"></i> Fournisseurs
    </a>
    @endif

    <div class="section-lbl">Config</div>
    @if(Route::has('admin.categories'))
    <a href="{{ route('admin.categories') }}" class="nav-link {{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
        <i class="fa-solid fa-tags"></i> Catégories
    </a>
    @endif
    @if(Route::has('admin.support'))
    <a href="{{ route('admin.support') }}" class="nav-link {{ request()->routeIs('admin.support*') ? 'active' : '' }}">
        <i class="fa-solid fa-headset"></i> Support
    </a>
    @endif

    <div class="mt-auto p-3">
        <a href="{{ route('home') }}" class="nav-link"><i class="fa-solid fa-arrow-left"></i> Retour au site</a>
    </div>
</aside>

<div class="admin-main">
    <header class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle" type="button">
                <i class="fa-solid fa-bars"></i>
            </button>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none">Admin</a></li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2 gap-md-3">
            <span class="text-muted small d-none d-md-inline">{{ now()->translatedFormat('l d F Y') }}</span>

            {{-- Bascule mode nuit / jour --}}
            <button type="button" class="theme-toggle-btn" id="themeToggle" title="Mode nuit / jour" aria-label="Changer le thème">
                <i class="fa-solid fa-sun icon-sun"></i>
                <i class="fa-solid fa-moon icon-moon"></i>
            </button>

            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" id="userDropdown">
                    <i class="fa-solid fa-user-shield me-1"></i> {{ auth()->user()->nom_complet ?? 'Admin' }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end" id="userDropdownMenu">
                    <li><a class="dropdown-item" href="{{ url('/admin') }}"><i class="fa-solid fa-table-columns me-2"></i>Filament</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">@csrf
                            <button type="submit" class="dropdown-item text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i>Déconnexion</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main class="admin-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    });
    overlay?.addEventListener('click', () => {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    });

    /* ── Thème jour / nuit ── */
    const THEME_KEY = 'admin-theme';

    window.applyAdminChartTheme = function (theme) {
        const isDark = theme !== 'light';
        window.dashChartDefaults = {
            color: isDark ? '#94a3b8' : '#475569',
            borderColor: isDark ? 'rgba(255,255,255,.08)' : 'rgba(26,39,68,.1)',
            plugins: { legend: { labels: { color: isDark ? '#94a3b8' : '#475569', boxWidth: 12 } } },
            scales: {
                x: { ticks: { color: isDark ? '#64748b' : '#64748b' }, grid: { color: isDark ? 'rgba(255,255,255,.05)' : 'rgba(26,39,68,.08)' } },
                y: { ticks: { color: isDark ? '#64748b' : '#64748b' }, grid: { color: isDark ? 'rgba(255,255,255,.05)' : 'rgba(26,39,68,.08)' } },
            },
        };
        if (typeof Chart !== 'undefined') {
            Chart.defaults.color = isDark ? '#94a3b8' : '#475569';
            Chart.defaults.borderColor = isDark ? 'rgba(255,255,255,.08)' : 'rgba(26,39,68,.1)';
        }
        const menu = document.getElementById('userDropdownMenu');
        if (menu) {
            menu.classList.toggle('dropdown-menu-dark', isDark);
        }
    };

    window.setAdminTheme = function (theme) {
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem(THEME_KEY, theme);
        window.applyAdminChartTheme(theme);
        window.dispatchEvent(new CustomEvent('admin-theme-changed', { detail: { theme } }));
    };

    document.getElementById('themeToggle')?.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        window.setAdminTheme(current === 'dark' ? 'light' : 'dark');
    });

    window.applyAdminChartTheme(document.documentElement.getAttribute('data-theme') || 'dark');
</script>
@stack('scripts')
</body>
</html>
