<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', "L'Âme du Fil — Coopérative Marocaine")</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-lame-du-fil.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Source+Sans+3:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
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

            --or:           var(--ame-terre);
            --or-light:     var(--ame-terre-light);
            --or-dark:      var(--ame-terre-dark);
            --indigo:       var(--ame-charbon-deep);
            --sable:        var(--ame-lin);
            --sable-dark:   var(--ame-lin-dark);
            --blanc:        var(--ame-creme);
            --gris-doux:    #6B6560;
            --texte:        var(--ame-charbon);
            --vert-atlas:   #4A6741;
            --rouge-fes:    #A63D32;
            --shadow-lg:    0 8px 40px rgba(42,38,36,0.14);
            --radius:       12px;
            --radius-sm:    8px;
            --transition:   0.25s ease;
            --font-serif:   'Cormorant Garamond', Georgia, serif;
            --font-sans:    'Source Sans 3', system-ui, sans-serif;
        }

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
            min-height: 100vh;
        }

        [style*="Amiri"] { font-family: var(--font-serif) !important; }

        a { color: var(--or-dark); text-decoration: none; transition: var(--transition); }
        a:hover { color: var(--or); }

        /* Buttons */
        .btn-or {
            background: linear-gradient(135deg, var(--ame-terre), var(--ame-terre-dark));
            color: white; border: none;
            border-radius: var(--radius-sm);
            padding: 10px 22px;
            font-weight: 500; font-size: 14px;
            transition: var(--transition);
            box-shadow: 0 2px 10px rgba(155,74,58,0.28);
            cursor: pointer;
        }
        .btn-or:hover {
            background: linear-gradient(135deg, var(--ame-terre-dark), #5C2A1F);
            color: white; transform: translateY(-1px);
            box-shadow: 0 4px 16px rgba(155,74,58,0.38);
        }

        /* Forms */
        .form-control-tissu {
            border: 1.5px solid var(--sable-dark);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            font-size: 14px;
            color: var(--texte);
            background: var(--blanc);
            transition: var(--transition);
            width: 100%;
        }
        .form-control-tissu:focus {
            border-color: var(--or);
            box-shadow: 0 0 0 3px rgba(155,74,58,0.12);
            outline: none;
        }
        .form-label-tissu {
            font-size: 13px; font-weight: 600;
            color: var(--texte); margin-bottom: 6px;
            display: block;
        }

        /* Alerts */
        .alert-tissu {
            border-radius: var(--radius); border: none;
            border-left: 4px solid var(--or);
            background: var(--sable);
            color: var(--texte);
            padding: 12px 16px; font-size: 14px;
        }
        .alert-tissu.error   { border-color: var(--rouge-fes); background: #fff1f0; }
        .alert-tissu.success { border-color: var(--vert-atlas); background: #f0fdf4; }
    </style>

    @stack('styles')
</head>

<body>
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
