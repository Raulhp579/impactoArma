document.addEventListener('DOMContentLoaded', () => {
    // 1. Inyectar Modal de Ajustes si no existe
    if (!document.getElementById('settingsModal')) {
        const modalHTML = `
            <div id="settingsModal" class="modal-overlay hidden" style="z-index: 9999;">
                <div class="modal-content" style="max-width: 400px;">
                    <div class="modal-header">
                        <h2>Ajustes</h2>
                        <button id="closeSettings" class="btn-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="custom-form">
                            <div class="form-group" style="padding: 1rem 0;">
                                <label for="mapThemeSelect" style="font-size: 1.05rem; color: var(--text-main); margin-bottom: 0.5rem; display: block;">Estilo de Mapa</label>
                                <select id="mapThemeSelect" style="width: 100%; padding: 0.85rem; border-radius: 8px; background: rgba(255,255,255,0.06); color: white; border: 1px solid var(--border-color); outline: none; cursor: pointer; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg fill=%22white%22 height=%2224%22 viewBox=%220 0 24 24%22 width=%2224%22 xmlns=%22http://www.w3.org/2000/svg%22><path d=%22M7 10l5 5 5-5z%22/></svg>'); background-repeat: no-repeat; background-position-x: 95%; background-position-y: center;">
                                    <option value="dark" style="background: #1a1a1a;">Carto Oscuro (Default)</option>
                                    <option value="light" style="background: #1a1a1a;">Carto Claro</option>
                                    <option value="satellite" style="background: #1a1a1a;">Satélite (Esri)</option>
                                    <option value="normal" style="background: #1a1a1a;">Normal (OpenStreetMap)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    // 2. Elementos del Modal
    const btnAjustesList = document.querySelectorAll('button[data-tooltip="Ajustes"]');
    const settingsModal = document.getElementById('settingsModal');
    const closeSettings = document.getElementById('closeSettings');
    const mapThemeSelect = document.getElementById('mapThemeSelect');

    // 3. Abrir/Cerrar Modal
    btnAjustesList.forEach(btnAjustes => {
        btnAjustes.addEventListener('click', () => {
            settingsModal.classList.remove('hidden');
        });
    });

    if (closeSettings) {
        closeSettings.addEventListener('click', () => {
            settingsModal.classList.add('hidden');
        });
    }

    // Cerrar al pulsar fuera
    settingsModal.addEventListener('click', (e) => {
        if (e.target === settingsModal) {
            settingsModal.classList.add('hidden');
        }
    });
    
    // 4. Lógica de Tema del Mapa
    const currentTheme = localStorage.getItem('mapTheme') || 'dark';
    
    if (mapThemeSelect) {
        // Inicializar Select
        mapThemeSelect.value = currentTheme;
        
        // Manejar Cambio
        mapThemeSelect.addEventListener('change', (e) => {
            const newTheme = e.target.value;
            localStorage.setItem('mapTheme', newTheme);
            
            // Emitir evento global para que 'mapa.js' lo dibuje de nuevo
            window.dispatchEvent(new CustomEvent('mapThemeChanged', { detail: newTheme }));
            
            // Aplicar clase al body para views que usan 'fondo.jpg'
            applyBodyTheme(newTheme);
        });
    }

    // 5. Aplicar en carga inicial
    applyBodyTheme(currentTheme);

    function applyBodyTheme(theme) {
        if (theme === 'light') {
            document.body.classList.add('light-map-theme');
        } else {
            document.body.classList.remove('light-map-theme');
        }
    }
});
