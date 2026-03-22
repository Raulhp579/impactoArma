<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Impacto Arma - Gestor de Planeamiento</title>
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

        .btn-add-objetivo {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border-color: rgba(16, 185, 129, 0.25);
            padding: 0.4rem 0.8rem;
        }

        .btn-add-objetivo:hover {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }

        /* Pestañas de Tabla (Estilo gestionImpactos) */
        .table-tabs {
            display: flex;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 4px;
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

        /* Área de la tabla */
        .table-container {
            flex: 1;
            overflow: hidden; /* Cambiado para slider */
            display: flex;
            flex-direction: column;
        }

        /* Scrollbar oscuro para Chrome/Edge/Safari */
        .table-container::-webkit-scrollbar {
            width: 6px;
        }
        .table-container::-webkit-scrollbar-track {
            background: transparent;
        }
        .table-container::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.15);
            border-radius: 3px;
        }
        .table-container::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.25);
        }

        table {
            width: 100%;
            border-collapse: separate; /* Evita que sticky th transparente fondos */
            border-spacing: 0;
            text-align: left;
        }

        thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background: var(--surface-solid); /* #181a20 - variable definida para fondos opacos */
        }

        thead tr {
            background: inherit !important;
        }

        th {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1.25rem 0; /* Un poco más de aire */
            border-bottom: 1px solid var(--border-color);
            text-align: left;
        }

        td {
            padding: 1.2rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.95rem;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .td-actions {
            display: flex;
            gap: 0.5rem;
        }

        tr:last-child td {
            border-bottom: none;
        }
        
        .td-actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Scrollbar custom */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.2);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
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
            padding-right: 2.5rem;
            border-radius: 8px;
            font-family: inherit;
            outline: none;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.2s, background 0.2s;
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.85rem center;
            background-size: 1.2rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            background-color: rgba(255, 255, 255, 0.09);
        }
        .form-group select option {
            background: #1f2227;
            color: white;
            padding: 0.85rem;
            font-size: 0.95rem;
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
                <button class="icon-btn" data-tooltip="Mapa Actual" onclick="window.location.href='{{ url('/') }}'">
                    <svg viewBox="0 0 24 24"><path d="M9 3 5 6.993v13L9 17l6 3 4-3.993v-13L15 7l-6-4z"/><path d="M9 3v14M15 7v14"/></svg>
                </button>
                
                <!-- 3. Gestionar Áreas -->
                <button class="icon-btn active" data-tooltip="Gestor de planeamiento">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                </button>
                
                <!-- 4. Gestionar Impactos -->
                <button class="icon-btn" data-tooltip="Calificador de Tiro" onclick="window.location.href='{{ url('/gestion-impactos') }}'">
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

        <!-- MAIN LAYOUT -->
        <main class="main-content">
            <div class="container">
                <div class="header">
                    <h1>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                        Área de Planeamiento
                    </h1>
                    <div class="header-actions" style="display: flex; gap: 0.75rem; align-items: center;">
                        <button class="btn btn-primary" id="btnCrearArea">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Añadir Área
                        </button>
                        <div class="table-tabs">
                            <div class="tab-item active" data-value="areas">🗺️ Áreas</div>
                            <div class="tab-item" data-value="datosGeograficos">🌍 Datos Geográficos</div>
                        </div>
                    </div>
                </div>

                <div class="table-container" style="overflow: hidden; width: 100%; flex: 1; position: relative;">
                    <div class="tables-track" id="tables-track" style="display: flex; transition: transform 0.4s ease; height: 100%; width: 200%;">
                        
                        <!-- TABLA ÁREAS -->
                        <div class="table-wrapper" style="width: 50%; flex: 0 0 50%; height: 100%; overflow: auto; padding: 0 2rem 2rem 2rem; box-sizing: border-box;">
                            <table id="areasTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre del Área</th>
                                        <th>Nº Objetivos</th>
                                        <th style="width: 200px;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="areasTableBody">
                                    @foreach ($areas as $area)
                                        <tr>
                                            <td>{{ $area->id }}</td>
                                            <td>{{ $area->nombre }}</td>
                                            <td>{{ count($area->objetivos) }}</td>
                                            <td>
                                                <div class="td-actions">
                                                    <button class="btn btn-add-objetivo" data-id="{{ $area->id }}" data-nombre="{{ $area->nombre }}">Objetivos</button>
                                                    <button class="btn btn-edit" data-id="{{ $area->id }}">Editar</button>
                                                    <button class="btn btn-danger" data-id="{{ $area->id }}">Eliminar</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- FORMULARIO DATOS GEOGRÁFICOS -->
                        <div class="table-wrapper" style="width: 50%; flex: 0 0 50%; height: 100%; overflow: auto; padding: 0 2rem 2rem 2rem; box-sizing: border-box;">
                            <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; max-width: 600px; margin: 2rem auto;">
                                <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; font-size: 1.25rem;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M2 12h20M12 2a15.3 15.3 0 0 0 4 10 15.3 15.3 0 0 0-4 10M12 2a15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1 4 10"></path></svg>
                                    Configuración del Mapa
                                </h2>
                                
                                <form id="form-config-mapa" class="custom-form">
                                    <input type="hidden" id="config_id">
                                    
                                    <div class="form-group">
                                        <label for="config_sistema">Sistema de Coordenadas</label>
                                        <select id="config_sistema" class="form-control" required style="width: 100%;">
                                            <option value="UTM">UTM (Universal Transverse Mercator)</option>
                                            <option value="GEO">Geográficas (Latitud/Longitud)</option>
                                        </select>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group" style="flex: 1;">
                                            <label for="config_huso">Huso / Zona (UTM)</label>
                                            <input type="text" id="config_huso" placeholder="Ej. 30, 35..." class="form-control">
                                        </div>
                                        <div class="form-group" style="flex: 1;">
                                            <label for="config_hemisferio">Hemisferio</label>
                                            <select id="config_hemisferio" class="form-control">
                                                <option value="1">Norte</option>
                                                <option value="0">Sur</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-row" style="margin-top: 15px;">
                                        <div class="form-group" style="flex: 1;">
                                            <label for="config_prefijo_boca">Prefijo Nombre Boca (Arma)</label>
                                            <input type="text" id="config_prefijo_boca" placeholder="Ej. Boca, Cañón..." class="form-control" style="width: 100%;">
                                        </div>
                                        <div class="form-group" style="flex: 1;">
                                            <label for="config_numero_boca">Nº Inicial</label>
                                            <input type="number" id="config_numero_boca" placeholder="Ej. 1" class="form-control" style="width: 100%;">
                                        </div>
                                    </div>

                                    <button type="submit" class="btn-submit" style="margin-top: 1rem;">Guardar Configuración</button>
                                </form>
                            </div>
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

    <!-- MODAL CREAR/EDITAR ÁREA -->
    <div id="modalArea" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalAreaTitle">Añadir Nueva Área</h2>
                <button id="closeModalArea" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-area" class="custom-form">
                    <input type="hidden" id="area_id" name="id">
                    <div class="form-group">
                        <label for="area_nombre">Nombre del Área</label>
                        <input type="text" id="area_nombre" name="nombre" autocomplete="off" required>
                    </div>
                    
                    <div class="form-group" style="margin-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                        <label>Vértices del Polígono (<span id="countVertices">0</span>)</label>
                        <div class="form-row" style="margin-bottom: 0.5rem; align-items: stretch; gap: 0.5rem;">
                            <div class="form-group" style="margin-bottom: 0; flex: 1;">
                                <input type="number" step="any" id="vertice_x" placeholder="Coord X" autocomplete="off" style="font-size: 0.85rem;">
                            </div>
                            <div class="form-group" style="margin-bottom: 0; flex: 1;">
                                <input type="number" step="any" id="vertice_y" placeholder="Coord Y" autocomplete="off" style="font-size: 0.85rem;">
                            </div>
                            <button type="button" id="btnAnyadirVertice" class="btn" style="min-width: 44px; display: flex; align-items: center; justify-content: center; padding: 0; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; align-self: stretch;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                            </button>
                        </div>
                        <div class="vertices-list" id="listaVertices" style="max-height: 140px; overflow-y: auto; background: rgba(0,0,0,0.2); border-radius: 8px; padding: 0.5rem;">
                            <p id="noVerticesMsg" style="color: var(--text-muted); font-size: 0.85rem; text-align: center; margin: 0.5rem 0;">No hay vértices añadidos</p>
                        </div>
                    </div>
                    <button type="submit" id="btnGuardarArea" class="btn-submit">Guardar Área</button>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL ELIMINAR ÁREA -->
    <div id="modalDeleteArea" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <div class="modal-header" style="justify-content: center; border-bottom: none; margin-bottom: 0;">
                <h2 style="color: var(--danger); font-size: 1.5rem;">
                    <svg viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto 10px; display: block;" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                    Eliminar Área
                </h2>
            </div>
            <div class="modal-body" style="padding: 1rem 0 1rem;">
                <p style="color: var(--text-muted); font-size: 1rem; margin-bottom: 1.5rem; line-height: 1.5;">
                    ¿Estás seguro de que deseas eliminar esta área? Esta acción no se puede deshacer y eliminará todos los registros asociados.
                </p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <form id="form-delete-area" style="display: none;">
                        <input type="hidden" id="delete_area_id" name="id">
                    </form>
                    <button type="button" id="btnCancelDeleteArea" class="btn" style="flex: 1; justify-content: center;">Cancelar</button>
                    <button type="button" id="btnConfirmDeleteArea" class="btn btn-danger" style="flex: 1; justify-content: center;">Sí, Eliminar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- MODAL GESTIONAR OBJETIVOS -->
    <div id="modalAddObjetivo" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Objetivos: <span id="nombreAreaObjetivo" style="color:var(--primary);"></span></h2>
                <button id="closeModalObjetivo" class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-add-objetivo" class="custom-form">
                    <input type="hidden" id="obj_area_id">
                    <div class="form-group">
                        <label>Nombre del Objetivo</label>
                        <input type="text" id="new_obj_nombre" required placeholder="Ej. Búnker, Silo..." autocomplete="off">
                    </div>
                    <div class="form-row" style="margin-top: 0.5rem; align-items: stretch;">
                        <div class="form-group" style="flex: 1;">
                            <label>Coordenada X</label>
                            <input type="number" step="any" id="new_obj_x" required placeholder="0.00">
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Coordenada Y</label>
                            <input type="number" step="any" id="new_obj_y" required placeholder="0.00">
                        </div>
                    </div>
                    <button type="submit" class="btn-submit">Guardar Objetivo</button>
                </form>

                <div class="form-group" style="margin-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem;">
                    <label>Marcadores Existentes</label>
                    <div class="vertices-list" id="listaObjetivosExistentes" style="max-height: 180px; overflow-y: auto; background: rgba(0,0,0,0.2); border-radius: 8px; padding: 0.5rem; margin-top: 0.5rem;">
                        <!-- JS renders row items here -->
                    </div>
                </div>
            </div>
        </div>
    </div>



    @vite('resources/js/gestionarArea.js')

    @vite('resources/js/modal.js')
    @vite('resources/js/settings.js')
    @vite('resources/js/echo.js')
</body>
</html>
