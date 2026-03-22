import proj4 from 'proj4';



document.addEventListener('DOMContentLoaded', () => {
    // --- CARGAR CONFIGURACIÓN MAPA ---
    let configMapaGlobal = null;
    async function cargarConfiguracionMapa() {
        try {
            const response = await fetch('/api/config_mapa');
            const data = await response.json();
            if (data && data.length > 0) {
                configMapaGlobal = data[0];
                if (window.updateCoordLabels) window.updateCoordLabels(); // <--- Actualizar labels
            }
        } catch (error) {
            console.error("Error cargando configuración mapa:", error);
        }
    }
    cargarConfiguracionMapa();

    // --- MANEJAR BOTONES DE CLASE ---
    document.querySelectorAll('.btn-clase').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.btn-clase').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const valClase = document.getElementById('val-clase');
            if (valClase) {
                valClase.value = btn.getAttribute('data-value');
            }
        });
    });

    // ============================================
    // DOM ELEMENTS - MODAL
    // ============================================
    const btnAnyadir = document.querySelector("#añadir");
    const modal = document.querySelector("#addModal");
    const btnCloseModal = document.querySelector("#closeBtn");

    // Abrir modal
    if (btnAnyadir && modal) {
        btnAnyadir.addEventListener("click", (e) => {
            e.preventDefault()
            currentStep = 0;
            updateModalView();
            modal.classList.remove("hidden");
        });
    }

    // Cerrar modal
    if (btnCloseModal && modal) {
        btnCloseModal.addEventListener("click", () => {
            modal.classList.add("hidden");
        });
    }

    // Cerrar modal al hacer click fuera
    if (modal) {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                modal.classList.add("hidden");
            }
        });
    }

    // ============================================
    // LÓGICA DEL WIZARD (PASO A PASO)
    // ============================================
    let currentStep = 0;

    function updateModalView() {
        const slider = document.getElementById('modal-slider');
        const tipo = document.getElementById('val-tipo').value;

        if (slider) {
            slider.style.transform = `translateX(-${currentStep * 100}%)`;
        }
        
        const secImpacto = document.getElementById('extra-impacto');
        const secArma = document.getElementById('extra-arma');

        if (secImpacto && secArma) {
            secImpacto.classList.toggle('hidden', tipo !== 'impacto');
            secArma.classList.toggle('hidden', tipo !== 'arma');
        }
    }

    // Hacemos las funciones globales para los onclicks inline
    window.changeStep = function(dir) {
        currentStep += dir;
        if (currentStep < 0) currentStep = 0;
        if (currentStep > 2) currentStep = 2;
        updateModalView();
    }

    window.setTipo = function(tipo) {
        const valTipo = document.getElementById('val-tipo');
        if (valTipo) valTipo.value = tipo;

        const cardImpacto = document.getElementById('card-impacto');
        const cardArma = document.getElementById('card-arma');

        if (cardImpacto && cardArma) {
            cardImpacto.classList.toggle('active', tipo === 'impacto');
            cardArma.classList.toggle('active', tipo === 'arma');
        }
        updateModalView();
    }

    window.updateCoordLabels = function() {
        const type = configMapaGlobal?.sistemaCoordenadas?.toLowerCase() || 'geo';
        const lblX = document.getElementById('lbl-x');
        const lblY = document.getElementById('lbl-y');
        const inpX = document.getElementById('val-x');
        const inpY = document.getElementById('val-y');

        console.log("updateCoordLabels called. Type:", type, "Labels found:", !!lblX, !!lblY);

        if (lblX && lblY) {
            if (type === 'utm') {
                lblX.textContent = "Easting / X";
                lblY.textContent = "Northing / Y";
                if (inpX) inpX.placeholder = "348000";
                if (inpY) inpY.placeholder = "4474000";
            } else {
                lblX.textContent = "Latitud (X)";
                lblY.textContent = "Longitud (Y)";
                if (inpX) inpX.placeholder = "00.0000";
                if (inpY) inpY.placeholder = "00.0000";
            }
        }
    }

    // ============================================
    // CARGA DE RED DE SELECTS (Áreas, Armas, Grupos)
    // ============================================
    async function loadSelectOptions(selector, endpoint) {
        try {
            const selectElement = document.querySelector(selector);
            if (!selectElement) return;

            selectElement.innerHTML = '<option value="">Cargando...</option>';
            
            const response = await fetch(endpoint);
            const data = await response.json();
            const items = data.data ? data.data : data;
            
            selectElement.innerHTML = '<option value="">Seleccione una opción</option>';
            items.forEach(item => {
                const text = item.nombre ? item.nombre : (item.tipo ? `${item.tipo} - ID: ${item.id}` : `ID: ${item.id}`);
                const option = document.createElement("option");
                option.value = item.id;
                option.textContent = text;
                if (item.id_area) {
                    option.setAttribute('data-id_area', item.id_area);
                }
                selectElement.appendChild(option);
            });
        } catch (error) {
            console.error(`Error cargando los datos de ${endpoint}:`, error);
        }
    }

    // Inicializar selects
    loadSelectOptions('#val-objetivo', '/api/objetivos_area');
    loadSelectOptions('#val-arma', '/api/armas');

    // ============================================
    // CONFIRMAR REGISTRO (Guardar en API)
    // ============================================
    window.finalizar = async function() {
        const tipo = document.getElementById('val-tipo').value;
        let x = document.getElementById('val-x').value;
        let y = document.getElementById('val-y').value;
        const coordType = configMapaGlobal?.sistemaCoordenadas?.toLowerCase() || 'geo';

        if (!x || !y) {
            alert("Las coordenadas son obligatorias.");
            return;
        }

        // Conversión UTM -> Geográficas si es necesario (por dropdown o auto-detección de valores altos)
        if (coordType === 'utm' || Math.abs(x) > 180 || Math.abs(y) > 180) {
            if (!configMapaGlobal) {
                alert("Esperando configuración del mapa de la base de datos...");
                return;
            }
            try {
                const easting = parseFloat(x);
                const northing = parseFloat(y);
                if (isNaN(easting) || isNaN(northing)) throw new Error("Coordenadas no numéricas");

                const huso = configMapaGlobal.huso || '30';
                const hemisferio = configMapaGlobal.hemisferio == 1 || configMapaGlobal.hemisferio === true ? '' : '+south ';
                const EPSG_STRING = `+proj=utm +zone=${huso} ${hemisferio}+ellps=WGS84 +datum=WGS84 +units=m +no_defs`;

                const [lon, lat] = proj4(EPSG_STRING, "EPSG:4326", [easting, northing]);
                
                x = lat; 
                y = lon; 
                console.log(`UTM Convertido (${huso}): [${easting}, ${northing}] -> [${lat}, ${lon}]`);
            } catch (e) {
                console.error("Error Proj4 UTM:", e);
                alert("Error en coordenadas UTM: " + e.message);
                return;
            }
        }

        console.log("Datos a enviar:", { x, y, coordType });
        let datos = {};
        let endpoint = '';

        if (tipo === 'impacto') {
            const momento = document.getElementById('val-momento').value;
            const objetivoSelect = document.getElementById('val-objetivo');
            const objetivo = objetivoSelect?.value;
            // Forma más robusta de obtener la opción seleccionada
            const optionSeleccionada = objetivoSelect ? objetivoSelect.querySelector('option:checked') : null;
            const area = optionSeleccionada?.getAttribute('data-id_area');
            const arma = document.getElementById('val-arma').value;

            console.log("FINALIZAR IMPACTO:", { momento, objetivo, area, arma });

            if (!momento || !area || !arma) {
                alert(`Campos obligatorios de Impacto incompletos:\n- Momento: ${momento ? '✓' : 'Falta'}\n- Objetivo: ${objetivo ? '✓' : 'Falta'}\n- Área (Deducida): ${area ? '✓' : 'No asociada al objetivo'}\n- Boca de fuego: ${arma ? '✓' : 'Falta'}`);
                return;
            }

            datos = {
                x_impacto: x,
                y_impacto: y,
                momento_impacto: momento,
                id_area: area,
                id_objetivo: objetivo || null, // <--- Nuevo
                id_arma: arma
            };
            endpoint = '/api/impactos';
        } else {
            const descripcion = document.getElementById('val-descripcion')?.value || ""; 
            const clase = document.getElementById('val-clase').value;

            if (!clase) {
                alert("La Clase de Arma es obligatoria.");
                return;
            }

            datos = {
                descripcion: descripcion,
                tipo: clase,
                cord_x: x,
                cord_y: y
            };
            endpoint = '/api/armas';
        }

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos)
            });

            if (response.ok) {
                if (modal) modal.classList.add('hidden');
                window.location.reload()
            } else {
                const errorData = await response.json();
                alert("Error: " + (errorData.message || response.statusText));
            }
        } catch (error) {
            console.error(error);
        }
    }
});