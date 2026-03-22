import proj4 from 'proj4';


document.addEventListener("DOMContentLoaded", () => {
    // --- VARIABLE GLOBAL CONFIGURACIÓN ---
    let configMapaGlobal = null;

    function formatCoords(x, y) {
        if (!x || !y) return "N/A";
        if (configMapaGlobal && configMapaGlobal.sistemaCoordenadas === "UTM") {
            const huso = configMapaGlobal.huso || "30";
            const hemisferio = configMapaGlobal.hemisferio === 1 || configMapaGlobal.hemisferio === true ? 'N' : 'S';
            
            const utmProj = `+proj=utm +zone=${huso} +${hemisferio === 'N' ? 'north' : 'south'} +ellps=WGS84 +datum=WGS84 +units=m +no_defs`;
            const geoProj = "+proj=longlat +datum=WGS84 +no_defs";

            try {
                // proj4 convierte [Lon, Lat] a [E, N]
                const [easting, northing] = proj4(geoProj, utmProj, [parseFloat(y), parseFloat(x)]);
                return `${Math.round(easting)} / ${Math.round(northing)} (UTM ${huso}${hemisferio})`;
            } catch (e) {
                console.error(e);
                return `${x} / ${y}`; 
            }
        }
        return `${parseFloat(x).toFixed(5)} / ${parseFloat(y).toFixed(5)}`; 
    }
    // ----------------------------------------
    // ELEMENTOS MODAL CREAR/EDITAR
    // ----------------------------------------
    const btnCrearArea = document.getElementById("btnCrearArea");
    const modalArea = document.getElementById("modalArea");
    const closeModalArea = document.getElementById("closeModalArea");
    const modalAreaTitle = document.getElementById("modalAreaTitle");

    let tempVertices = [];
    // ----------------------------------------
    // ELEMENTOS MODAL OBJETIVOS INDEPENDIENTE
    // ----------------------------------------
    const modalAddObjetivo = document.getElementById("modalAddObjetivo");
    const closeModalObjetivo = document.getElementById("closeModalObjetivo");
    const nombreAreaObjetivo = document.getElementById("nombreAreaObjetivo");
    const inputObjAreaId = document.getElementById("obj_area_id");
    
    const formAddObjetivo = document.getElementById("form-add-objetivo");
    const inputNewObjX = document.getElementById("new_obj_x");
    const inputNewObjY = document.getElementById("new_obj_y");
    const listaObjetivosExistentes = document.getElementById("listaObjetivosExistentes");

    function cargarObjetivosExistentes(idArea) {
        if (!listaObjetivosExistentes) return;
        listaObjetivosExistentes.innerHTML = `<p style="text-align:center; color:var(--text-muted); font-size:0.85rem;">Cargando...</p>`;
        
        fetch(`/api/areas/${idArea}`)
            .then(r => r.json())
            .then(data => {
                listaObjetivosExistentes.innerHTML = "";
                if (data && data.objetivos && data.objetivos.length > 0) {
                    data.objetivos.forEach(o => {
                        const div = document.createElement("div");
                        div.style.cssText = "display:flex; justify-content:space-between; align-items:center; padding: 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.85rem; background: rgba(255,255,255,0.02); margin-bottom: 3px; border-radius: 4px;";
                        div.innerHTML = `
                            <span><b style="color:var(--primary);">${o.nombre || 'Objetivo'}:</b> ${formatCoords(o.x_zona, o.y_zona)}</span>
                            <button type="button" class="btn-delete-obj" data-id="${o.id}" style="background:transparent; border:none; color:var(--danger); cursor:pointer; display:flex; align-items:center; gap:4px; font-size:0.8rem; padding:2px 4px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2M10 11v6M14 11v6"/></svg>
                                Borrar
                            </button>
                        `;
                        listaObjetivosExistentes.appendChild(div);
                    });
                } else {
                    listaObjetivosExistentes.innerHTML = `<p style="text-align:center; color:var(--text-muted); font-size:0.85rem; margin:0.5rem 0;">No hay objetivos</p>`;
                }
            })
            .catch(err => console.error("Error cargando objetivos:", err));
    }

    // Formulario Área
    const formArea = document.getElementById("form-area");
    const inputAreaId = document.getElementById("area_id");
    const inputAreaNombre = document.getElementById("area_nombre");

    // Vértices elements
    const inputVerticeX = document.getElementById("vertice_x");
    const inputVerticeY = document.getElementById("vertice_y");
    const btnAnyadirVertice = document.getElementById("btnAnyadirVertice");
    const listaVertices = document.getElementById("listaVertices");
    const countVertices = document.getElementById("countVertices");

    function renderVertices() {
        if (!listaVertices) return;
        listaVertices.innerHTML = "";
        if (countVertices) countVertices.textContent = tempVertices.length;

        if (tempVertices.length === 0) {
            listaVertices.innerHTML = '<p id="noVerticesMsg" style="color: var(--text-muted); font-size: 0.85rem; text-align: center; margin: 0.5rem 0;">No hay vértices añadidos</p>';
            return;
        }

        tempVertices.forEach((v, index) => {
            const div = document.createElement("div");
            div.style.cssText = "display:flex; justify-content:space-between; align-items:center; padding: 0.25rem 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.05); font-size: 0.85rem;";
            div.innerHTML = `
                <span><b>X:</b> ${v.x} &nbsp;&nbsp; <b>Y:</b> ${v.y}</span>
                <button type="button" class="btn-remove-vertice" data-index="${index}" style="background:transparent; border:none; color:var(--danger); cursor:pointer; font-size: 1.2rem; line-height: 1;">&times;</button>
            `;
            listaVertices.appendChild(div);
        });
    }

    if (btnAnyadirVertice) {
        btnAnyadirVertice.addEventListener("click", () => {
            let x = parseFloat(inputVerticeX.value);
            let y = parseFloat(inputVerticeY.value);
            
            if (isNaN(x) || isNaN(y)) {
                alert("Introduce coordenadas X e Y válidas para el vértice.");
                return;
            }

            // Auto-detectar UTM (si los valores son mayores a 180)
            if (Math.abs(x) > 180 || Math.abs(y) > 180) {
                if (!configMapaGlobal) {
                    alert("Esperando configuración del mapa de la base de datos...");
                    return;
                }
                try {
                    const huso = configMapaGlobal.huso || '30';
                    const hemisferio = configMapaGlobal.hemisferio == 1 || configMapaGlobal.hemisferio === true ? '' : '+south ';
                    const EPSG_STRING = `+proj=utm +zone=${huso} ${hemisferio}+ellps=WGS84 +datum=WGS84 +units=m +no_defs`;

                    const [lon, lat] = proj4(EPSG_STRING, "EPSG:4326", [x, y]);
                    x = lat;
                    y = lon;
                } catch(e) {
                    alert("Error convirtiendo UTM: " + e.message);
                    return;
                }
            }

            tempVertices.push({ x: x, y: y });
            inputVerticeX.value = "";
            inputVerticeY.value = "";
            inputVerticeX.focus();
            renderVertices();
        });
    }

    if (listaVertices) {
        listaVertices.addEventListener("click", (e) => {
            if (e.target.classList.contains("btn-remove-vertice")) {
                const index = e.target.getAttribute("data-index");
                tempVertices.splice(index, 1);
                renderVertices();
            }
        });
    }

    // ----------------------------------------
    // ELEMENTOS MODAL ELIMINAR
    // ----------------------------------------
    const modalDeleteArea = document.getElementById("modalDeleteArea");
    const btnCancelDeleteArea = document.getElementById("btnCancelDeleteArea");
    const btnConfirmDeleteArea = document.getElementById("btnConfirmDeleteArea");
    const inputDeleteAreaId = document.getElementById("delete_area_id");

    // ----------------------------------------
    // MOSTRAR/OCULTAR MODAL CREAR/EDITAR
    // ----------------------------------------

    const btnFabNuevaArea = document.getElementById("btnFabNuevaArea");

    function abrirModalNuevaArea() {
        modalAreaTitle.textContent = "Añadir Nueva Área";
        inputAreaId.value = "";
        inputAreaNombre.value = "";
        tempVertices = [];
        renderVertices();
        modalArea.classList.remove("hidden");
    }

    if (btnCrearArea) {
        btnCrearArea.addEventListener("click", abrirModalNuevaArea);
    }

    if (btnFabNuevaArea) {
        btnFabNuevaArea.addEventListener("click", abrirModalNuevaArea);
    }

    if (closeModalArea) {
        closeModalArea.addEventListener("click", () => {
            modalArea.classList.add("hidden");
        });
    }

    if (modalArea) {
        modalArea.addEventListener("click", (e) => {
            if (e.target === modalArea) modalArea.classList.add("hidden");
        });
    }

    // Botones Editar en la Tabla (Event Delegation para mayor robustez)
    document.querySelector("#areasTableBody")?.addEventListener("click", (e) => {
        const btnAddObj = e.target.closest(".btn-add-objetivo");
        if (btnAddObj) {
            const id = btnAddObj.getAttribute("data-id");
            const nombre = btnAddObj.getAttribute("data-nombre");
            nombreAreaObjetivo.textContent = nombre;
            inputObjAreaId.value = id;
            if (typeof hasChanges !== 'undefined') hasChanges = false;
            cargarObjetivosExistentes(id);
            modalAddObjetivo.classList.remove("hidden");
            return; // Prevenir click en btnEdit
        }

        const btnEdit = e.target.closest(".btn-edit");
        if (btnEdit) {
            const row = btnEdit.closest("tr");
            const id = row.cells[0].textContent.trim();
            const nombre = row.cells[1].textContent.trim();

            modalAreaTitle.textContent = "Editar Área";
            inputAreaId.value = id;
            inputAreaNombre.value = nombre;

            tempVertices = [];
            renderVertices();

            fetch(`/api/areas/${id}`)
                .then(r => r.json())
                .then(data => {
                    if(data && data.vertices) {
                        tempVertices = data.vertices.map(v => ({x: v.x, y: v.y}));
                        renderVertices();
                    }
                })
                .catch(err => console.error("Error fetching data:", err));

            modalArea.classList.remove("hidden");
        }

        const btnDelete = e.target.closest(".btn-danger");
        if (btnDelete) {
            const id = btnDelete.getAttribute("data-id");
            inputDeleteAreaId.value = id;
            modalDeleteArea.classList.remove("hidden");
        }
    });

    // ----------------------------------------
    // MOSTRAR/OCULTAR MODAL ELIMINAR (CERRAR)
    // ----------------------------------------
    if (btnCancelDeleteArea) {
        btnCancelDeleteArea.addEventListener("click", () => {
            modalDeleteArea.classList.add("hidden");
        });
    }

    if (modalDeleteArea) {
        modalDeleteArea.addEventListener("click", (e) => {
            if (e.target === modalDeleteArea)
                modalDeleteArea.classList.add("hidden");
        });
    }

    // ----------------------------------------
    // GUARDAR (CREAR / EDITAR)
    // ----------------------------------------
    if (formArea) {
        formArea.addEventListener("submit", async (e) => {
            e.preventDefault();

            const id = inputAreaId.value;
            const datos = {
                nombre: inputAreaNombre.value,
                vertices: tempVertices
            };

            const url = id ? `/api/areas/${id}` : "/api/areas";
            const method = id ? "PUT" : "POST";

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify(datos),
                });

                if (response.ok) {
                    modalArea.classList.add("hidden");
                    window.location.reload();
                } else {
                    const errorData = await response.json();
                    alert("Error al guardar: " + (errorData.message || response.statusText));
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Error de conexión");
            }
        });
    }

    // ----------------------------------------
    // ELIMINAR ÁREA
    // ----------------------------------------
    if (btnConfirmDeleteArea) {
        btnConfirmDeleteArea.addEventListener("click", async () => {
            const id = inputDeleteAreaId.value;
            if (!id) return;

            try {
                const response = await fetch(`/api/areas/${id}`, {
                    method: "DELETE",
                    headers: {
                        "Accept": "application/json"
                    }
                });

                if (response.ok) {
                    modalDeleteArea.classList.add("hidden");
                    window.location.reload();
                } else {
                    const errorData = await response.json();
                    alert("Error al eliminar: " + (errorData.message || response.statusText));
                }
            } catch (error) {
                console.error("Error:", error);
                alert("Error de conexión");
            }
        });
    }

    let hasChanges = false; // NUEVO

    // Cierre modal objetivos
    if (closeModalObjetivo) {
        closeModalObjetivo.addEventListener("click", () => {
            modalAddObjetivo.classList.add("hidden");
            if (hasChanges) window.location.reload();
        });
    }

    if (modalAddObjetivo) {
        modalAddObjetivo.addEventListener("click", (e) => {
            if (e.target === modalAddObjetivo) {
                modalAddObjetivo.classList.add("hidden");
                if (hasChanges) window.location.reload();
            }
        });
    }

    // Submit Añadir Objetivo
    if (formAddObjetivo) {
        formAddObjetivo.addEventListener("submit", async (e) => {
            e.preventDefault();
            const idArea = inputObjAreaId.value;
            const inputNombre = document.getElementById("new_obj_nombre");
            
            let xObj = parseFloat(inputNewObjX.value);
            let yObj = parseFloat(inputNewObjY.value);

            if (Math.abs(xObj) > 180 || Math.abs(yObj) > 180) {
                if (!configMapaGlobal) {
                    alert("Esperando configuración del mapa de la base de datos...");
                    return;
                }
                try {
                    const huso = configMapaGlobal.huso || '30';
                    const hemisferio = configMapaGlobal.hemisferio == 1 || configMapaGlobal.hemisferio === true ? '' : '+south ';
                    const EPSG_STRING = `+proj=utm +zone=${huso} ${hemisferio}+ellps=WGS84 +datum=WGS84 +units=m +no_defs`;

                    const [lon, lat] = proj4(EPSG_STRING, "EPSG:4326", [xObj, yObj]);
                    xObj = lat;
                    yObj = lon;
                } catch(e) {
                    alert("Error convirtiendo UTM: " + e.message);
                    return;
                }
            }

            const datos = {
                id_area: idArea,
                nombre: inputNombre ? inputNombre.value : "",
                x_zona: xObj,
                y_zona: yObj
            };

            try {
                const res = await fetch("/api/objetivos_area", {
                    method: "POST",
                    headers: { 
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify(datos)
                });
                if (res.ok) {
                    if (inputNombre) inputNombre.value = "";
                    inputNewObjX.value = "";
                    inputNewObjY.value = "";
                    hasChanges = true;
                    cargarObjetivosExistentes(idArea);
                } else { alert("Error al guardar objetivo"); }
            } catch (error) { console.error(error); }
        });
    }

    // Delete objetivo
    if (listaObjetivosExistentes) {
        listaObjetivosExistentes.addEventListener("click", async (e) => {
            if (e.target.classList.contains("btn-delete-obj")) {
                const idObj = e.target.getAttribute("data-id");
                const idArea = inputObjAreaId.value;
                if (!confirm("¿Eliminar objetivo?")) return;

                try {
                    const res = await fetch(`/api/objetivos_area/${idObj}`, { method: "DELETE" });
                    if (res.ok) { 
                        hasChanges = true;
                        cargarObjetivosExistentes(idArea); 
                    }
                } catch (error) { console.error(error); }
            }
        });
    }

    // ----------------------------------------
    // CONFIGURACIÓN DATOS GEOGRÁFICOS
    // ----------------------------------------
    const tablesTrack = document.getElementById('tables-track');
    const tabItems = document.querySelectorAll('.tab-item');
    const btnCrearAreaHeader = document.getElementById('btnCrearArea');

    if (tabItems && tablesTrack) {
        tabItems.forEach(tab => {
            tab.addEventListener('click', () => {
                const value = tab.getAttribute('data-value');
                
                tabItems.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                if (tablesTrack) {
                    tablesTrack.style.transform = value === 'datosGeograficos' ? 'translateX(-50%)' : 'translateX(0%)';
                }

                // Ocultar botón "Añadir Área" si estamos en Datos Geográficos
                if (btnCrearAreaHeader) {
                    btnCrearAreaHeader.style.display = value === 'datosGeograficos' ? 'none' : 'flex';
                }
            });
        });
    }

    // --- CARGAR CONFIGURACIÓN ---
    const formConfig = document.getElementById('form-config-mapa');
    const configIdInput = document.getElementById('config_id');
    const configSistema = document.getElementById('config_sistema');
    const configHuso = document.getElementById('config_huso');
    const configHemisferio = document.getElementById('config_hemisferio');
    const configPrefijoBoca = document.getElementById('config_prefijo_boca');
    const configNumeroBoca = document.getElementById('config_numero_boca');

    async function cargarConfiguracion() {
        try {
            const response = await fetch('/api/config_mapa');
            const data = await response.json();
            
            // Si hay datos (data es un array), tomar el primero
            if (data && data.length > 0) {
                const config = data[0];
                configMapaGlobal = config; // <--- Asignar para uso general
                if (configIdInput) configIdInput.value = config.id;
                if (configSistema) configSistema.value = config.sistemaCoordenadas || "UTM";
                if (configHuso) configHuso.value = config.huso || "";
                if (configHemisferio) configHemisferio.value = config.hemisferio ? "1" : "0";
                if (configPrefijoBoca) configPrefijoBoca.value = config.prefijo_nombre_boca || "";
                if (configNumeroBoca) configNumeroBoca.value = config.numero_boca_inicial || "";
            }
        } catch (error) {
            console.error("Error cargando configuración:", error);
        }
    }

    if (formConfig) {
        cargarConfiguracion();

        formConfig.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const id = configIdInput ? configIdInput.value : null;
            const datos = {
                sistemaCoordenadas: configSistema ? configSistema.value : "UTM",
                huso: configHuso ? configHuso.value : "",
                hemisferio: configHemisferio ? (configHemisferio.value === "1") : true,
                prefijo_nombre_boca: configPrefijoBoca ? configPrefijoBoca.value : "",
                numero_boca_inicial: configNumeroBoca && configNumeroBoca.value ? parseInt(configNumeroBoca.value) : null
            };

            const url = id ? `/api/config_mapa/${id}` : '/api/config_mapa';
            const method = id ? 'PUT' : 'POST';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(datos)
                });

                if (response.ok) {
                    alert("Configuración guardada correctamente.");
                    if (!id) {
                        cargarConfiguracion(); // Recargar para obtener el ID
                    }
                } else {
                    const err = await response.json();
                    alert("Error al guardar: " + (err.message || response.statusText));
                }
            } catch (error) {
                console.error("Error guardando configuración:", error);
                alert("Error de conexión");
            }
        });
    }
});
