<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impacto Arma - Mapa</title>

    @vite('resources/css/app.css')
    <style>

        :root {
            --bg-dark: #0f1115;
            --surface: #181a20;
            --surface-glass: rgba(24, 26, 32, 0.85);
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.08);
        }

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            overflow: hidden;
        }

        /* Scrollbar oscuro global */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.15) transparent;
        }
        *::-webkit-scrollbar {
            width: 6px;
        }
        *::-webkit-scrollbar-track {
            background: transparent;
        }
        *::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.15);
            border-radius: 3px;
        }
        *::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }

        .app-container {
            display: flex;
            height: 100vh;
            width: 100vw;
        }

        /* LEFT SIDEBAR */
        .sidebar-left {
            width: 72px;
            background-color: var(--surface);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 0;
            z-index: 10;
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
        }

        .sidebar-top, .sidebar-bottom {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
            align-items: center;
        }

        .logo-box {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
        }
        .logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3)) brightness(1.5);
        }

        .icon-btn {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1px solid transparent;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .icon-btn:hover {
            color: var(--text-main);
            background-color: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .icon-btn.active {
            color: var(--primary);
            background-color: rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.2);
        }

        /* Tooltip */
        .icon-btn::after {
            content: attr(data-tooltip);
            position: absolute;
            left: calc(100% + 10px);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transform: translateX(-10px);
            transition: all 0.2s ease;
            pointer-events: none;
            z-index: 50;
        }

        .icon-btn:hover::after {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }

        /* Tooltip right-sided */
        .toolbar-right .icon-btn::after {
            left: auto;
            right: calc(100% + 10px);
            transform: translateX(10px);
        }
        .toolbar-right .icon-btn:hover::after {
            transform: translateX(0);
        }

        /* MAIN CONTENT (MAP AREA) */
        .main-content {
            flex: 1;
            position: relative;
            background-color: #0b1120;
        }

        /* Ocultar UI de Leaflet para usar nuestra propia interfaz premium */
        .leaflet-control-zoom {
            display: none !important;
        }
        .leaflet-bottom.leaflet-right {
            display: none !important; /* Oculta logo para limpieza visual del sketch */
        }

        /* RIGHT TOOLBAR */
        .toolbar-right {
            position: absolute;
            top: 2rem;
            right: 2rem;
            background: var(--surface-glass);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 0.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
            z-index: 10;
        }

        .toolbar-right .icon-btn {
            width: 44px;
            height: 44px;
            border-radius: 12px;
        }

        .toolbar-divider {
            height: 1px;
            background: var(--border-color);
            margin: 0.25rem 0.5rem;
        }


        /* SVG Defaults */
        svg {
            width: 24px;
            height: 24px;
            stroke-width: 2;
            stroke: currentColor;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* MODAL */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
            transition: opacity 0.3s ease;
        }
        .modal-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }
        .modal-content {
            background: var(--surface);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            width: 100%;
            max-width: 560px; /* Modal más ancho para que respiren las columnas */
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transform: translateY(0) scale(1);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .modal-overlay.hidden .modal-content {
            transform: translateY(20px) scale(0.95);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--text-main);
        }
        .btn-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 2rem;
            cursor: pointer;
            line-height: 1;
        }
        .btn-close:hover {
            color: white;
        }
        
        /* TABS */
        .modal-tabs {
            display: flex;
            background: rgba(0,0,0,0.3);
            border-radius: 8px;
            padding: 4px;
            margin-bottom: 1.5rem;
        }
        .tab-btn {
            flex: 1;
            padding: 0.5rem;
            border: none;
            background: transparent;
            color: var(--text-muted);
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .tab-btn.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        /* FORMS */
        .custom-form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .custom-form.hidden-form {
            display: none;
        }
        .form-row {
            display: flex;
            gap: 1rem;
        }
        .form-row .form-group {
            flex: 1;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .form-group label {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 0.25rem;
            display: block;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="datetime-local"],
        .form-group textarea {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-color);
            color: white;
            padding: 0.85rem;
            border-radius: 8px;
            font-family: inherit;
            outline: none;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.2s, background 0.2s;
        }
        .form-group select {
            background-color: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-color);
            color: white;
            padding: 0.85rem;
            padding-right: 2.5rem; /* Espacio extra para la flecha custom */
            border-radius: 8px;
            font-family: inherit;
            outline: none;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.2s, background 0.2s;
            cursor: pointer;
            
            /* Flecha customizada SVG y ocultación nativa */
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.85rem center;
            background-size: 1.2rem;

            /* Truncamiento inteligente con Puntos Suspensivos */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .form-group select:focus {
            border-color: var(--primary);
            background-color: rgba(255, 255, 255, 0.09);
        }
        .form-group select option {
            background: #1f2227; /* Oscuro sólido sin transparencia para que no haya bugs en windows */
            color: white;
            padding: 0.85rem;
            font-size: 0.95rem;
        }
        /* Para esconder las flechas nativas en type=number si es necesario */
        .form-group input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none; margin: 0;
        }
        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="datetime-local"]:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.09);
        }
        .checkbox-group {
            flex-direction: row;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
            margin: 0;
            padding: 0;
        }
        .checkbox-group label {
            margin-bottom: 0;
            color: var(--text-main);
            cursor: pointer;
        }
        .btn-submit:hover {
            background: var(--primary-hover);
        }

        /* PANELS OVERLAYS (Search & Filter) */
        .search-box {
            position: absolute;
            top: 2rem;
            right: 6.5rem; /* Ajustado al lado del toolbar */
            background: var(--surface-glass);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 0.5rem;
            display: flex;
            gap: 0.5rem;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
            z-index: 10;
            transition: all 0.3s ease;
        }

        .search-box.hidden, .filter-box.hidden {
            opacity: 0;
            transform: scale(0.95) translateX(10px);
            pointer-events: none;
        }

        .search-box input {
            background: transparent;
            border: none;
            color: white;
            padding: 0.5rem;
            outline: none;
            width: 200px;
            font-family: inherit;
        }

        .search-box button {
            background: var(--primary);
            border: none;
            border-radius: 8px;
            color: white;
            width: 36px; height: 36px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
        }

        .filter-box {
            position: absolute;
            top: 2rem; /* Subido al top */
            right: 6.5rem; /* Pegado a la barra de la derecha */
            background: var(--surface-glass);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.25rem; /* Más grande */
            width: 220px; /* Más ancho */
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
            z-index: 10;
            transition: all 0.3s ease;
        }

        .filter-header {
            font-size: 0.95rem; /* Fuente más grande */
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 0.75rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            padding-bottom: 0.5rem;
        }

        .filter-body {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            color: var(--text-main);
            font-size: 1rem; /* Más grande */
        }

        .filter-item input {
            accent-color: var(--primary);
            cursor: pointer;
            width: 16px; height: 16px; /* Caja más grande */
        }
        /* RIGHT SIDEBAR */
        .sidebar-right {
            width: 320px;
            background-color: var(--surface);
            border-left: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 10;
            box-shadow: -2px 0 10px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .sidebar-right-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .sidebar-right-header h3 {
            margin: 0;
            font-size: 1.25rem;
            color: var(--text-main);
        }

        .sidebar-search {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            transition: border-color 0.2s;
        }

        .sidebar-search:focus-within {
            border-color: var(--primary);
        }

        .sidebar-search input {
            background: transparent;
            border: none;
            color: white;
            flex: 1;
            outline: none;
            font-size: 0.9rem;
        }

        .search-btn-icon {
            background: none;
            border: none;
            color: var(--text-muted);
            padding: 0;
            display: flex;
            cursor: pointer;
        }

        .sidebar-right-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            position: relative;
        }

        .sidebar-view {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: opacity 0.2s ease;
        }

        .sidebar-view.hidden {
            display: none !important;
            opacity: 0;
        }

        /* Areas List */
        .areas-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .area-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            justify-content: flex-start; /* Ajustado para checkbox */
            align-items: center;
        }

        .area-card-checkbox {
            margin-right: 0.75rem;
            display: flex;
            align-items: center;
        }

        .area-toggle-chk {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .area-card:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .area-card-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .area-card-name {
            font-weight: 600;
            color: var(--text-main);
        }

        .area-card-stats {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .badge-count {
            background: rgba(59, 130, 246, 0.1);
            color: var(--primary);
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        /* Detail View */
        .btn-back {
            background: none;
            border: none;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            transition: color 0.2s;
        }

        .btn-back:hover {
            color: var(--text-main);
        }

        .btn-back svg {
            width: 18px;
            height: 18px;
        }

        .area-title {
            margin: 0 0 1rem 0;
            font-size: 1.5rem;
            color: var(--text-main);
        }

        .stats-summary {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            flex: 1;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-label {
            font-size: 0.7rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
        }

        .detail-section {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .detail-section h4 {
            margin: 0;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .item-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .detail-item {
            background: rgba(255, 255, 255, 0.01);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
        }

        .detail-item-main {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .detail-item-title {
            font-weight: 500;
            color: var(--text-main);
        }

        .detail-item-sub {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .detail-item-status {
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .status-efectivo {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status-fallido {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
    </style>
</head>
<body>

    <div class="app-container">
        
        <!-- SIDEBAR IZQUIERDA -->
        <aside class="sidebar-left">
            <div class="sidebar-top">
                <!-- L Logo -->
                <div class="logo-box">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo Impacto Arma">
                </div>
                
                <!-- 2. Mapa Actual -->
                <button class="icon-btn active" data-tooltip="Mapa Actual" onclick="window.location.href='{{ url('/') }}'">
                    <svg viewBox="0 0 24 24"><path d="M9 3 5 6.993v13L9 17l6 3 4-3.993v-13L15 7l-6-4z"/><path d="M9 3v14M15 7v14"/></svg>
                </button>
                
                <!-- 3. Gestionar Áreas -->
                <button class="icon-btn" data-tooltip="Gestionar Áreas" onclick="window.location.href='{{ url('/gestionar-areas') }}'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                </button>
                
                <!-- 4. Gestionar Impactos -->
                <button class="icon-btn" data-tooltip="Gestionar Impactos" onclick="window.location.href='{{ url('/gestion-impactos') }}'">
                    <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>
                </button>
            </div>

            <div class="sidebar-bottom">
                <!-- Ajustes (Abajo Izquierda) -->
                <button class="icon-btn" data-tooltip="Ajustes">
                    <svg viewBox="0 0 24 24"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </aside>

        <!-- MAIN MAP AREA -->
        <main class="main-content">
            <!-- Contenedor Real del Mapa Leaflet -->
            <div id="map" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0; z-index: 1;"></div>
            
            <!-- TOOLBAR DERECHA -->
            <div class="toolbar-right">
                <!-- Ampliar -->
                <button class="icon-btn" data-tooltip="Acercar (+)" id="btnZoomIn">
                    <svg viewBox="0 0 24 24"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                </button>
                
                <!-- Minimizar -->
                <button class="icon-btn" data-tooltip="Alejar (-)" id="btnZoomOut">
                    <svg viewBox="0 0 24 24"><path d="M5 12h14"/></svg>
                </button>

                <div class="toolbar-divider"></div>



                <!-- Búsqueda / Coordenada -->
                <button class="icon-btn" data-tooltip="Búsqueda / Coordenadas" id="btnSearch">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </button>

                <!-- Filtro de Capas -->
                <button class="icon-btn" data-tooltip="Filtrar Elementos" id="btnFilter">
                    <svg viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                </button>
            </div>

            <!-- PANEL DE BÚSQUEDA FLOTANTE -->
            <div id="search-container" class="search-box hidden">
                <input type="text" id="search-input" placeholder="Buscar localización..." autocomplete="off">
                <button id="btnSearchSubmit">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </button>
            </div>

            <!-- PANEL DE FILTROS FLOTANTE -->
            <div id="filter-panel" class="filter-box hidden">
                <div class="filter-header">Filtrar Capas</div>
                <div class="filter-body">
                    <label class="filter-item">
                        <input type="checkbox" id="chkImpactos" checked>
                        <span>Impactos</span>
                    </label>
                    <label class="filter-item">
                        <input type="checkbox" id="chkArmas" checked>
                        <span>Armas</span>
                    </label>
                </div>
            </div>

            <!-- BOTÓN AÑADIR (Abajo Derecha) -->
            <button id="añadir" class="fab-add" data-tooltip="Añadir Nuevo Impacto">
                <svg viewBox="0 0 24 24"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            </button>
            
        </main>

        <!-- SIDEBAR DERECHA: FILTRO ÁREAS -->
        <aside class="sidebar-right" id="sidebar-right">
            <div class="sidebar-right-header">
                <h3>Impacto por Áreas</h3>
                <div class="sidebar-search">
                    <input type="text" id="area-search" placeholder="Buscar área..." autocomplete="off">
                    <button class="search-btn-icon">
                        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </button>
                </div>
            </div>
            
            <div class="sidebar-right-content">
                <!-- VISTA 1: LISTA DE ÁREAS -->
                <div id="areas-list-view" class="sidebar-view">
                    <div class="areas-list" id="areas-items-container">
                        <!-- Items dinámicos -->
                    </div>
                </div>

                <!-- VISTA 2: DETALLE DEL ÁREA (Impactos / Armas) -->
                <div id="area-detail-view" class="sidebar-view hidden">
                    <button class="btn-back" id="btn-back-sidebar">
                        <svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                        <span>Volver</span>
                    </button>
                    
                    <h2 id="detail-area-name" class="area-title">Nombre del Área</h2>
                    
                    <div class="stats-summary">
                        <div class="stat-card">
                            <span class="stat-label">Impactos</span>
                            <span class="stat-value" id="stat-impacts">0</span>
                        </div>
                        <div class="stat-card">
                            <span class="stat-label">Eficacia Media</span>
                            <span class="stat-value" id="stat-eficacia">0%</span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Impactos Recientes</h4>
                        <div id="detail-impactos" class="item-list">
                            <!-- Items dinámicos -->
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4>Armas Utilizadas</h4>
                        <div id="detail-armas" class="item-list">
                            <!-- Items dinámicos -->
                        </div>
                    </div>
                </div>
            </div>
        </aside>

    </div>

    @include('components.add-modal')

    @vite('resources/js/mapa.js')
    @vite('resources/js/modal.js')
    @vite('resources/js/settings.js')
    @vite('resources/js/app.js')
</body>
</html>
