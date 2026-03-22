import proj4 from 'proj4';

const UTM_ZONES = {
    'ES': "+proj=utm +zone=30 +ellps=GRS80 +towgs84=0,0,0,0,0,0,0 +units=m +no_defs",
    'LV': "+proj=utm +zone=35 +ellps=WGS84 +datum=WGS84 +units=m +no_defs"
};

document.addEventListener('DOMContentLoaded', () => {
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
        const type = document.getElementById('val-coord-type')?.value || 'geo';
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
                // Priorizar nombre, luego tipo, luego ID
                const text = item.nombre ? item.nombre : (item.tipo ? `${item.tipo} - ID: ${item.id}` : `ID: ${item.id}`);
                const option = document.createElement("option");
                option.value = item.id;
                option.textContent = text;
                selectElement.appendChild(option);
            });
        } catch (error) {
            console.error(`Error cargando los datos de ${endpoint}:`, error);
        }
    }

    // Inicializar selects
    loadSelectOptions('#val-area', '/api/areas');
    loadSelectOptions('#val-arma', '/api/armas');
    loadSelectOptions('#val-grupo', '/api/grupos');

    // Cargar objetivos dinámicamente según área (Añadir)
    const valArea = document.getElementById('val-area');
    if (valArea) {
        valArea.addEventListener('change', async (e) => {
            const areaId = e.target.value;
            const valObjetivo = document.getElementById('val-objetivo');
            if (!valObjetivo) return;

            if (!areaId) {
                valObjetivo.innerHTML = '<option value="">Selecciona un área...</option>';
                return;
            }

            valObjetivo.innerHTML = '<option value="">Cargando objetivos...</option>';
            try {
                const response = await fetch(`/api/areas/${areaId}`);
                const data = await response.json();
                const objetivos = data.objetivos || [];

                valObjetivo.innerHTML = '<option value="">Seleccione un objetivo</option>';
                objetivos.forEach(obj => {
                    const option = document.createElement("option");
                    option.value = obj.id;
                    option.textContent = obj.nombre || `Objetivo ID: ${obj.id}`;
                    valObjetivo.appendChild(option);
                });
            } catch (error) { console.error(error); }
        });
    }

    // ============================================
    // CONFIRMAR REGISTRO (Guardar en API)
    // ============================================
    window.finalizar = async function() {
        const tipo = document.getElementById('val-tipo').value;
        let x = document.getElementById('val-x').value;
        let y = document.getElementById('val-y').value;
        const coordType = document.getElementById('val-coord-type')?.value || 'geo';

        if (!x || !y) {
            alert("Las coordenadas son obligatorias.");
            return;
        }

        // Conversión UTM -> Geográficas si es necesario (por dropdown o auto-detección de valores altos)
        if (coordType === 'utm' || Math.abs(x) > 180 || Math.abs(y) > 180) {
            try {
                const easting = parseFloat(x);
                const northing = parseFloat(y);
                if (isNaN(easting) || isNaN(northing)) throw new Error("Coordenadas no numéricas");

                // Obtener el EPSG string basado en el país seleccionado
                const countryCode = document.getElementById('utm_country_modal')?.value || 'ES';
                const EPSG_STRING = UTM_ZONES[countryCode] || UTM_ZONES['ES'];

                // Proj4 espera [X, Y] (Easting, Northing)
                // Devuelve [Lon, Lat]
                const [lon, lat] = proj4(EPSG_STRING, "EPSG:4326", [easting, northing]);
                
                x = lat; // Latitud (X en backend)
                y = lon; // Longitud (Y en backend)
                console.log(`UTM Convertido: [${easting}, ${northing}] -> [${lat}, ${lon}]`);
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
            const area = document.getElementById('val-area').value;
            const objetivo = document.getElementById('val-objetivo')?.value; // <--- Nuevo
            const arma = document.getElementById('val-arma').value;

            if (!momento || !area || !arma) {
                alert("Campos obligatorios de Impacto incompletos.");
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
            const nombre = document.getElementById('val-nombre').value;
            const descripcion = document.getElementById('val-descripcion')?.value || ""; // <--- Nuevo
            const clase = document.getElementById('val-clase').value;
            const grupo = document.getElementById('val-grupo').value;

            if (!nombre || !clase) {
                alert("Nombre y Clase de Arma son obligatorios.");
                return;
            }

            datos = {
                nombre: nombre,
                descripcion: descripcion, // <--- Nuevo
                tipo: clase,
                cord_x: x,
                cord_y: y,
                id_grupo: grupo
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