<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', "L'Âme du Fil — Coopérative Marocaine")</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-lame-du-fil.png') }}">

    <!-- Google Fonts : Cormorant Garamond (titres, esprit logo) + Source Sans 3 (texte) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* ══════════════════════════════════════════════════
           VARIABLES — L'Âme du Fil (charte logo)
        ══════════════════════════════════════════════════ */
        :root {
            --ame-terre:        #9B4A3A;
            --ame-terre-dark:   #7A3829;
            --ame-terre-light:  #C47362;
            --ame-charbon:      #3A3A3A;
            --ame-charbon-deep: #2A2624;
            --ame-creme:        #F7F3EC;
            --ame-fil-or:       #B8956A;
            --ame-fil-or-light: #D4B896;
            --ame-lin:          #EDE6DA;
            --ame-lin-dark:     #DDD4C4;

            /* Alias compatibles (vues existantes) */
            --or:           var(--ame-terre);
            --or-light:     var(--ame-terre-light);
            --or-dark:      var(--ame-terre-dark);
            --terre:        var(--ame-terre-dark);
            --indigo:       var(--ame-charbon-deep);
            --indigo-light: #4A4540;
            --sable:        var(--ame-lin);
            --sable-dark:   var(--ame-lin-dark);
            --blanc:        var(--ame-creme);
            --gris-doux:    #6B6560;
            --texte:        var(--ame-charbon);
            --vert-atlas:   #4A6741;
            --rouge-fes:    #A63D32;
            --shadow-sm:    0 2px 8px rgba(42,38,36,0.07);
            --shadow-md:    0 4px 20px rgba(42,38,36,0.11);
            --shadow-lg:    0 8px 40px rgba(42,38,36,0.14);
            --radius:       12px;
            --radius-sm:    8px;
            --transition:   0.25s ease;
            --font-serif:   'Cormorant Garamond', Georgia, 'Times New Roman', serif;
            --font-sans:    'Source Sans 3', system-ui, -apple-system, sans-serif;
            --logo-terre:   var(--ame-terre);
            --logo-charbon: var(--ame-charbon);
        }

        /* Mode sombre — surcharge légère des variables */
        html.dark-mode {
            --sable: #1f2430;
            --sable-dark: #2a3142;
            --blanc: #14171f;
            --texte: #e8e4d8;
            --gris-doux: #9ca3af;
        }
        html.dark-mode body {
            background: #14171f;
            color: var(--texte);
        }
        html.dark-mode .card-tissu,
        html.dark-mode [style*="background:white"],
        html.dark-mode [style*="background: white"] {
            background: #1f2430 !important;
            color: var(--texte);
        }

        /* ══════════════════════════════════════════════════
           BASE
        ══════════════════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: var(--font-sans);
            background-color: var(--blanc);
            background-image:
                radial-gradient(ellipse 80% 50% at 15% -10%, rgba(155,74,58,0.05) 0%, transparent 55%),
                radial-gradient(ellipse 60% 40% at 90% 100%, rgba(184,149,106,0.07) 0%, transparent 50%);
            color: var(--texte);
            font-size: 15px;
            line-height: 1.6;
        }

        h1, h2, h3, h4, h5, h6, .font-serif {
            font-family: var(--font-serif);
            font-weight: 600;
        }

        /* Harmonise les anciennes polices inline (Amiri / DM Sans) */
        [style*="Amiri"] { font-family: var(--font-serif) !important; }
        [style*="DM Sans"] { font-family: var(--font-sans) !important; }

        a { color: var(--or-dark); text-decoration: none; transition: var(--transition); }
        a:hover { color: var(--or); }

        /* Scrollbar personnalisée */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: var(--sable); }
        ::-webkit-scrollbar-thumb { background: var(--or); border-radius: 3px; }

        /* ══════════════════════════════════════════════════
           TOPBAR — Bande supérieure fine
        ══════════════════════════════════════════════════ */
        .topbar {
            background: var(--ame-charbon-deep);
            color: rgba(255,255,255,0.88);
            font-size: 12.5px;
            padding: 6px 0;
            letter-spacing: 0.3px;
        }
        .topbar a { color: rgba(255,255,255,0.85); }
        .topbar a:hover { color: var(--ame-fil-or-light); }

        /* ══════════════════════════════════════════════════
           NAVBAR PRINCIPALE
        ══════════════════════════════════════════════════ */
        .navbar-main {
            background: rgba(247,243,236,0.97);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--sable-dark);
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: var(--shadow-sm);
        }

        .navbar-brand-wrap {
            padding: 14px 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo-img {
            height: 58px;
            width: auto;
            max-width: 200px;
            object-fit: contain;
            display: block;
        }

        .brand-logo {
            display: none;
        }

        .brand-text .brand-name {
            font-family: var(--font-serif);
            font-size: 20px;
            font-weight: 700;
            color: var(--logo-terre);
            line-height: 1.1;
            display: block;
        }

        .brand-text .brand-sub {
            font-size: 10.5px;
            color: var(--gris-doux);
            letter-spacing: 1.5px;
            text-transform: uppercase;
            display: block;
        }

        /* Nav links */
        .nav-main .nav-link {
            color: var(--texte) !important;
            font-size: 14px;
            font-weight: 500;
            padding: 22px 16px !important;
            position: relative;
            transition: var(--transition);
        }

        .nav-main .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 16px;
            right: 16px;
            height: 2px;
            background: linear-gradient(90deg, var(--ame-terre), var(--ame-fil-or));
            transform: scaleX(0);
            transition: transform 0.25s ease;
        }

        .nav-main .nav-link:hover,
        .nav-main .nav-link.active {
            color: var(--ame-terre-dark) !important;
        }

        .nav-main .nav-link:hover::after,
        .nav-main .nav-link.active::after {
            transform: scaleX(1);
        }

        /* Dropdown */
        .dropdown-menu {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 8px;
            background: var(--blanc);
            border-top: 3px solid var(--or);
            min-width: 200px;
        }

        .dropdown-item {
            border-radius: var(--radius-sm);
            padding: 8px 14px;
            font-size: 14px;
            color: var(--texte);
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: var(--sable);
            color: var(--or-dark);
        }

        .dropdown-divider {
            border-color: var(--sable-dark);
            margin: 6px 0;
        }

        .navbar-actions .dropdown-menu {
            z-index: 1100;
            background: #F5F0E8;
            border: 1px solid var(--sable-dark);
            min-width: 220px;
        }

        .navbar-actions .dropdown-item {
            color: var(--indigo);
            font-weight: 500;
        }

        .navbar-actions .dropdown-item:hover,
        .navbar-actions .dropdown-item:focus {
            background: var(--sable);
            color: var(--or-dark);
        }

        .navbar-actions .dropdown-item.text-danger:hover {
            background: #fff1f0;
            color: var(--rouge-fes);
        }

        /* Barre de recherche */
        .search-bar {
            display: flex;
            gap: 0;
            max-width: 340px;
            width: 100%;
        }

        .search-bar input {
            border: 1.5px solid var(--sable-dark);
            border-right: none;
            border-radius: var(--radius-sm) 0 0 var(--radius-sm);
            padding: 8px 14px;
            font-size: 13.5px;
            background: var(--sable);
            color: var(--texte);
            outline: none;
            transition: var(--transition);
            flex: 1;
        }

        .search-bar input:focus {
            border-color: var(--or);
            background: white;
        }

        .search-bar button {
            background: var(--or);
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            cursor: pointer;
            transition: var(--transition);
        }

        .search-bar button:hover {
            background: var(--or-dark);
        }

        /* Icônes action (panier, notif) */
        .action-icon {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--sable);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--texte);
            font-size: 18px;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .action-icon:hover {
            background: var(--or);
            color: white;
        }

        .action-icon.dropdown-toggle::after {
            display: none;
        }

        .action-icon .badge-count {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--ame-terre);
            color: white;
            font-size: 10px;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--blanc);
        }

        /* ══════════════════════════════════════════════════
           MOTIF GÉOMÉTRIQUE — Séparateur décoratif
        ══════════════════════════════════════════════════ */
        .motif-bar {
            height: 5px;
            background: repeating-linear-gradient(
                90deg,
                var(--ame-terre) 0px, var(--ame-terre) 24px,
                var(--ame-fil-or) 24px, var(--ame-fil-or) 48px,
                var(--ame-charbon) 48px, var(--ame-charbon) 72px,
                var(--ame-terre-light) 72px, var(--ame-terre-light) 96px
            );
        }

        /* ══════════════════════════════════════════════════
           BREADCRUMB
        ══════════════════════════════════════════════════ */
        .breadcrumb-section {
            background: var(--sable);
            padding: 10px 0;
            border-bottom: 1px solid var(--sable-dark);
        }

        .breadcrumb {
            margin: 0;
            background: none;
            padding: 0;
            font-size: 13px;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: '›';
            color: var(--or);
        }

        .breadcrumb-item a { color: var(--or-dark); }
        .breadcrumb-item.active { color: var(--gris-doux); }

        /* ══════════════════════════════════════════════════
           BUTTONS
        ══════════════════════════════════════════════════ */
        .btn-or {
            background: linear-gradient(135deg, var(--ame-terre), var(--ame-terre-dark));
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            padding: 10px 22px;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
            box-shadow: 0 2px 10px rgba(155, 74, 58, 0.28);
        }

        .btn-or:hover {
            background: linear-gradient(135deg, var(--ame-terre-dark), #5C2A1F);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(155, 74, 58, 0.38);
        }

        .btn-outline-or {
            background: transparent;
            color: var(--or-dark);
            border: 1.5px solid var(--or);
            border-radius: var(--radius-sm);
            padding: 9px 20px;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
        }

        .btn-outline-or:hover {
            background: var(--or);
            color: white;
        }

        .btn-indigo {
            background: var(--ame-charbon);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            padding: 10px 22px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-indigo:hover {
            background: var(--ame-charbon-deep);
            color: white;
        }

        /* ══════════════════════════════════════════════════
           ALERTS personnalisées
        ══════════════════════════════════════════════════ */
        .alert-tissu {
            border-radius: var(--radius);
            border: none;
            border-left: 4px solid var(--or);
            background: var(--sable);
            color: var(--texte);
            padding: 12px 16px;
            font-size: 14px;
        }

        .alert-tissu.success { border-color: var(--vert-atlas); background: #f0fdf4; }
        .alert-tissu.error   { border-color: var(--rouge-fes); background: #fff1f0; }
        .alert-tissu.info    { border-color: var(--ame-charbon); background: #F0EBE4; }

        /* ══════════════════════════════════════════════════
           CARDS
        ══════════════════════════════════════════════════ */
        .card-tissu {
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--sable-dark);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: var(--transition);
        }

        .card-tissu:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        /* ── Composants partagés (catalogue, formations, artisan) ── */
        .page-wrap { padding: 48px 0 80px; }

        .brand-banner {
            background: linear-gradient(145deg, var(--ame-charbon-deep) 0%, #3d3530 50%, var(--ame-terre-dark) 100%);
            border-radius: var(--radius);
            padding: 36px;
            position: relative;
            overflow: hidden;
            color: white;
        }

        .brand-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0.05;
            background: repeating-linear-gradient(45deg, white 0, white 1px, transparent 0, transparent 50%);
            background-size: 20px 20px;
        }

        .brand-banner > * { position: relative; }

        .tab-nav {
            display: flex;
            gap: 4px;
            border-bottom: 2px solid var(--sable-dark);
            margin-bottom: 28px;
            overflow-x: auto;
        }

        .tab-nav a {
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 500;
            color: var(--gris-doux);
            text-decoration: none;
            white-space: nowrap;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
        }

        .tab-nav a.active,
        .tab-nav a:hover {
            color: var(--ame-terre-dark);
            border-bottom-color: var(--ame-terre);
        }

        .produit-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-top: 1px solid var(--sable-dark);
            background: var(--sable);
        }

        .produit-prix {
            font-family: var(--font-serif);
            font-size: 22px;
            font-weight: 700;
            color: var(--ame-terre-dark);
        }

        .produit-prix span {
            font-size: 13px;
            font-weight: 400;
            color: var(--gris-doux);
            font-family: var(--font-sans);
        }

        .produit-categorie {
            font-size: 11.5px;
            color: var(--ame-terre-dark);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .btn-panier {
            width: 38px;
            height: 38px;
            background: var(--ame-charbon);
            color: white;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-panier:hover {
            background: var(--ame-terre-dark);
            color: white;
            transform: scale(1.08);
        }

        .formation-img-brand {
            background: linear-gradient(135deg, var(--ame-charbon-deep), var(--ame-terre-dark));
        }

        .etape-num {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--ame-terre), var(--ame-terre-dark));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .kpi-box::before,
        .kpi-box.or::before { background: var(--ame-terre); }
        .kpi-box.indigo::before,
        .kpi-box.charbon::before { background: var(--ame-charbon); }
        .kpi-box.vert::before { background: var(--vert-atlas); }
        .kpi-box.rouge::before { background: var(--rouge-fes); }

        .kpi-val {
            font-family: var(--font-serif);
            font-size: 36px;
            font-weight: 700;
            color: var(--texte);
            line-height: 1;
        }

        /* En-tête de section avec décoration */
        .section-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
        }

        .section-header .section-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, var(--ame-terre), var(--ame-fil-or));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .section-header h2 {
            font-size: 26px;
            margin: 0;
            color: var(--texte);
        }

        .section-header p {
            font-size: 13.5px;
            color: var(--gris-doux);
            margin: 0;
        }

        /* ══════════════════════════════════════════════════
           BADGE STATUTS
        ══════════════════════════════════════════════════ */
        .badge-statut {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-pending    { background: #FEF3C7; color: #92400E; }
        .badge-confirmed  { background: #DBEAFE; color: #1E40AF; }
        .badge-processing { background: #EDE9FE; color: #5B21B6; }
        .badge-shipped    { background: #D1FAE5; color: #065F46; }
        .badge-delivered  { background: #D1FAE5; color: #065F46; }
        .badge-cancelled  { background: #FEE2E2; color: #991B1B; }
        .badge-actif      { background: #D1FAE5; color: #065F46; }
        .badge-inactif    { background: #F3F4F6; color: #6B7280; }
        .badge-suspendu   { background: #FEE2E2; color: #991B1B; }
        .badge-verified   { background: #FEF3C7; color: #92400E; }

        /* ══════════════════════════════════════════════════
           FOOTER
        ══════════════════════════════════════════════════ */
        .footer-main {
            background: linear-gradient(165deg, var(--ame-charbon-deep) 0%, #352F2C 50%, var(--ame-terre-dark) 100%);
            color: rgba(255,255,255,0.85);
            padding: 60px 0 0;
            margin-top: 80px;
        }

        .footer-brand .brand-name {
            font-family: var(--font-serif);
            font-size: 24px;
            color: white;
            display: block;
            margin-bottom: 8px;
        }

        .footer-main h5 {
            color: var(--ame-fil-or-light);
            font-size: 13px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 18px;
            font-weight: 600;
        }

        .footer-main ul { list-style: none; padding: 0; margin: 0; }
        .footer-main ul li { margin-bottom: 10px; }

        .footer-main ul a {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            transition: var(--transition);
        }

        .footer-main ul a:hover { color: var(--ame-fil-or-light); padding-left: 4px; }

        .footer-bottom {
            background: rgba(0,0,0,0.2);
            padding: 16px 0;
            margin-top: 48px;
            font-size: 13px;
            color: rgba(255,255,255,0.5);
        }

        .footer-motif {
            height: 4px;
            background: repeating-linear-gradient(
                90deg,
                var(--or) 0px, var(--or) 12px,
                var(--rouge-fes) 12px, var(--rouge-fes) 24px,
                var(--vert-atlas) 24px, var(--vert-atlas) 36px,
                var(--sable) 36px, var(--sable) 48px
            );
        }

        /* ══════════════════════════════════════════════════
           SIDEBAR ADMIN
        ══════════════════════════════════════════════════ */
        .sidebar-admin {
            width: 260px;
            min-height: 100vh;
            background: var(--indigo);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 999;
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.75);
            font-size: 14px;
            font-weight: 400;
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255,255,255,0.08);
            color: white;
            border-left-color: var(--or);
        }

        .sidebar-nav a i { font-size: 18px; flex-shrink: 0; }

        .sidebar-nav .nav-section {
            padding: 18px 20px 8px;
            font-size: 10.5px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,0.35);
            font-weight: 600;
        }

        .admin-content {
            margin-left: 260px;
            min-height: 100vh;
            background: var(--sable);
        }

        .admin-topbar {
            background: white;
            padding: 14px 28px;
            border-bottom: 1px solid var(--sable-dark);
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow-sm);
        }

        /* Stat cards admin */
        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 24px;
            border: 1px solid var(--sable-dark);
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .stat-card.or::before    { background: var(--or); }
        .stat-card.indigo::before{ background: var(--indigo); }
        .stat-card.vert::before  { background: var(--vert-atlas); }
        .stat-card.rouge::before { background: var(--rouge-fes); }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin-bottom: 14px;
        }

        .stat-card.or .stat-icon    { background: rgba(155,74,58,0.12); color: var(--or); }
        .stat-card.indigo .stat-icon{ background: rgba(42,38,36,0.10);  color: var(--ame-charbon); }
        .stat-card.vert .stat-icon  { background: rgba(45,106,79,0.12);  color: var(--vert-atlas); }
        .stat-card.rouge .stat-icon { background: rgba(160,48,42,0.12);  color: var(--rouge-fes); }

        .stat-card .stat-value {
            font-family: var(--font-serif);
            font-size: 32px;
            font-weight: 700;
            color: var(--texte);
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-card .stat-label {
            font-size: 13px;
            color: var(--gris-doux);
        }

        /* ══════════════════════════════════════════════════
           TABLES
        ══════════════════════════════════════════════════ */
        .table-tissu {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 14px;
        }

        .table-tissu th {
            background: var(--sable);
            color: var(--texte);
            font-weight: 600;
            padding: 12px 16px;
            font-size: 12.5px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-bottom: 2px solid var(--or);
        }

        .table-tissu td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--sable-dark);
            vertical-align: middle;
            color: var(--texte);
        }

        .table-tissu tr:hover td {
            background: var(--sable);
        }

        /* ══════════════════════════════════════════════════
           FORMS
        ══════════════════════════════════════════════════ */
        .form-control-tissu,
        .form-select-tissu {
            border: 1.5px solid var(--sable-dark);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            font-size: 14px;
            color: var(--texte);
            background: var(--blanc);
            transition: var(--transition);
            width: 100%;
        }

        .form-control-tissu:focus,
        .form-select-tissu:focus {
            border-color: var(--or);
            box-shadow: 0 0 0 3px rgba(155,74,58,0.12);
            outline: none;
        }

        .form-label-tissu {
            font-size: 13px;
            font-weight: 600;
            color: var(--texte);
            margin-bottom: 6px;
            display: block;
        }

        /* ══════════════════════════════════════════════════
           FLASH MESSAGES
        ══════════════════════════════════════════════════ */
        .flash-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-width: 360px;
        }

        body.livreur-space .flash-container {
            top: 16px;
        }

        .flash-msg {
            padding: 14px 18px;
            border-radius: var(--radius);
            font-size: 14px;
            font-weight: 500;
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(120%); opacity: 0; }
            to   { transform: translateX(0);   opacity: 1; }
        }

        .flash-success { background: #f0fdf4; color: #166534; border-left: 4px solid var(--vert-atlas); }
        .flash-error   { background: #fff1f0; color: #991b1b; border-left: 4px solid var(--rouge-fes); }
        .flash-info    { background: #F0EBE4; color: var(--ame-charbon); border-left: 4px solid var(--ame-terre); }

        /* ══════════════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════════════ */
        @media (max-width: 768px) {
            .search-bar { display: none; }
            .topbar .d-none-sm { display: none !important; }
            .sidebar-admin { display: none; }
            .admin-content { margin-left: 0; }
            .nav-main .nav-link { padding: 12px 16px !important; }
        }

        .mobile-nav {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding: 10px 16px;
            background: var(--blanc);
            border-bottom: 1px solid var(--sable-dark);
            -webkit-overflow-scrolling: touch;
        }
        .mobile-nav a {
            flex-shrink: 0;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            color: var(--texte);
            text-decoration: none;
            border-radius: 20px;
            background: var(--sable);
            border: 1px solid var(--sable-dark);
            white-space: nowrap;
        }
        .mobile-nav a.active,
        .mobile-nav a:hover {
            background: var(--or);
            color: #fff;
            border-color: var(--or);
        }
    </style>

    @stack('styles')
</head>

<body @class(['livreur-space' => request()->routeIs('livreur.*', 'fournisseur.*', 'formateur.*')])>

{{-- ═══════════════════════════════════════════════════════
     TOPBAR
═══════════════════════════════════════════════════════ --}}
@unless(request()->routeIs('livreur.*', 'fournisseur.*', 'formateur.*'))
<div class="topbar">
    <div class="container-xl">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex gap-3">
                <span><i class="bi bi-geo-alt-fill me-1"></i>Coopérative — Maroc</span>
                <span class="d-none d-md-inline">|</span>
                <span class="d-none d-md-inline"><i class="bi bi-telephone me-1"></i>+212 5XX-XXXXXX</span>
            </div>
            <div class="d-flex gap-3">
                @guest
                    <a href="{{ route('login') }}">Connexion</a>
                    <span>|</span>
                    <a href="{{ route('register') }}">Inscription</a>
                @else
                    <a href="{{ route('profile') }}">
                        <i class="bi bi-person-circle me-1"></i>
                        {{ auth()->user()->prenom }}
                    </a>
                    <span>|</span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;font-size:12.5px;">
                            Déconnexion
                        </button>
                    </form>
                @endguest
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     NAVBAR PRINCIPALE
═══════════════════════════════════════════════════════ --}}
<nav class="navbar-main">
    <div class="container-xl">
        <div class="d-flex align-items-center justify-content-between w-100">

            {{-- Brand --}}
            <a href="{{ route('home') }}" class="navbar-brand-wrap text-decoration-none">
                <img src="{{ asset('images/logo-lame-du-fil.png') }}"
                     alt="L'Âme du Fil — Coopérative textile marocaine"
                     class="brand-logo-img"
                     width="180" height="58">
            </a>

            {{-- Nav links --}}
            <ul class="nav nav-main d-none d-lg-flex mb-0">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
                       href="{{ route('home') }}">Accueil</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('catalogue.*') ? 'active' : '' }}"
                       href="#" data-bs-toggle="dropdown">Catalogue</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('catalogue.index') }}">
                            <i class="bi bi-grid-3x3-gap me-2 text-muted"></i>Tous les produits</a></li>
                        @foreach(\App\Models\Categorie::whereNull('parent_id')->take(5)->get() as $cat)
                            <li><a class="dropdown-item" href="{{ route('catalogue.categorie', $cat->slug) }}">
                                <i class="bi bi-tag me-2 text-muted"></i>{{ $cat->nom }}</a></li>
                        @endforeach
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('artisans.*') ? 'active' : '' }}"
                       href="{{ route('artisans.index') }}">Artisans</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('formations.*') ? 'active' : '' }}"
                       href="{{ route('formations.index') }}">Formations</a>
                </li>
                @if(auth()->check() && auth()->user()->isArtisan())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('artisan.dashboard') ? 'active' : '' }}"
                           href="{{ route('artisan.dashboard') }}">Dashboard</a>
                    </li>
                @endif
                @if(auth()->check() && auth()->user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                           href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                @endif
                @if(auth()->check() && auth()->user()->isLivreur())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('livreur.dashboard') ? 'active' : '' }}"
                           href="{{ route('livreur.dashboard') }}">Dashboard</a>
                    </li>
                @endif
                @if(auth()->check() && auth()->user()->isApprenant())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('apprenant.*', 'apprenti.*') ? 'active' : '' }}"
                           href="{{ route('apprenant.dashboard') }}">Mon espace apprenti</a>
                    </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link" href="#">Notre Histoire</a>
                </li>
            </ul>

            {{-- Barre de recherche --}}
            <form action="{{ route('catalogue.index') }}" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Rechercher un produit..."
                       value="{{ request('q') }}">
                <button type="submit"><i class="bi bi-search"></i></button>
            </form>

            {{-- Actions --}}
            <div class="d-flex align-items-center gap-2 navbar-actions">
                {{-- Panier --}}
                <a href="{{ route('panier.index') }}" class="action-icon" title="Mon panier">
                    <i class="bi bi-bag"></i>
                    @if(session('panier_count', 0) > 0)
                        <span class="badge-count">{{ session('panier_count') }}</span>
                    @endif
                </a>

                @auth
                    {{-- Notifications --}}
                    <a href="{{ route('notifications.index') }}" class="action-icon" title="Notifications">
                        <i class="bi bi-bell"></i>
                        @php $nonLues = auth()->user()->notifications_custom()->where('is_read', false)->count(); @endphp
                        @if($nonLues > 0)
                            <span class="badge-count">{{ $nonLues }}</span>
                        @endif
                    </a>

                    {{-- Menu profil --}}
                    <div class="dropdown">
                        <button type="button"
                                class="action-icon dropdown-toggle"
                                id="profileMenuButton"
                                data-bs-toggle="dropdown"
                                data-bs-display="static"
                                aria-expanded="false"
                                aria-label="Menu profil"
                                title="Mon compte">
                            <i class="bi bi-person"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="profileMenuButton">
                            @php
                                $ordersRoute = match (auth()->user()->role) {
                                    'artisan' => route('artisan.commandes'),
                                    'admin'   => route('admin.commandes'),
                                    default   => route('commandes.index'),
                                };
                            @endphp
                            <li>
                                <a class="dropdown-item" href="{{ route('formations.index') }}">Formations</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile') }}">Mon Profil</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ $ordersRoute }}">Mes Commandes</a>
                            </li>
                            @if(auth()->user()->role === 'admin')
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">Administration</a>
                                </li>
                            @endif
                            <li>
                                <a class="dropdown-item" href="{{ route('profile') }}#parametres">Paramètres</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Déconnexion</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    {{-- Invité : redirection vers connexion --}}
                    <a href="{{ route('login') }}" class="action-icon" title="Connexion">
                        <i class="bi bi-person"></i>
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

{{-- Navigation mobile (écrans < lg) --}}
<div class="mobile-nav d-lg-none">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Accueil</a>
    <a href="{{ route('catalogue.index') }}" class="{{ request()->routeIs('catalogue.*') ? 'active' : '' }}">Catalogue</a>
    <a href="{{ route('artisans.index') }}" class="{{ request()->routeIs('artisans.*') ? 'active' : '' }}">Artisans</a>
    <a href="{{ route('formations.index') }}" class="{{ request()->routeIs('formations.*') ? 'active' : '' }}">Formations</a>
    @auth
        @if(auth()->user()->isApprenant())
            <a href="{{ route('apprenant.dashboard') }}" class="{{ request()->routeIs('apprenant.*', 'apprenti.*') ? 'active' : '' }}">Mon espace</a>
        @elseif(auth()->user()->isArtisan())
            <a href="{{ route('artisan.dashboard') }}" class="{{ request()->routeIs('artisan.*') ? 'active' : '' }}">Dashboard</a>
        @elseif(auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">Admin</a>
        @elseif(auth()->user()->isLivreur())
            <a href="{{ route('livreur.dashboard') }}" class="{{ request()->routeIs('livreur.*') ? 'active' : '' }}">Livreur</a>
        @endif
    @endauth
</div>
@endunless

{{-- Motif géométrique décoratif --}}
@unless(request()->routeIs('livreur.*', 'fournisseur.*', 'formateur.*', 'home'))
<div class="motif-bar"></div>
@endunless

{{-- ═══════════════════════════════════════════════════════
     FLASH MESSAGES
═══════════════════════════════════════════════════════ --}}
<div class="flash-container" id="flashContainer">
    @if(session('success'))
        <div class="flash-msg flash-success">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash-msg flash-error">
            <i class="bi bi-exclamation-circle-fill"></i>
            {{ session('error') }}
        </div>
    @endif
    @if(session('info'))
        <div class="flash-msg flash-info">
            <i class="bi bi-info-circle-fill"></i>
            {{ session('info') }}
        </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════
     BREADCRUMB (optionnel par page)
═══════════════════════════════════════════════════════ --}}
@hasSection('breadcrumb')
    <div class="breadcrumb-section">
        <div class="container-xl">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════
     CONTENU PRINCIPAL
═══════════════════════════════════════════════════════ --}}
<main>
    @yield('content')
</main>

{{-- ═══════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════ --}}
<footer class="footer-main">
    <div class="footer-motif"></div>
    <div class="container-xl py-5">
        <div class="row g-5">
            {{-- Brand --}}
            <div class="col-lg-4">
                <div class="footer-brand mb-3">
                    <img src="{{ asset('images/logo-lame-du-fil.png') }}"
                         alt="L'Âme du Fil"
                         style="height:72px;width:auto;margin-bottom:12px;filter:brightness(1.05);">
                    <p style="font-size:14px;color:rgba(255,255,255,0.65);line-height:1.7;">
                        Plateforme numérique de la coopérative textile marocaine.
                        Valoriser le savoir-faire ancestral des artisans du Maroc.
                    </p>
                </div>
                <div class="d-flex gap-3">
                    <a href="#" class="action-icon" style="background:rgba(255,255,255,0.1);color:white;">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="action-icon" style="background:rgba(255,255,255,0.1);color:white;">
                        <i class="bi bi-instagram"></i>
                    </a>
                    <a href="#" class="action-icon" style="background:rgba(255,255,255,0.1);color:white;">
                        <i class="bi bi-whatsapp"></i>
                    </a>
                </div>
            </div>

            {{-- Liens --}}
            <div class="col-6 col-lg-2">
                <h5>Catalogue</h5>
                <ul>
                    <li><a href="{{ route('catalogue.index') }}">Tous les produits</a></li>
                    <li><a href="#">Tapis Berbères</a></li>
                    <li><a href="#">Broderies</a></li>
                    <li><a href="#">Tissages</a></li>
                    <li><a href="#">Djellabas</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h5>Services</h5>
                <ul>
                    <li><a href="{{ route('formations.index') }}">Formations</a></li>
                    <li><a href="{{ route('artisans.index') }}">Nos Artisans</a></li>
                    <li><a href="#">Livraison</a></li>
                    <li><a href="#">Fournisseurs</a></li>
                    <li><a href="#">Support</a></li>
                </ul>
            </div>

            <div class="col-lg-3">
                <h5>Contact</h5>
                <ul>
                    <li style="color:rgba(255,255,255,0.65);">
                        <i class="bi bi-geo-alt me-2" style="color:var(--or-light);"></i>
                        Maroc — Coopérative Artisanale
                    </li>
                    <li style="color:rgba(255,255,255,0.65);">
                        <i class="bi bi-envelope me-2" style="color:var(--or-light);"></i>
                        contact@tissu-artisanal.ma
                    </li>
                    <li style="color:rgba(255,255,255,0.65);">
                        <i class="bi bi-telephone me-2" style="color:var(--or-light);"></i>
                        +212 5XX-XXXXXX
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container-xl d-flex justify-content-between align-items-center">
            <span>© {{ date('Y') }} L'Âme du Fil — Coopérative textile marocaine. Tous droits réservés.</span>
            <span>🇲🇦 Fait avec fierté au Maroc</span>
        </div>
    </div>
</footer>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Auto-disparition des flash messages
    setTimeout(() => {
        document.querySelectorAll('.flash-msg').forEach(el => {
            el.style.transition = 'all 0.4s ease';
            el.style.opacity = '0';
            el.style.transform = 'translateX(120%)';
            setTimeout(() => el.remove(), 400);
        });
    }, 4500);
</script>

<script src="{{ asset('js/app.js') }}"></script>
<script src="{{ asset('js/ajax.js') }}"></script>

@stack('scripts')
</body>
</html>