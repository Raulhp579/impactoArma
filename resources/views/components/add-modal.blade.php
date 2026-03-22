<!-- MODAL COMPARTIDO AÑADIR (Diseño Wizard) -->
<style>
    #addModal {
        --surface: #11151c;
        --surface2: #1a1f29;
        --border: rgba(255,255,255,0.08);
        --accent: #3b82f6;
        --accent-dim: rgba(59, 130, 246, 0.1);
        --success: #10b981;
        --text: #f8fafc;
        --muted: #64748b;
        --label: #94a3b8;
    }

    /* Modal Overlay */
    #addModal {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    #addModal:not(.hidden) { opacity: 1; pointer-events: all; display: flex !important; }

    /* Modal Main Box */
    #addModal .modal-content {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        width: 100%;
        max-width: 580px;
        position: relative;
        max-height: 85vh;
        overflow-y: auto;
        overflow-x: hidden;
        box-shadow: 0 40px 100px rgba(0,0,0,0.8);
        font-family: 'Syne', sans-serif; /* Para mantener diseño */
    }

    #addModal .modal-header {
        padding: 24px 24px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }
    #addModal .modal-header h2 { font-size: 18px; font-weight: 700; color: var(--text); }
    
    #addModal .close-btn {
        background: none; border: none; color: var(--muted);
        cursor: pointer; font-size: 28px; line-height: 1; transition: color 0.2s;
    }
    #addModal .close-btn:hover { color: var(--text); }

    /* Slider Engine */
    #addModal .slider-view { overflow: hidden; width: 100%; }
    
    #modal-slider {
        display: flex;
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        width: 100%;
        will-change: transform;
    }

    #addModal .step-container {
        min-width: 100%;
        padding: 28px 24px 32px;
    }

    /* Type Selection Cards */
    #addModal .selection-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 20px; }
    
    #addModal .type-card {
        background: var(--surface2);
        border: 2px solid var(--border);
        border-radius: 14px;
        padding: 28px 16px;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s ease;
        display: flex; flex-direction: column; align-items: center; gap: 16px;
    }
    #addModal .type-card .card-emoji { font-size: 40px; line-height: 1; }
    #addModal .type-card span { font-size: 14px; font-weight: 700; color: var(--label); letter-spacing: 0.02em; }
    
    #addModal .type-card:hover { border-color: rgba(255,255,255,0.15); background: rgba(255,255,255,0.02); }
    #addModal .type-card.active {
        border-color: var(--accent);
        background: var(--accent-dim);
        box-shadow: 0 0 15px var(--accent-dim);
    }
    #addModal .type-card.active span { color: var(--accent); }

    /* Form Styling */
    #addModal .form-group { margin-bottom: 20px; }
    #addModal .form-group label {
        display: block; font-size: 13px; font-weight: 600;
        color: var(--label); margin-bottom: 8px;
    }
    #addModal .form-control {
        width: 100%; background: var(--surface2);
        border: 1.5px solid var(--border);
        border-radius: 8px; padding: 12px;
        color: var(--text); font-family: inherit; font-size: 14px;
        outline: none; transition: 0.2s;
    }
    #addModal select.form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        padding-right: 36px;
        cursor: pointer;
    }
    #addModal select.form-control option {    
        background: #1f2227;
        color: white;
    }
    #addModal .form-control:focus { border-color: var(--accent); box-shadow: 0 0 0 4px var(--accent-dim); }

    #addModal .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    #addModal .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1.2fr; gap: 12px; }

    /* Navigation Buttons */
    #addModal .footer-actions {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: 16px;
    }
    #addModal .btn {
        padding: 12px 24px; border-radius: 8px; cursor: pointer;
        font-size: 14px; font-weight: 700; font-family: inherit;
        transition: 0.2s; border: none;
    }
    #addModal .btn-secondary { background: var(--surface2); color: var(--muted); border: 1.5px solid var(--border); }
    #addModal .btn-secondary:hover { color: var(--text); border-color: var(--muted); }
    
    #addModal .btn-primary { background: var(--accent); color: white; }
    #addModal .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }

    #addModal .btn-success { background: var(--success); color: #11151c; }
    #addModal .btn-success:hover { opacity: 0.9; transform: translateY(-1px); }

    #addModal .hidden { display: none !important; }
    .btn-clase {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text);
        border: 1px solid var(--border);
        padding: 0.75rem;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        flex: 1;
        min-width: 80px;
        text-align: center;
        font-size: 0.85rem;
    }
    .btn-clase:hover {
        background: rgba(255, 255, 255, 0.1);
    }
    .btn-clase.active {
        background: var(--accent) !important;
        border-color: var(--accent) !important;
        color: white !important;
    }
</style>

<!-- MODAL ESTRUCTURA -->
<div class="modal-overlay hidden" id="addModal">
    <div class="modal-content">
        
        <div class="modal-header">
            <h2>Registrar Nuevo Elemento</h2>
            <button class="close-btn" id="closeBtn">&times;</button>
        </div>

        <div class="slider-view">
            <div id="modal-slider">
                
                <!-- PASO 1: SELECCIÓN -->
                <div class="step-container">
                    <div class="selection-grid">
                        <div class="type-card" onclick="setTipo('arma')" id="card-arma">
                            <div class="card-emoji">🛡️</div>
                            <span>BOCA</span>
                        </div>
                        <div class="type-card active" onclick="setTipo('impacto')" id="card-impacto">
                            <div class="card-emoji">💥</div>
                            <span>IMPACTO</span>
                        </div>
                    </div>
                    <input type="hidden" id="val-tipo" value="impacto">
                    
                    <div class="footer-actions">
                        <div></div>
                        <button class="btn btn-primary" onclick="changeStep(1)">Siguiente →</button>
                    </div>
                </div>

                <!-- PASO 2: COORDENADAS -->
                <div class="step-container">


                    <div class="grid-2">
                        <div class="form-group">
                            <label id="lbl-x">Latitud (X)</label>
                            <input type="number" step="any" class="form-control" id="val-x" placeholder="00.0000">
                        </div>
                        <div class="form-group">
                            <label id="lbl-y">Longitud (Y)</label>
                            <input type="number" step="any" class="form-control" id="val-y" placeholder="00.0000">
                        </div>
                    </div>
                    <div class="footer-actions">
                        <button class="btn btn-secondary" onclick="changeStep(-1)">← Atrás</button>
                        <button class="btn btn-primary" onclick="changeStep(1)">Siguiente →</button>
                    </div>
                </div>

                <!-- PASO 3: ESPECÍFICOS -->
                <div class="step-container">
                    
                    <!-- CAMPOS IMPACTO -->
                        <div id="extra-impacto">
                            <div class="form-group">
                                <label>Fecha y Hora</label>
                                <input type="datetime-local" class="form-control" id="val-momento">
                            </div>
                            <div class="form-group" style="margin-top: 15px;">
                                <label>Objetivo</label>
                                <select class="form-control" id="val-objetivo">
                                    <option value="">Cargando objetivos...</option>
                                </select>
                            </div>
                        <div class="form-group" style="margin-top: 15px;">
                            <label>Boca de fuego</label>
                            <select class="form-control" id="val-arma">
                                <option value="">Cargando armas...</option>
                            </select>
                        </div>
                    </div>

                    <!-- CAMPOS ARMA -->
                    <div id="extra-arma" class="hidden">
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea class="form-control" id="val-descripcion" rows="2" placeholder="Breve descripción..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Clase (Tipo)</label>
                            <input type="hidden" id="val-clase">
                            <div class="clase-buttons" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
                                <button type="button" class="btn-clase" data-value="Ligera">Ligera</button>
                                <button type="button" class="btn-clase" data-value="Pesada">Pesada</button>
                                <button type="button" class="btn-clase" data-value="Mortero">Mortero</button>
                                <button type="button" class="btn-clase" data-value="Lanzacohete">Lanzacohete</button>
                            </div>
                        </div>
                    </div>

                    <div class="footer-actions">
                        <button class="btn btn-secondary" onclick="changeStep(-1)">← Atrás</button>
                        <button class="btn btn-success" onclick="finalizar()">Confirmar Registro ✓</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>