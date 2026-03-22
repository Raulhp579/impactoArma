<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impacto Arma - Gestionar Impactos</title>
    @vite('resources/css/app.css')
    <style>
        :root {
            --bg-dark: #0f1115;
            --surface: rgba(30, 33, 40, 0.75);
            --surface-solid: #181a20;
            --surface-hover: rgba(45, 50, 60, 0.85);
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(255, 255, 255, 0.1);
        }

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-main);
            height: 100vh;
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

        /* LEFT SIDEBAR */
        .sidebar-left {
            width: 72px;
            background-color: var(--surface-solid);
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

        /* MAIN CONTENT AREA */
        .main-content {
            flex: 1;
            position: relative;
            background-image: url('{{ asset("img/fondo.jpg") }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Contenedor Principal Glassmorphism */
        .container {
            background: var(--surface);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            width: 90%;
            max-width: 1200px;
            height: 85vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
        }

        /* Header del contenedor */
        .header {
            padding: 2rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 0, 0, 0.2);
        }

        .header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid var(--border-color);
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .btn-danger {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border-color: rgba(239, 68, 68, 0.3);
            padding: 0.4rem 0.8rem;
        }

        .btn-danger:hover {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
        }

        .btn-edit {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
            border-color: rgba(59, 130, 246, 0.3);
            padding: 0.4rem 0.8rem;
        }
        
        .btn-edit:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* Área de la tabla */
        .table-container {
            flex: 1;
            overflow-y: auto;
            padding: 0 2rem 2rem 2rem; /* Removido padding-top */
        }

        /* OVERRIDES DATATABLES DARK GLASSMORPHISM */
        div.dt-container {
            color: var(--text-main) !important;
            font-family: inherit;
        }

        div.dt-container .dt-search input, 
        div.dt-container .dt-length select {
            background: rgba(255, 255, 255, 0.06) !important;
            border: 1px solid var(--border-color) !important;
            color: white !important;
            border-radius: 6px !important;
            padding: 0.4rem 0.8rem !important;
            outline: none;
        }
        div.dt-container .dt-search input:focus, 
        div.dt-container .dt-length select:focus {
            border-color: var(--primary) !important;
        }

        div.dt-container .dt-info {
            color: var(--text-muted) !important;
        }

        /* ------- FUERZA BRUTA DATATABLES PAGINATION ------- */
        body div.dt-container .dt-paging *, 
        body div.dt-container .dt-paging button, 
        body div.dt-container .dt-paging a,
        div.dt-container .dt-paging .dt-paging-button {
            color: white !important;
            background: transparent !important;
            border: none !important;
            padding: 0.4rem 0.8rem !important;
            text-decoration: none !important;
        }

        body div.dt-container .dt-paging .dt-paging-button:hover:not(.disabled):not(.current),
        body div.dt-container .dt-paging button:hover:not(.disabled):not(.current) {
            background: rgba(255, 255, 255, 0.08) !important;
            color: white !important;
            border-radius: 6px !important;
        }

        body div.dt-container .dt-paging .dt-paging-button.current,
        body div.dt-container .dt-paging button.current {
            background: var(--primary) !important;
            color: white !important;
            border: none !important;
            border-radius: 6px !important;
        }

        body div.dt-container .dt-paging .dt-paging-button.disabled,
        body div.dt-container .dt-paging button.disabled,
        body div.dt-container .dt-paging button[disabled],
        div.dt-container .dt-paging .dt-paging-button.disabled:hover {
            color: rgba(255, 255, 255, 0.3) !important;
            background: transparent !important;
            cursor: default !important;
            opacity: 0.6 !important;
        }
        
        table.dataTable {
            width: 100% !important;
            border-collapse: collapse !important;
            text-align: left;
            margin-top: 0 !important; /* Quitar margen superior */
            margin-bottom: 1rem !important;
        }

        table.dataTable thead th, table.dataTable thead td {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color) !important;
            
            /* Cabecera Estática (Sticky) */
            position: sticky !important;
            top: 0 !important; /* Ajustado a 0 sin padding superior */
            background: rgba(30, 33, 40, 0.95) !important;
            backdrop-filter: blur(8px) !important;
            z-index: 10 !important;
        }

        table.dataTable tbody td {
            padding: 1.2rem 0.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
            font-size: 0.95rem;
        }

        table.dataTable tbody tr {
            background: transparent !important;
        }

        table.dataTable tbody tr:hover {
            background: rgba(255, 255, 255, 0.02) !important;
        }
        
        .td-actions {
            display: flex;
            gap: 0.5rem;
        }


        /* MODAL */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
            z-index: 500;
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
            max-width: 500px;
            padding: 2rem;
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
            font-size: 1.3rem;
            color: var(--text-main);
        }
        .btn-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.8rem;
            cursor: pointer;
            line-height: 1;
        }
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
        }
        .custom-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .custom-form.hidden-form {
            display: none;
        }
        .form-row {
            display: flex;
            gap: 1rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            flex: 1;
        }
        .form-group label {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-muted);
        }
        .form-group input, .form-group textarea, .form-group select {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-color);
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            font-family: inherit;
            outline: none;
        }
        .form-group select option {
            background: #1a1a1a;
            color: white;
        }
        .checkbox-group {
            flex-direction: row;
            align-items: center;
            gap: 0.5rem;
        }
        .checkbox-group input {
            width: 16px; height: 16px;
        }
        .btn-submit {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.85rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 0.5rem;
        }
        .btn-submit:hover {
            opacity: 0.9;
        }
        /* NUEVAS PESTAÑAS TABLA */
        .table-tabs {
            display: flex;
            background: rgba(0, 0, 0, 0.25);
            border-radius: 10px;
            padding: 4px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            gap: 4px;
        }
        .tab-item {
            padding: 0.5rem 0.9rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .tab-item:hover {
            color: var(--text-main);
            background: rgba(255, 255, 255, 0.03);
        }
        .tab-item.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body>
    <div class="app-container">
        
        <!-- SIDEBAR EXCLUSIVA GESTION IMPACTOS -->
        <aside class="sidebar-left">
            <div class="sidebar-top">
                <!-- L Logo -->
                <div class="logo-box">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo Impacto Arma">
                </div>
                
                <!-- 2. Mapa Actual -->
                <button class="icon-btn" data-tooltip="Mapa Actual" onclick="window.location.href='{{ url('/') }}'">
                    <svg viewBox="0 0 24 24"><path d="M9 3 5 6.993v13L9 17l6 3 4-3.993v-13L15 7l-6-4z"/><path d="M9 3v14M15 7v14"/></svg>
                </button>
                
                <!-- 3. Gestionar Áreas -->
                <button class="icon-btn" data-tooltip="Gestionar Áreas" onclick="window.location.href='{{ url('/gestionar-areas') }}'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                </button>
                
                <!-- 4. Gestionar Impactos -->
                <button class="icon-btn active" data-tooltip="Gestionar Impactos">
                    <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>
                </button>
            </div>

            <div class="sidebar-bottom">
                <!-- Ajustes -->
                <button class="icon-btn" data-tooltip="Ajustes">
                    <svg viewBox="0 0 24 24"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </aside>

        <!-- MAIN LAYOUT -->
        <main class="main-content">
            <div class="container">
                <div class="header">
                    <h1>
                        <svg viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>
                        Gestión de Impactos
                    </h1>
                    <div class="header-actions" style="display: flex; gap: 0.75rem; align-items: center;">
                        <select id="filter-eficacia" style="background: rgba(255, 255, 255, 0.06); border: 1px solid var(--border-color); color: white; border-radius: 8px; padding: 0.6rem 2.5rem 0.6rem 1rem; outline: none; cursor: pointer; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg fill=%22white%22 height=%2224%22 viewBox=%220 0 24 24%22 width=%2224%22 xmlns=%22http://www.w3.org/2000/svg%22><path d=%22M7 10l5 5 5-5z%22/></svg>'); background-repeat: no-repeat; background-position-x: 95%; background-position-y: center; font-family: inherit; font-size: 0.9rem;">
                            <option value="" style="background: #1a1a1a;">Filtro Efectividad: Todos</option>
                            <option value="Efectivo" style="background: #1a1a1a;">Efectivo</option>
                            <option value="Fallido" style="background: #1a1a1a;">Fallido</option>
                        </select>

                        <div class="table-tabs">
                            <div class="tab-item active" data-value="impactos">📊 Impactos</div>
                            <div class="tab-item" data-value="armas">🛡️ Armas</div>
                        </div>
                    </div>
                </div>

                <!-- SLIDER DE TABLAS -->
                <div class="table-slider-container" style="overflow: hidden; width: 100%; flex: 1; position: relative;">
                    <div id="table-slider" style="display: flex; transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); width: 200%; height: 100%;">
                        
                        <!-- TABLA IMPACTOS -->
                        <div class="table-container" style="width: 50%; height: 100%; overflow-y: auto; padding: 0 2rem 2rem 2rem;">
                            <table id="impactosTable" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Área</th>
                                        <th>Arma</th>
                                        <th>Coords X/Y</th>
                                        <th>Momento</th>
                                        <th>Efectivo</th>
                                        <th style="width: 120px">Eficacia %</th>
                                        <th style="width: 150px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($impactos as $impacto)
                                        <tr>
                                            <td class="text-muted">{{ $impacto->id }}</td>
                                            <td>{{ $impacto->area->nombre }}</td>
                                            <td>{{ $impacto->arma->nombre }}</td>
                                            <td>{{ $impacto->x_impacto }} / {{ $impacto->y_impacto }}</td>
                                            <td>{{ $impacto->momento_impacto }}</td>
                                            <td style="color: {{ $impacto->efectivo ? 'var(--primary)' : 'var(--danger)' }};">{{ $impacto->efectivo ? 'Efectivo' : 'Fallido' }}</td>
                                            <td>{{ $impacto->eficacia }}%</td>
                                            <td>
                                                <div class="td-actions">
                                                    <button class="btn btn-edit" 
                                                        data-id="{{ $impacto->id }}" 
                                                        data-x="{{ $impacto->x_impacto }}" 
                                                        data-y="{{ $impacto->y_impacto }}" 
                                                        data-momento="{{ $impacto->momento_impacto }}" 
                                                        data-efectivo="{{ $impacto->efectivo }}"
                                                        data-id-area="{{ $impacto->id_area }}"
                                                        data-id-objetivo="{{ $impacto->id_objetivo }}"
                                                        data-id-arma="{{ $impacto->id_arma }}">Editar</button>
                                                    <button class="btn btn-danger" data-id="{{ $impacto->id }}">Borrar</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- TABLA ARMAS -->
                        <div class="table-container" style="width: 50%; height: 100%; overflow-y: auto; padding: 0 2rem 2rem 2rem;">
                            <table id="armasTable" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Grupo</th>
                                        <th>Coords X/Y</th>
                                        <th style="width: 120px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Se llenará dinámicamente -->
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
            <!-- BOTÓN AÑADIR (Abajo Derecha) -->
            <button id="añadir" class="fab-add" data-tooltip="Añadir Nuevo Impacto">
                <svg viewBox="0 0 24 24"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            </button>
        </main>
    </div>

    @include('components.add-modal')

    <!-- MODAL EDITAR IMPACTO -->
    <div id="modalEditImpacto" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Impacto</h2>
                <button id="closeModalEditImpacto" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-edit-impacto" class="custom-form">
                    <input type="hidden" id="edit_impacto_id" name="id">
                    <div class="form-row" style="align-items: stretch; gap: 0.5rem;">
                        <div class="form-group" style="flex: 1;">
                            <label>Coordenada X</label>
                            <input type="number" step="any" id="edit_x_impacto" autocomplete="off" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Coordenada Y</label>
                            <input type="number" step="any" id="edit_y_impacto" autocomplete="off" required>
                        </div>
                        <div class="form-group" style="flex: 1.2;">
                            <label>País UTM</label>
                            <select id="utm_country_edit_impacto" class="form-control">
                                <option value="ES">España (30N)</option>
                                <option value="LV">Letonia (35V)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Momento del Impacto</label>
                        <input type="datetime-local" id="edit_momento_impacto" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Área</label>
                            <select id="edit_id_area" required style="background: rgba(255, 255, 255, 0.06); border: 1px solid var(--border-color); color: white; padding: 0.75rem; border-radius: 8px; outline: none;"></select>
                        </div>
                        <div class="form-group">
                            <label>Objetivo</label>
                            <select id="edit_id_objetivo" style="background: rgba(255, 255, 255, 0.06); border: 1px solid var(--border-color); color: white; padding: 0.75rem; border-radius: 8px; outline: none;">
                                <option value="">Selecciona un área...</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Arma</label>
                        <select id="edit_id_arma" required style="background: rgba(255, 255, 255, 0.06); border: 1px solid var(--border-color); color: white; padding: 0.75rem; border-radius: 8px; outline: none;"></select>
                    </div>

                    <button type="submit" id="btnGuardarEditImpacto" class="btn-submit">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL ELIMINAR IMPACTO -->
    <div id="modalDeleteImpacto" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <div class="modal-header" style="justify-content: center; border-bottom: none; margin-bottom: 0;">
                <h2 style="color: var(--danger); font-size: 1.5rem;">
                    <svg viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto 10px; display: block;" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    Eliminar Impacto
                </h2>
            </div>
            <div class="modal-body" style="padding: 1rem 0 1rem;">
                <p style="color: var(--text-muted); font-size: 1rem; margin-bottom: 1.5rem; line-height: 1.5;">
                    ¿Estás seguro de que deseas eliminar este impacto? Esta acción no se puede deshacer y los datos se perderán permanentemente.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <form id="form-delete-impacto" style="display: none;">
                        <input type="hidden" id="delete_impacto_id" name="id">
                    </form>
                    <button type="button" id="btnCancelDeleteImpacto" class="btn" style="flex: 1; justify-content: center;">Cancelar</button>
                    <button type="button" id="btnConfirmDeleteImpacto" class="btn btn-danger" style="flex: 1; justify-content: center;">Sí, Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL EDITAR ARMA -->
    <div id="modalEditArma" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Editar Armamento</h2>
                <button id="closeModalEditArma" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-edit-arma" class="custom-form">
                    <input type="hidden" id="edit_arma_id" name="id">
                    <div class="form-group">
                        <label>Nombre / Identificador</label>
                        <input type="text" id="edit_nombre_arma" autocomplete="off" required style="background: rgba(255, 255, 255, 0.06); border: 1px solid var(--border-color); color: white; padding: 0.75rem; border-radius: 8px; outline: none;">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Clase (Tipo)</label>
                            <input type="text" id="edit_tipo_arma" autocomplete="off" required style="background: rgba(255, 255, 255, 0.06); border: 1px solid var(--border-color); color: white; padding: 0.75rem; border-radius: 8px; outline: none;">
                        </div>
                        <div class="form-group">
                            <label>Batería / Grupo</label>
                            <select id="edit_id_grupo_arma" required style="background: rgba(255, 255, 255, 0.06); border: 1px solid var(--border-color); color: white; padding: 0.75rem; border-radius: 8px; outline: none;"></select>
                        </div>
                    </div>
                    <div class="form-row" style="align-items: stretch; gap: 0.5rem;">
                        <div class="form-group" style="flex: 1;">
                            <label>Coordenada X</label>
                            <input type="number" step="any" id="edit_x_arma" autocomplete="off" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Coordenada Y</label>
                            <input type="number" step="any" id="edit_y_arma" autocomplete="off" required>
                        </div>
                        <div class="form-group" style="flex: 1.2;">
                            <label>País UTM</label>
                            <select id="utm_country_edit_arma" class="form-control">
                                <option value="ES">España (30N)</option>
                                <option value="LV">Letonia (35V)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea id="edit_descripcion_arma" rows="3" style="background: rgba(255, 255, 255, 0.06); border: 1px solid var(--border-color); color: white; padding: 0.75rem; border-radius: 8px; outline: none; resize: none;"></textarea>
                    </div>

                    <button type="submit" id="btnGuardarEditArma" class="btn-submit">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL ELIMINAR ARMA -->
    <div id="modalDeleteArma" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <div class="modal-header" style="justify-content: center; border-bottom: none; margin-bottom: 0;">
                <h2 style="color: var(--danger); font-size: 1.5rem;">
                    <svg viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto 10px; display: block;" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    Eliminar Armamento
                </h2>
            </div>
            <div class="modal-body" style="padding: 1rem 0 1rem;">
                <p style="color: var(--text-muted); font-size: 1rem; margin-bottom: 1.5rem; line-height: 1.5;">
                    ¿Estás seguro de que deseas eliminar este armamento? Esta acción no se puede deshacer.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <form id="form-delete-arma" style="display: none;">
                        <input type="hidden" id="delete_arma_id" name="id">
                    </form>
                    <button type="button" id="btnCancelDeleteArma" class="btn" style="flex: 1; justify-content: center;">Cancelar</button>
                    <button type="button" id="btnConfirmDeleteArma" class="btn btn-danger" style="flex: 1; justify-content: center;">Sí, Eliminar</button>
                </div>
            </div>
        </div>
    </div>
    
    @vite('resources/js/gestionImpacto.js')
    @vite('resources/js/modal.js')
    @vite('resources/js/settings.js')
    @vite('resources/js/echo.js')
</body>
</html>
