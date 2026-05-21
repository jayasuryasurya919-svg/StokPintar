<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'StokPintar')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1a1c1b;
            --muted: #404944;
            --line: #c0c8c3;
            --soft: #f9faf7;
            --surface-low: #f3f4f1;
            --surface-high: #e7e8e6;
            --surface-container: #edeeeb;
            --panel: #ffffff;
            --green: #001e15;
            --green-container: #003527;
            --green-soft: #b0f0d6;
            --green-mint: #adedd3;
            --blue: #2b6954;
            --blue-soft: #e6f7ef;
            --amber: #ff9939;
            --amber-soft: #ffdcc3;
            --rose: #ba1a1a;
            --rose-soft: #ffdad6;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--soft);
            color: var(--ink);
            font-size: 14px;
            line-height: 1.45;
        }

        a { color: inherit; }
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 21px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            font-variation-settings: 'FILL' 0, 'wght' 450, 'GRAD' 0, 'opsz' 24;
        }
        .shell { min-height: 100vh; display: grid; grid-template-columns: 280px minmax(0, 1fr); }
        .guest-shell { min-height: 100vh; }
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            background: var(--green-container);
            color: white;
            border-right: none;
            padding: 24px 0 0;
            display: flex;
            flex-direction: column;
            gap: 18px;
            z-index: 50;
        }

        @keyframes pulseLogo { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.08); } }
        .brand { display: flex; align-items: center; gap: 14px; font-weight: 800; font-size: 20px; text-decoration: none; padding: 0 28px; color: white; }
        .brand-mark {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: var(--green-mint);
            color: var(--green-container);
            font-weight: 900;
            box-shadow: 0 12px 22px rgba(0, 0, 0, .12);
            animation: pulseLogo 3s infinite ease-in-out;
        }
        .brand strong { display: block; font-size: 20px; line-height: 1.1; letter-spacing: -.02em; }
        .brand small { display:block; margin-top:4px; color: rgba(255,255,255,.6); font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }

        .tenant {
            margin: 0 24px;
            padding: 14px 16px;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 12px;
            background: rgba(0,0,0,.15);
        }
        .tenant strong, .tenant span { display: block; }
        .tenant span { margin-top: 4px; color: rgba(255,255,255,.6); font-size: 13px; }
        .tenant select {
            width: 100%;
            margin-top: 10px;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 8px;
            background: rgba(0,0,0,.18);
            color: white;
            padding: 8px 10px;
            font-weight: 700;
        }

        .nav { display: grid; gap: 4px; padding: 0 12px; }
        .nav a, .logout-button {
            color: rgba(255,255,255,.7);
            text-decoration: none;
            padding: 14px 16px;
            border-radius: 12px 0 0 12px;
            font-size: 14px;
            font-weight: 600;
            border: 0;
            border-right: 4px solid transparent;
            background: transparent;
            text-align: left;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background .16s ease, color .16s ease, border-color .16s ease;
        }
        .nav a.active, .nav a:hover, .logout-button:hover {
            background: rgba(255,255,255,.08);
            color: white;
        }
        .nav a.active { border-right-color: var(--green-mint); font-weight: 800; background: rgba(255,255,255,.12); }

        .role-box {
            margin-top: auto;
            margin-left: 24px;
            margin-right: 24px;
            margin-bottom: 24px;
            padding: 14px 16px;
            border-radius: 12px;
            background: rgba(0,0,0,.15);
            border: 1px solid rgba(255,255,255,.1);
            color: var(--green-mint);
        }
        .role-box small { display: block; margin-top: 4px; color: rgba(255,255,255,.6); }

        main { padding: 0; overflow-x: hidden; }
        .app-main { min-width: 0; }
        .app-header {
            position: sticky;
            top: 0;
            z-index: 40;
            height: 72px;
            padding: 0 32px;
            background: rgba(255,255,255,.92);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--line);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .mobile-menu-btn {
            display: none;
            width: 40px;
            height: 40px;
            border: 0;
            background: transparent;
            color: var(--ink);
            border-radius: 8px;
            cursor: pointer;
            place-items: center;
        }
        .mobile-menu-btn:hover { background: var(--surface-low); }
        .app-header-left {
            display: flex;
            align-items: center;
            gap: 18px;
            flex: 1;
            min-width: 0;
        }
        .app-header-right {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 0 0 auto;
        }
        .app-search {
            position: relative;
            width: min(100%, 440px);
        }
        .app-search .material-symbols-outlined {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 22px;
            color: var(--muted);
        }
        .app-search input {
            padding-left: 44px;
            min-height: 40px;
            border-radius: 999px;
            background: var(--surface-low);
        }
        .app-icon-button {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: 0;
            background: transparent;
            color: var(--muted);
            display: grid;
            place-items: center;
            cursor: pointer;
        }
        .app-icon-button:hover { background: var(--surface-low); color: var(--ink); }
        .app-avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: 2px solid var(--surface-container);
            background: var(--green-mint);
            color: var(--green-container);
            display: grid;
            place-items: center;
            font-weight: 800;
            overflow: hidden;
        }
        .app-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .app-divider {
            width: 1px;
            height: 28px;
            background: var(--line);
        }
        .app-content {
            padding: 32px;
        }
        .guest-shell > main.auth-page { padding: 0; overflow: hidden; }
        .page-stack { display: grid; gap: 18px; }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            margin: 0 0 24px;
            padding: 0;
            background: transparent;
            border-bottom: 0;
        }
        h1 { margin: 0; font-size: 32px; line-height: 40px; font-weight: 800; letter-spacing: -.03em; color: var(--green); }
        .subtitle { color: var(--muted); margin: 7px 0 0; max-width: 760px; font-size: 16px; line-height: 24px; }

        .actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
        .action-row { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .btn {
            border: 1px solid var(--line);
            background: var(--panel);
            color: var(--ink);
            border-radius: 12px;
            min-height: 40px;
            padding: 10px 16px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: background .16s ease, border-color .16s ease, color .16s ease, transform .16s ease;
        }
        .btn:hover { background: var(--surface-low); }
        .btn:active { transform: scale(.98); }
        .btn.primary { background: var(--green-container); color: white; border-color: var(--green-container); }
        .btn.primary:hover { background: var(--green-container); border-color: var(--green-container); }
        .btn.transaction { background: var(--amber); color: white; border-color: var(--amber); }
        .btn.danger { color: var(--rose); border-color: rgba(186,26,26,.18); background: #fff; }
        .btn.small { padding: 7px 10px; font-size: 13px; }

        .card { background: var(--panel); border: 1px solid var(--line); border-radius: 16px; padding: 24px; }
        .card.flush { padding: 0; overflow: hidden; }
        .card.compact { padding: 18px; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
        .grid-2 { display: grid; grid-template-columns: minmax(0, 1fr) minmax(320px, .75fr); gap: 16px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .filter-grid { display: grid; grid-template-columns: minmax(220px, 1fr) minmax(160px, .45fr) auto; gap: 14px; align-items: end; }
        .metric-label { margin: 0 0 8px; color: var(--muted); font-size: 12px; text-transform: uppercase; font-weight: 800; }
        .metric-value { margin: 0; font-size: 24px; line-height: 32px; font-weight: 800; color: var(--ink); font-variant-numeric: tabular-nums; }
        .metric-note { margin: 7px 0 0; color: var(--muted); font-size: 13px; }
        .metric-primary .metric-value { color: var(--green); }
        .metric-critical { background: var(--rose-soft); border-color: rgba(186,26,26,.2); }
        .metric-critical .metric-label, .metric-critical .metric-value { color: var(--rose); }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .metric-card { position: relative; overflow: hidden; min-height: 128px; border-color: rgba(192,200,195,.72); animation: slideUp 0.5s ease backwards; }
        .metric-card:nth-child(1) { animation-delay: 0.05s; }
        .metric-card:nth-child(2) { animation-delay: 0.1s; }
        .metric-card:nth-child(3) { animation-delay: 0.15s; }
        .metric-card:nth-child(4) { animation-delay: 0.2s; }
        .metric-card .material-symbols-outlined {
            position: absolute;
            right: 18px;
            bottom: 14px;
            color: rgba(0,53,39,.12);
            font-size: 54px;
        }
        .metric-critical .material-symbols-outlined { color: rgba(186,26,26,.16); }
        .section-title { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
        .section-title h2 { margin: 0; font-size: 18px; }

        /* TOAST NOTIFICATION */
        .toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 12px; }
        .toast {
            background: white; color: var(--ink); padding: 16px 20px; border-radius: 12px;
            box-shadow: 0 12px 32px rgba(0,30,21,.12); border-left: 4px solid var(--green);
            display: flex; align-items: center; gap: 12px; font-weight: 600;
            transform: translateX(120%); transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .toast.toast-error { border-left-color: var(--rose); }
        .toast.show { transform: translateX(0); }
        
        /* MOBILE OVERLAY */
        .mobile-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 45; backdrop-filter: blur(2px); }
        .mobile-overlay.open { display: block; }
        
        @media (max-width: 900px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: fixed; left: -280px; width: 280px; transition: left .3s ease; box-shadow: 12px 0 32px rgba(0,0,0,.2); }
            .sidebar.open { left: 0; }
            .mobile-menu-btn { display: grid; }
            .app-header { padding: 0 20px; }
            .app-content { padding: 20px; }
            .grid-4, .grid-3, .grid-2 { grid-template-columns: 1fr; }
            .form-grid { grid-template-columns: 1fr; }
            .pos-layout { grid-template-columns: 1fr; }
            .pos-register-body { grid-template-columns: 1fr; }
        }

        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 26px;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            background: var(--blue-soft);
            color: var(--green-container);
        }
        .badge.low { background: var(--rose-soft); color: var(--rose); }
        .badge.ok { background: var(--green-soft); color: var(--green-container); }
        .badge.money { background: var(--amber-soft); color: #6a3700; }

        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { text-align: left; padding: 14px 24px; border-bottom: 1px solid var(--line); vertical-align: middle; }
        th { color: var(--muted); background: var(--surface-low); font-size: 12px; text-transform: uppercase; letter-spacing: .08em; font-weight: 800; }
        tbody tr:hover { background: rgba(0,53,39,.035); }
        .table-wrap { overflow-x: auto; }
        .table-footer { padding: 16px 24px; background: var(--panel); }
        .empty-cell { padding: 30px 24px; color: var(--muted); text-align: center; }

        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .form-shell { max-width: 980px; }
        .form-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; padding-top: 6px; }
        .field { display: grid; gap: 6px; }
        .field-label-row { display:flex; justify-content:space-between; align-items:center; gap:12px; }
        .field-label-row a { color:var(--blue); font-weight:800; text-decoration:none; }
        .field-label-row a:hover { text-decoration:underline; }
        label { font-size: 13px; color: var(--muted); font-weight: 800; }
        input, select, textarea {
            width: 100%;
            border: 1px solid var(--line);
            background: white;
            border-radius: 12px;
            min-height: 40px;
            padding: 9px 12px;
            color: var(--ink);
            font: inherit;
        }
        input:focus, select:focus, textarea:focus {
            outline: 0;
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(176, 240, 214, .56);
        }
        textarea { min-height: 88px; resize: vertical; }

        .alert { padding: 12px 14px; border-radius: 8px; margin-bottom: 16px; font-weight: 700; }
        .alert.success { background: var(--green-soft); color: var(--green); }
        .alert.error { background: var(--rose-soft); color: var(--rose); }
        .muted { color: var(--muted); }
        .text-right { text-align: right; }
        .price { font-weight: 800; white-space: nowrap; color: var(--green); font-variant-numeric: tabular-nums; }
        .stack { display: grid; gap: 14px; }
        .inline-form { display: inline; }
        .panel-header {
            padding: 16px 24px;
            border-bottom: 1px solid var(--line);
            background: var(--surface-low);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .panel-header h2, .panel-header h3 { margin: 0; font-size: 20px; line-height: 28px; }
        .card.flush .section-title { margin: 0; padding: 16px 24px; background: var(--surface-low); border-bottom: 1px solid var(--line); }
        .activity-list { display: grid; gap: 10px; }
        .activity-item {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--line);
        }
        .activity-item:last-child { border-bottom: 0; padding-bottom: 0; }
        .activity-item strong { display: block; }
        .activity-item .muted { margin-top: 3px; font-size: 12px; }
        .auth-page {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(420px, 3fr) minmax(360px, 2fr);
            background: var(--panel);
        }
        .auth-visual {
            position: relative;
            overflow: hidden;
            background: var(--green-container);
            color: white;
            min-height: 100vh;
            padding: 32px;
            display: flex;
            align-items: flex-end;
        }
        .auth-visual img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .auth-visual::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(0deg, rgba(0,53,39,.84), rgba(0,53,39,.14));
        }
        .auth-copy { position: relative; z-index: 1; max-width: 560px; }
        .auth-brand-line { display: flex; align-items: center; gap: 8px; color: var(--green-soft); margin-bottom: 16px; font-weight: 800; font-size: 24px; }
        .auth-brand-line .material-symbols-outlined { font-size: 40px; font-variation-settings: 'FILL' 1, 'wght' 500, 'GRAD' 0, 'opsz' 40; }
        .auth-copy h2 { font-size: 32px; line-height: 40px; margin: 0 0 12px; }
        .auth-copy p { margin: 0; color: rgba(255,255,255,.82); font-size: 16px; line-height: 24px; }
        .auth-form-pane {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
            background: var(--panel);
        }
        .auth-card { width: 100%; max-width: 420px; }
        .auth-card header { margin-bottom: 28px; }
        .auth-card h1 { font-size: 24px; line-height: 32px; }
        .auth-brand-mobile { display: none; margin-bottom: 28px; }
        .google-login-button {
            width: 100%;
            min-height: 46px;
            margin: 0;
            background: white;
            border-color: var(--line);
            color: var(--ink);
            font-weight: 800;
        }
        .google-login-button span {
            width: 24px;
            height: 24px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: #fff;
            border: 1px solid var(--line);
            color: #4285f4;
            font-weight: 900;
            font-family: Arial, sans-serif;
        }
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 22px 0;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .auth-divider::before,
        .auth-divider::after {
            content: "";
            height: 1px;
            flex: 1;
            background: var(--line);
        }
        .icon-field { position: relative; }
        .icon-field > .material-symbols-outlined {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 20px;
            pointer-events: none;
        }
        .icon-field input { padding-left: 40px; }
        .icon-field .field-action {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: var(--muted);
            padding: 4px;
            cursor: pointer;
        }
        .icon-field .field-action:hover { color: var(--green); }
        .icon-field.has-action input { padding-right: 44px; }
        .auth-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: -4px;
        }
        .remember-check {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
            font-size: 14px;
            font-weight: 700;
        }
        .remember-check input {
            width: auto;
            min-height: auto;
        }
        .auth-card footer {
            margin-top: 28px;
            text-align: center;
            color: var(--muted);
        }
        .auth-card footer a { color: var(--blue); font-weight: 800; text-decoration: none; }
        .auth-card footer a:hover { text-decoration: underline; }
        .auth-bottom-footer {
            position: fixed;
            right: 0;
            bottom: 0;
            width: 40%;
            background: var(--panel);
            border-top: 1px solid var(--line);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            color: var(--muted);
            font-size: 12px;
        }
        .auth-bottom-footer strong { color: var(--green); font-size: 14px; }
        .auth-bottom-footer nav { display: flex; gap: 14px; }
        .auth-bottom-footer a { color: var(--muted); text-decoration: none; font-weight: 700; }
        .auth-bottom-footer a:hover { color: var(--green); }
        .pos-layout { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 16px; align-items: start; }
        .pos-product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 16px; }
        .product-tile { border: 1px solid var(--line); border-radius: 8px; overflow: hidden; background: var(--panel); }
        .product-media {
            aspect-ratio: 1;
            display: grid;
            place-items: center;
            background: var(--surface-container);
            color: var(--green);
            font-size: 28px;
            font-weight: 900;
        }
        .product-tile-body { padding: 14px; display: grid; gap: 8px; }
        .product-tile h3 { margin: 0; font-size: 15px; line-height: 20px; }
        .stock-form-row { display: grid; grid-template-columns: 140px 130px 1fr auto; gap: 8px; align-items: end; }
        .feature-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-top: 28px; }
        .feature-card { background: var(--surface-low); border: 1px solid var(--line); border-radius: 12px; padding: 22px; display: grid; gap: 10px; }
        .feature-card.primary { background: var(--green-container); color: var(--green-soft); border-color: var(--green-container); }
        .feature-card .material-symbols-outlined { color: var(--green); font-size: 32px; }
        .feature-card.primary .material-symbols-outlined { color: var(--green-soft); }
        .feature-wide { grid-column: 1 / -1; position: relative; min-height: 180px; border-radius: 12px; overflow: hidden; border: 1px solid var(--line); background: var(--green-container); color: white; display: flex; align-items: end; padding: 24px; }
        .feature-wide::before { content: ""; position: absolute; inset: 0; background: linear-gradient(0deg, rgba(0,53,39,.72), rgba(0,53,39,.18)); }
        .feature-wide span { position: relative; font-weight: 800; }
        .register-shell { min-height: 100vh; background: var(--soft); display: flex; flex-direction: column; }
        .register-header { height: 64px; background: var(--panel); border-bottom: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; padding: 0 32px; }
        .register-main { flex: 1; display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(360px, .9fr); gap: 48px; align-items: center; max-width: 1180px; margin: 0 auto; padding: 48px 24px; width: 100%; }
        .register-copy h1 { color: var(--green); font-size: 32px; line-height: 40px; margin: 0 0 12px; }
        .register-copy p { color: var(--muted); font-size: 16px; line-height: 24px; max-width: 460px; }
        .register-card { background: var(--panel); border: 1px solid var(--line); border-radius: 12px; padding: 32px; }
        .register-footer { background: var(--panel); border-top: 1px solid var(--line); padding: 22px 24px; display: flex; justify-content: space-between; align-items: center; gap: 18px; color: var(--muted); }
        .register-brand { color: var(--green); font-size: 24px; }
        .register-footer .register-brand { font-size: 14px; }
        .register-card header { margin-bottom: 24px; }
        .terms-check { display:flex; align-items:flex-start; gap:10px; color:var(--muted); font-weight:500; }
        .terms-check input { width:auto; min-height:auto; margin-top:3px; }
        .login-link-box { margin-top:24px; padding-top:20px; border-top:1px solid var(--line); text-align:center; }
        .login-link-box a { color:var(--green); font-weight:800; text-decoration:none; }
        .login-link-box a:hover { text-decoration:underline; }
        .register-footer a { color: var(--muted); text-decoration: none; font-weight: 700; }
        .register-footer a:hover { color: var(--green); }
        .register-footer-links { display:flex; gap:18px; flex-wrap:wrap; }
        .dashboard-chart { height: 250px; }
        .chart-card { margin: 18px 0; }
        .chart-legend { display: flex; align-items: center; gap: 8px; color: var(--muted); font-weight: 700; font-size: 13px; }
        .chart-dot { width: 12px; height: 12px; border-radius: 999px; background: var(--green); }
        .product-name-cell { display: flex; align-items: center; gap: 12px; }
        .product-thumb { width: 42px; height: 42px; border-radius: 8px; border: 1px solid var(--line); background: var(--surface-container); color: var(--green); display: grid; place-items: center; font-weight: 900; }
        .inventory-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-top: 18px; }
        .inventory-stat { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 20px; display: flex; gap: 14px; align-items: center; }
        .stat-icon { width: 48px; height: 48px; border-radius: 999px; display: grid; place-items: center; background: var(--green-container); color: var(--green-soft); }
        .pos-mobile-header, .pos-bottom-nav, .pos-cart-sticky { display: none; }
        .category-pills { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 2px; }
        .pill { border: 0; border-radius: 999px; padding: 7px 14px; background: var(--surface-container); color: var(--muted); font-weight: 800; white-space: nowrap; }
        .pill.active { background: var(--green-container); color: var(--green-soft); }
        .search-row { display: flex; gap: 8px; align-items: center; }
        .search-box { position: relative; flex: 1; }
        .search-box .material-symbols-outlined { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--line); }
        .search-box input { padding-left: 40px; }
        .cart-summary { background: var(--surface-high); border: 1px solid var(--line); border-radius: 16px; padding: 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px; box-shadow: 0 10px 28px rgba(11,28,48,.12); }
        .pos-workspace {
            margin: -32px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--soft);
        }
        .pos-register-bar {
            flex: 0 0 auto;
            min-height: 88px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 24px 32px;
            background: var(--panel);
            border-bottom: 1px solid var(--line);
        }
        .pos-search {
            position: relative;
            flex: 1;
            max-width: 560px;
        }
        .pos-search > .material-symbols-outlined {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--line);
        }
        .pos-search input {
            padding-left: 42px;
            padding-right: 54px;
            background: var(--surface-low);
        }
        .pos-search [data-open-barcode-scanner] {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            min-height: 36px;
            width: 42px;
            padding: 0;
            justify-content: center;
        }
        .pos-register-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }
        .pos-segments {
            display: flex;
            gap: 4px;
            padding: 4px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--surface-container);
        }
        .pos-segments button {
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--muted);
            padding: 8px 14px;
            font-weight: 800;
            cursor: pointer;
            white-space: nowrap;
        }
        .pos-segments button.active {
            background: var(--green);
            color: white;
        }
        .pos-register-body {
            flex: 0 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 400px;
            align-items: stretch;
            overflow: visible;
        }
        .pos-catalog {
            grid-column: 1;
            grid-row: 1;
            min-width: 0;
            overflow: visible;
            padding: 32px 32px 0;
        }
        .pos-catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 16px;
        }
        .pos-catalog-pagination {
            grid-column: 1;
            grid-row: 2;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 18px 32px 32px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }
        .pos-catalog-pagination:empty {
            display: none;
        }
        .pos-catalog-pagination div {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 6px;
        }
        .pos-catalog-pagination button {
            min-width: 36px;
            height: 36px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            color: var(--muted);
            font-weight: 900;
            cursor: pointer;
        }
        .pos-catalog-pagination button.active {
            border-color: var(--green);
            background: var(--green);
            color: white;
        }
        .pos-product-card {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--panel);
            transition: border-color .16s ease, transform .16s ease, box-shadow .16s ease;
        }
        .pos-product-card[hidden] {
            display: none !important;
        }
        .pos-product-card:hover {
            border-color: var(--green);
            box-shadow: 0 12px 28px rgba(11,28,48,.08);
            transform: translateY(-1px);
        }
        .pos-product-add-area {
            display: block;
            width: 100%;
            padding: 0;
            border: 0;
            background: transparent;
            color: inherit;
            text-align: left;
            cursor: pointer;
        }
        .pos-product-media {
            aspect-ratio: 1;
            display: grid;
            place-items: center;
            background: var(--surface-high);
            color: var(--green);
            overflow: hidden;
        }
        .pos-product-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .pos-product-media span {
            font-size: 30px;
            font-weight: 900;
            letter-spacing: 0;
        }
        .pos-product-info {
            display: grid;
            gap: 4px;
            padding: 20px 24px 8px;
        }
        .pos-product-info h3 {
            margin: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 20px;
            line-height: 28px;
            font-weight: 700;
        }
        .pos-product-info p {
            margin: 0;
            color: var(--green);
            font-weight: 900;
            font-variant-numeric: tabular-nums;
        }
        .pos-product-stock {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 8px 24px 20px;
            color: var(--muted);
            font-size: 12px;
        }
        .pos-add-button {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 999px;
            display: grid;
            place-items: center;
            background: var(--green);
            color: white;
            cursor: pointer;
            transition: transform .16s ease, background .16s ease;
            flex: 0 0 auto;
        }
        .pos-add-button:hover { background: var(--green-container); }
        .pos-add-button:active { transform: scale(.9); }
        .pos-checkout-panel {
            grid-column: 2;
            grid-row: 1;
            min-height: 0;
            align-self: stretch;
            display: flex;
            flex-direction: column;
            background: var(--panel);
            border-left: 1px solid var(--line);
        }
        .pos-panel-header {
            flex: 0 0 auto;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            padding: 24px;
            border-bottom: 1px solid var(--line);
        }
        .pos-panel-header h2 {
            margin: 0 0 6px;
            font-size: 24px;
            line-height: 32px;
        }
        .pos-panel-header p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
        }
        .pos-cart-items {
            flex: 0 1 auto;
            max-height: 360px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 24px;
        }
        .pos-cart-line {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(76px, auto);
            gap: 12px;
            align-items: start;
        }
        .pos-cart-line-info {
            display: grid;
            gap: 3px;
            min-width: 0;
        }
        .pos-cart-line-info strong {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pos-cart-line-info span {
            color: var(--muted);
            font-size: 12px;
        }
        .pos-cart-stepper {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px;
            border-radius: 8px;
            background: var(--surface-container);
        }
        .pos-cart-stepper button {
            width: 26px;
            height: 26px;
            border: 0;
            border-radius: 6px;
            display: grid;
            place-items: center;
            color: var(--ink);
            background: transparent;
            cursor: pointer;
        }
        .pos-cart-stepper button:hover { background: var(--surface-high); }
        .pos-cart-stepper .material-symbols-outlined { font-size: 18px; }
        .pos-cart-stepper span {
            min-width: 22px;
            text-align: center;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }
        .pos-cart-line-total {
            display: grid;
            justify-items: end;
            gap: 3px;
        }
        .pos-cart-line-total strong {
            font-size: 13px;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }
        .pos-cart-line-total button {
            border: 0;
            background: transparent;
            color: var(--rose);
            padding: 0;
            cursor: pointer;
        }
        .pos-cart-line-total .material-symbols-outlined { font-size: 19px; }
        .pos-payment-box {
            flex: 0 0 auto;
            display: grid;
            gap: 16px;
            padding: 24px;
            background: var(--surface-low);
            border-top: 1px solid var(--line);
        }
        .pos-totals {
            display: grid;
            gap: 7px;
        }
        .pos-totals div {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            color: var(--muted);
        }
        .pos-totals strong {
            color: var(--ink);
            font-variant-numeric: tabular-nums;
        }
        .pos-totals .total {
            margin-top: 4px;
            padding-top: 10px;
            border-top: 1px solid var(--line);
            color: var(--ink);
            font-size: 20px;
            line-height: 28px;
            font-weight: 700;
        }
        .pos-totals .total strong {
            color: var(--green);
            font-size: 20px;
        }
        .pos-payment-title {
            margin: 0 0 8px;
            font-weight: 800;
        }
        .pos-payment-methods {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }
        .pos-payment-methods button {
            min-height: 62px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            color: var(--muted);
            font-weight: 800;
            cursor: pointer;
        }
        .pos-payment-methods button.active {
            border: 2px solid var(--green);
            background: var(--green);
            color: white;
        }
        .pos-money-field {
            position: relative;
        }
        .pos-money-field span {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-weight: 800;
        }
        .pos-money-field input {
            min-height: 52px;
            padding-left: 40px;
            border-width: 2px;
            color: var(--green);
            font-size: 20px;
            line-height: 28px;
            font-weight: 900;
            font-variant-numeric: tabular-nums;
        }
        .pos-change-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 13px 16px;
            border-radius: 8px;
            background: var(--surface-high);
            font-weight: 800;
        }
        .pos-change-row strong {
            color: var(--blue);
            font-size: 20px;
            line-height: 28px;
            font-variant-numeric: tabular-nums;
        }
        .pos-process-button {
            width: 100%;
            min-height: 58px;
            border-radius: 12px;
            background: #d97706;
            border-color: #d97706;
            color: white;
            font-size: 18px;
            line-height: 28px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .pos-recent-sales {
            padding: 0 32px 28px;
        }
        .pos-recent-list {
            display: grid;
            gap: 8px;
        }
        .pos-recent-list article {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .pos-recent-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex: 1;
            min-width: 0;
        }
        .pos-recent-summary div {
            min-width: 0;
        }
        .pos-recent-summary strong:first-child {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pos-recent-summary span {
            display: block;
            color: var(--muted);
            font-size: 12px;
            margin-top: 2px;
        }
        .pos-recent-summary > strong {
            color: var(--ink);
            font-size: 14px;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .pos-empty {
            grid-column: 1 / -1;
            min-height: 260px;
            display: grid;
            place-items: center;
            align-content: center;
            gap: 8px;
            text-align: center;
            border: 1px dashed var(--line);
            border-radius: 12px;
            background: var(--panel);
            color: var(--muted);
        }
        .pos-empty .material-symbols-outlined {
            color: var(--green);
            font-size: 40px;
        }
        .pagination-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            color: var(--muted);
            font-size: 14px;
        }
        .pagination-links { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .pagination-link {
            min-width: 36px;
            min-height: 36px;
            padding: 8px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            color: var(--ink);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-weight: 800;
        }
        .pagination-link:hover { background: var(--surface-low); color: var(--green); }
        .pagination-link.active { background: var(--green); border-color: var(--green); color: white; }
        .pagination-link.disabled { color: var(--muted); opacity: .5; pointer-events: none; }
        .pagination-summary { margin: 0; }

        @media (max-width: 980px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar {
                position: sticky;
                top: 0;
                z-index: 60;
                height: auto;
                max-height: none;
                padding: 12px 0;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }
            .brand, .tenant, .role-box { display: none; }
            .nav { display: flex; gap: 4px; overflow-x: auto; padding: 0 12px; }
            .nav form { flex: 0 0 auto; }
            .nav a, .logout-button { border-right: 0; border-radius: 999px; padding: 9px 12px; white-space: nowrap; }
            .nav a.active { border-right-color: transparent; background: var(--green-container); color: var(--green-soft); }
            .grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .grid-2, .grid-3, .pos-layout, .auth-page { grid-template-columns: 1fr; }
            .filter-grid { grid-template-columns: 1fr 1fr; }
            .filter-grid .field:last-child { grid-column: 1 / -1; }
            .register-main { grid-template-columns: 1fr; }
            .register-copy { display: none; }
            .auth-visual { display: none; }
            .auth-brand-mobile { display: flex; padding: 0; }
            .auth-form-pane { min-height: 100vh; padding: 32px 20px; }
            .auth-bottom-footer { position: static; width: 100%; flex-direction: column; text-align: center; }
            .pos-register-bar { align-items: stretch; flex-direction: column; }
            .pos-search { max-width: none; width: 100%; }
            .pos-register-actions { justify-content: space-between; width: 100%; overflow-x: auto; }
            .pos-register-body { grid-template-columns: 1fr; overflow: visible; }
            .pos-catalog { overflow: visible; }
            .pos-catalog, .pos-catalog-pagination, .pos-checkout-panel { grid-column: 1; }
            .pos-catalog { grid-row: 1; }
            .pos-catalog-pagination { grid-row: 2; }
            .pos-checkout-panel { grid-row: 3; border-left: 0; border-top: 1px solid var(--line); }
        }

        @media (max-width: 620px) {
            main { padding: 16px; }
            .topbar { margin: -16px -16px 18px; padding: 18px 16px; flex-direction: column; align-items: stretch; }
            .topbar { flex-direction: column; }
            .actions { justify-content: flex-start; width: 100%; }
            .btn { width: 100%; }
            .grid-4, .form-grid, .filter-grid { grid-template-columns: 1fr; }
            .filter-grid .field:last-child { grid-column: auto; }
            .action-row, .form-actions { display: grid; grid-template-columns: 1fr; width: 100%; }
            .activity-item { grid-template-columns: 1fr; }
            .stock-form-row { grid-template-columns: 1fr; }
            .card { padding: 16px; }
            th, td { padding: 12px 14px; }
            table { min-width: 680px; }
            .register-header, .register-footer { flex-direction: column; height: auto; align-items: flex-start; padding: 16px; }
            .register-main { padding: 24px 16px; }
            .register-card { padding: 22px; }
            .feature-grid, .inventory-stats { grid-template-columns: 1fr; }
            .pos-page .topbar { display: none; }
            .pos-page { margin: -16px; padding-bottom: 140px; background: var(--soft); }
            .pos-mobile-header { display: flex; position: sticky; top: 0; z-index: 30; height: 56px; background: var(--panel); border-bottom: 1px solid var(--line); align-items: center; justify-content: space-between; padding: 0 16px; }
            .pos-mobile-header h1 { color: var(--green); font-size: 20px; line-height: 28px; }
            .pos-page .pos-layout { display: block; }
            .pos-page .card { border: 0; border-radius: 0; background: transparent; }
            .pos-page .section-title { display: none; }
            .pos-search-panel { display: grid !important; gap: 14px; background: var(--panel); padding: 16px; border-bottom: 1px solid var(--line); }
            .pos-product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; padding: 14px 16px; }
            .product-tile { border-radius: 12px; }
            .product-tile-body { padding: 12px; }
            .product-tile h3 { font-size: 14px; }
            .product-media { font-size: 22px; }
            .pos-page .form-grid { padding: 0 16px; }
            .pos-page > .pos-layout > .card:last-child { display: none; }
            .pos-cart-sticky { display: block; position: fixed; left: 0; right: 0; bottom: 64px; z-index: 40; padding: 0 16px 14px; }
            .pos-bottom-nav { display: flex; position: fixed; bottom: 0; left: 0; right: 0; height: 64px; z-index: 50; background: var(--panel); border-top: 1px solid var(--line); align-items: center; justify-content: space-around; }
            .pos-bottom-nav a { text-decoration: none; color: var(--muted); display: grid; justify-items: center; gap: 2px; font-size: 11px; font-weight: 800; }
            .pos-bottom-nav a.active { color: var(--green-soft); background: var(--green-container); border-radius: 999px; padding: 6px 14px; }
            .pos-workspace { margin: -16px; min-height: auto; }
            .pos-register-bar { min-height: auto; padding: 16px; }
            .pos-register-actions { display: grid; gap: 10px; }
            .pos-segments { overflow-x: auto; }
            .pos-register-body { display: grid; }
            .pos-catalog { padding: 16px 16px 0; }
            .pos-catalog-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .pos-catalog-pagination { align-items: flex-start; flex-direction: column; padding: 14px 16px 16px; }
            .pos-catalog-pagination div { justify-content: flex-start; }
            .pos-product-info { padding: 12px 12px 6px; }
            .pos-product-info h3 { font-size: 14px; line-height: 20px; }
            .pos-product-stock { padding: 6px 12px 12px; }
            .pos-checkout-panel { margin: 0 16px 18px; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
            .pos-panel-header, .pos-cart-items, .pos-payment-box { padding: 16px; }
            .pos-cart-items { max-height: 280px; }
            .pos-cart-line { grid-template-columns: 1fr; }
            .pos-cart-stepper { justify-content: center; width: max-content; }
            .pos-payment-methods { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .pos-payment-methods button { font-size: 12px; }
            .pos-recent-sales { padding: 0 16px 24px; }
            .pos-recent-list article { align-items: flex-start; }
            .pos-recent-summary { align-items: flex-start; flex-direction: column; gap: 4px; }
        }
    </style>
</head>
<body>
    @auth
    <div class="shell">
        <div class="mobile-overlay" id="mobile-overlay"></div>
        <aside class="sidebar" id="sidebar">
            <a class="brand" href="{{ auth()->user()->canPermission('platform.manage') ? route('platform.index') : route('dashboard') }}">
                <div class="brand-mark"><span class="material-symbols-outlined">inventory_2</span></div>
                <span><strong>StokPintar</strong><small>Versi Full</small></span>
            </a>

            @php
                $tenantStores = collect();
                $activeStoreId = session('store_id');
                if (auth()->user()->tenant_id && ! auth()->user()->canPermission('platform.manage')) {
                    $tenantStores = auth()->user()->storeAccess()->exists()
                        ? auth()->user()->storeAccess()->orderBy('name')->get()
                        : \App\Models\Store::query()->where('tenant_id', auth()->user()->tenant_id)->orderByDesc('is_default')->orderBy('name')->get();
                    $activeStoreId = $activeStoreId ?: $tenantStores->firstWhere('is_default', true)?->id ?: $tenantStores->first()?->id;
                }
                $activeStore = $tenantStores->firstWhere('id', (int) $activeStoreId);
            @endphp
            <div class="tenant">
                <strong>{{ auth()->user()->canPermission('platform.manage') ? 'Platform StokPintar' : (auth()->user()->tenant?->name ?? 'Toko Aktif') }}</strong>
                <span>{{ auth()->user()->name }} - {{ auth()->user()->roleLabel() }}</span>
                @if($tenantStores->count() > 1)
                    <form method="POST" action="{{ route('stores.context.update') }}">
                        @csrf
                        <select name="store_id" onchange="this.form.submit()" aria-label="Ganti toko aktif">
                            @foreach($tenantStores as $store)
                                <option value="{{ $store->id }}" @selected((int) $activeStoreId === $store->id)>{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @elseif($activeStore)
                    <span>Toko aktif: {{ $activeStore->name }}</span>
                @endif
            </div>

            <nav class="nav" aria-label="Navigasi utama">
                @if(auth()->user()->canPermission('platform.manage'))
                    <a href="{{ route('platform.index') }}" class="{{ request()->routeIs('platform.*') ? 'active' : '' }}"><span class="material-symbols-outlined">domain</span> Admin Platform</a>
                @endif
                @if(auth()->user()->canPermission('dashboard.view'))
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"><span class="material-symbols-outlined">dashboard</span> Dashboard</a>
                @endif
                @if(auth()->user()->canPermission('products.manage') || auth()->user()->canPermission('stock.mutate'))
                    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}"><span class="material-symbols-outlined">inventory_2</span> Produk & Stok</a>
                @endif
                @if(auth()->user()->canPermission('users.manage'))
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}"><span class="material-symbols-outlined">groups</span> Tim & Akses</a>
                @endif
                @if(auth()->user()->canPermission('subscription.manage'))
                    <a href="{{ route('subscription.index') }}" class="{{ request()->routeIs('subscription.*') ? 'active' : '' }}"><span class="material-symbols-outlined">workspace_premium</span> Paket & Setup</a>
                @endif
                @if(auth()->user()->canPermission('subscription.manage'))
                    <a href="{{ route('business-profile.edit') }}" class="{{ request()->routeIs('business-profile.*') ? 'active' : '' }}"><span class="material-symbols-outlined">storefront</span> Profil Bisnis</a>
                @endif
                @if(auth()->user()->canPermission('pos.access'))
                    <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }}"><span class="material-symbols-outlined">point_of_sale</span> POS Kasir</a>
                @endif
                @if(auth()->user()->canPermission('reports.view'))
                    <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><span class="material-symbols-outlined">assessment</span> Laporan</a>
                @endif
                @if(auth()->user()->canPermission('stock_history.view'))
                    <a href="{{ route('stock-history.index') }}" class="{{ request()->routeIs('stock-history.*') ? 'active' : '' }}"><span class="material-symbols-outlined">history</span> Riwayat Stok</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit"><span class="material-symbols-outlined">logout</span> Logout</button>
                </form>
            </nav>

            <div class="role-box">
                @if(auth()->user()->canPermission('platform.manage'))
                    <strong>Mode Platform</strong>
                    <small>Akses lintas tenant, paket, dan status langganan SaaS.</small>
                @else
                    <strong>StokPintar</strong>
                    <small>Fokus ke operasional inti: stok, POS, laporan, dan riwayat toko.</small>
                @endif
            </div>
        </aside>

        <main class="app-main">
            <header class="app-header">
                <div class="app-header-left">
                    <button type="button" class="mobile-menu-btn" id="mobile-menu-btn">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    @if(auth()->user()->canPermission('stock.mutate'))
                        <form class="app-search" method="GET" action="{{ route('products.index') }}">
                            <span class="material-symbols-outlined">search</span>
                            <input name="search" value="{{ request('search') }}" type="search" placeholder="Cari produk atau SKU...">
                        </form>
                    @endif
                </div>

                <div class="app-header-right">
                    @if(auth()->user()->canPermission('products.manage'))
                        <a class="btn primary" href="{{ route('products.create') }}">Tambah Produk</a>
                    @endif
                    @if(auth()->user()->canPermission('pos.access'))
                        <a class="btn transaction" href="{{ route('pos.index') }}">Buka POS</a>
                    @endif
                    @if(auth()->user()->canPermission('products.manage') || auth()->user()->canPermission('pos.access'))
                        <div class="app-divider" aria-hidden="true"></div>
                    @endif
                    @if(auth()->user()->canPermission('subscription.manage'))
                        <a class="app-icon-button" href="{{ route('business-profile.edit') }}" aria-label="Pengaturan bisnis">
                            <span class="material-symbols-outlined">settings</span>
                        </a>
                    @elseif(auth()->user()->canPermission('users.manage'))
                        <a class="app-icon-button" href="{{ route('users.index') }}" aria-label="Pengaturan tim">
                            <span class="material-symbols-outlined">settings</span>
                        </a>
                    @endif
                    <a class="app-avatar" href="{{ route('account.edit') }}" aria-label="Akun Saya - {{ auth()->user()->name }}">
                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </a>
                </div>
            </header>

            <div class="app-content">
                @if(session('status'))
                    <div class="alert success">{{ session('status') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert error">
                        {{ $errors->first() }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
    @else
        <div class="guest-shell">
            @if(session('status'))
                <div class="alert success">{{ session('status') }}</div>
            @endif

            @if($errors->any())
                <div class="alert error">
                    {{ $errors->first() }}
                </div>
            @endif

            @yield('content')
        </div>
    @endauth
    <div class="toast-container" id="toast-container"></div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Mobile Sidebar Toggle
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobile-overlay');

            if (mobileBtn && sidebar && overlay) {
                const toggleMenu = () => {
                    sidebar.classList.toggle('open');
                    overlay.classList.toggle('open');
                };
                mobileBtn.addEventListener('click', toggleMenu);
                overlay.addEventListener('click', toggleMenu);
            }

            // Toast Notification System
            window.showToast = function(message, type = 'success') {
                const container = document.getElementById('toast-container');
                if (!container) return;
                
                const toast = document.createElement('div');
                toast.className = `toast ${type === 'error' ? 'toast-error' : ''}`;
                const icon = type === 'error' ? 'error' : 'check_circle';
                const color = type === 'error' ? 'var(--rose)' : 'var(--green)';
                
                toast.innerHTML = `<span class="material-symbols-outlined" style="color:${color}">${icon}</span> <span>${message}</span>`;
                container.appendChild(toast);
                
                // Animate in
                setTimeout(() => toast.classList.add('show'), 50);
                
                // Animate out and remove
                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 400);
                }, 4000);
            };

            // Trigger flash session toasts if any exist
            @if(session('status'))
                showToast("{{ session('status') }}", "success");
            @endif
            @if(session('error'))
                showToast("{{ session('error') }}", "error");
            @endif
        });
    </script>
</body>
</html>
