////////VISTA HOME/////////////
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import "@fontsource/inter"; // Fuente descargada localmente

// CORRECCIÓN PARA ICONOS DE LEAFLET EN VITE (Marcadores que no se ven)
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL(
        "leaflet/dist/images/marker-icon-2x.png",
        import.meta.url,
    ).href,
    iconUrl: new URL("leaflet/dist/images/marker-icon.png", import.meta.url)
        .href,
    shadowUrl: new URL("leaflet/dist/images/marker-shadow.png", import.meta.url)
        .href,
});

document.addEventListener("DOMContentLoaded", () => {
    // Inicializar el mapa de Leaflet en el div con id "map"
    const map = L.map("map", {
        zoomControl: false,
        preferCanvas: true
    }).setView([40.4168, -3.7038], 6);


    // Corrige error de Leaflet donde el contenedor SVG se crea con tamaño 0x0 u offset incorrecto
    setTimeout(() => {
        map.invalidateSize();
    }, 300);

    // Añadir una capa base (Map tiles) basada en el tema guardado
    const currentTheme = localStorage.getItem("mapTheme") || "dark";

    const getTileUrl = (theme) => {
        switch (theme) {
            case "light":
                return "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png";
            case "satellite":
                return "https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}";
            case "normal":
                return "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png";
            case "dark":
            default:
                return "https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png";
        }
    };

    const getTileOptions = (theme) => {
        let attribution =
            '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
        if (theme === "satellite") {
            attribution =
                "Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community";
        } else if (theme === "dark" || theme === "light") {
            attribution +=
                ' &copy; <a href="https://carto.com/attributions">CARTO</a>';
        }

        return {
            attribution: attribution,
            maxZoom: theme === "satellite" ? 18 : 19,
        };
    };

    let currentTileLayer = L.tileLayer(
        getTileUrl(currentTheme),
        getTileOptions(currentTheme),
    ).addTo(map);

    // Escuchar cambios de tema desde los ajustes globales
    window.addEventListener("mapThemeChanged", (e) => {
        const newTheme = e.detail;
        map.removeLayer(currentTileLayer);
        currentTileLayer = L.tileLayer(
            getTileUrl(newTheme),
            getTileOptions(newTheme),
        ).addTo(map);
    });
    // ============================================
    // Capas de Marcadores (Layer Groups)
    // ============================================
    const impactoLayer = L.layerGroup().addTo(map);
    const armaLayer = L.layerGroup().addTo(map);
    const areaLayer = L.layerGroup().addTo(map);

    async function cargarMarcadores() {
        try {
            // 1. Cargar Áreas y sus Vértices
            const resAreas = await fetch("/api/areas");
            const dataAreas = await resAreas.json();
            const areas = dataAreas.data ? dataAreas.data : dataAreas;

            areas.forEach((area) => {
                area.objectivosGroup = L.layerGroup(); // Para guardar objetivos
                
                if (
                    area.vertices &&
                    Array.isArray(area.vertices) &&
                    area.vertices.length >= 3
                ) {
                    const coords = area.vertices
                        .map((v) => [parseFloat(v.x), parseFloat(v.y)])
                        .filter(
                            (coord) => !isNaN(coord[0]) && !isNaN(coord[1]),
                        );

                    if (coords.length >= 3) {
                        const polygon = L.polygon(coords, {
                            color: "#059669",
                            weight: 3,
                            fillColor: "#10b981",
                            fillOpacity: 0.4,
                        });
                        polygon.bindPopup(
                            `<b>Área: ${area.nombre}</b><br>Vértices: ${coords.length}`,
                        );
                        area.polygon = polygon; // Guardar referencia
                    }
                }

                if (area.objetivos && Array.isArray(area.objetivos)) {
                    area.objetivos.forEach((obj, idx) => {
                        const latObj = parseFloat(obj.x_zona);
                        const lonObj = parseFloat(obj.y_zona);

                        if (!isNaN(latObj) && !isNaN(lonObj)) {
                            const targetMarker = L.circleMarker([latObj, lonObj], {
                                radius: 8,
                                color: "#047857",
                                fillColor: "#10b981",
                                weight: 3,
                                fillOpacity: 1,
                            });
                            targetMarker.bindPopup(
                                `<b>Objetivo ${idx + 1}: ${area.nombre}</b>`
                            );
                            area.objectivosGroup.addLayer(targetMarker); // Añadir al grupo
                        }
                    });
                }
            });

            // 2. Cargar Impactos (AHORA VISIBLES)
            const resImpactos = await fetch("/api/impactos-con-detalles");
            const dataImpactos = await resImpactos.json();
            const impactos = dataImpactos.data
                ? dataImpactos.data
                : dataImpactos;

            if (Array.isArray(impactos)) {
                impactos.forEach((imp) => {
                    if (
                        imp.x_impacto !== null &&
                        imp.y_impacto !== null &&
                        imp.x_impacto !== undefined
                    ) {
                        const latImp = parseFloat(imp.x_impacto);
                        const lonImp = parseFloat(imp.y_impacto);

                        if (!isNaN(latImp) && !isNaN(lonImp)) {
                            const esEfectivo =
                                imp.efectivo === 1 || imp.efectivo === true;

                            const circle = L.circleMarker([latImp, lonImp], {
                                color: esEfectivo ? "#1e40af" : "#991b1b",
                                weight: 2,
                                fillColor: esEfectivo ? "#3b82f6" : "#ef4444",
                                fillOpacity: 0.8,
                                radius: 6,
                            });

                            circle.bindPopup(`
                                <div style="font-family: 'Inter', sans-serif;">
                                    <b style="color: ${esEfectivo ? "#3b82f6" : "#ef4444"};">Impacto ${esEfectivo ? "Efectivo" : "Fallido"}</b><br>
                                    Eficacia: <b>${imp.eficacia ?? 0}%</b>
                                </div>
                            `);

                            const area = areas.find(a => a.id === imp.id_area);
                            if (area) {
                                if (!area.impactsGroup) area.impactsGroup = L.layerGroup();
                                area.impactsGroup.addLayer(circle);
                            }
                        }
                    }
                });
            }

            // 3. Cargar Armas
            const resArmas = await fetch("/api/armas");
            const dataArmas = await resArmas.json();
            const armas = dataArmas.data ? dataArmas.data : dataArmas;

            if (Array.isArray(armas)) {
                armas.forEach((arma) => {
                    const latArma = parseFloat(arma.x || arma.cord_x);
                    const lonArma = parseFloat(arma.y || arma.cord_y);

                    if (!isNaN(latArma) && !isNaN(lonArma)) {
                        const marker = L.marker([latArma, lonArma]);
                        marker.bindPopup(`<b>Arma: ${arma.nombre}</b>`);
                        armaLayer.addLayer(marker);
                    }
                });
            }

            // 4. Ajuste de vista
            // 4. Ajuste de vista inicial (Enfocar todas las áreas guardadas)
            const allBounds = [];
            areas.forEach(area => {
                if (area.polygon) {
                    allBounds.push(area.polygon.getBounds());
                }
            });

            if (allBounds.length > 0) {
                const bounds = allBounds.reduce((total, b) => total.extend(b));
                if (bounds.isValid()) {
                    map.fitBounds(bounds.pad(0.2), {
                        animate: true,
                        duration: 1.5,
                    });
                }
            }

            // Inicializar Sidebar con los datos cargados
            inicializarSidebar(areas, impactos);
        } catch (error) {
            console.error("Error cargando marcadores:", error);
        }
    }

    cargarMarcadores();

    // ============================================
    // Lógica para enlazar Toolbar Custom
    // ============================================
    const btnZoomIn = document.querySelector("#btnZoomIn");
    const btnZoomOut = document.querySelector("#btnZoomOut");
    const btnSearch = document.querySelector("#btnSearch");
    const btnFilter = document.querySelector("#btnFilter");

    // 1. ZOOM
    if (btnZoomIn) btnZoomIn.addEventListener("click", () => map.zoomIn());
    if (btnZoomOut) btnZoomOut.addEventListener("click", () => map.zoomOut());

    // 2. BÚSQUEDA (API Nominatim)
    const searchContainer = document.querySelector("#search-container");
    const searchInput = document.querySelector("#search-input");
    const btnSearchSubmit = document.querySelector("#btnSearchSubmit");

    if (btnSearch && searchContainer) {
        btnSearch.addEventListener("click", () => {
            searchContainer.classList.toggle("hidden");
            if (!searchContainer.classList.contains("hidden"))
                searchInput.focus();
        });
    }

    async function buscarLocalizacion() {
        const query = searchInput.value.trim();
        if (!query) return;

        try {
            const response = await fetch(
                `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`,
            );
            const data = await response.json();

            if (data.length > 0) {
                const first = data[0];
                map.flyTo([first.lat, first.lon], 13, { duration: 1.5 });
                searchContainer.classList.add("hidden");
            } else {
                alert("No se encontró la localización.");
            }
        } catch (e) {
            console.error(e);
        }
    }

    if (btnSearchSubmit)
        btnSearchSubmit.addEventListener("click", buscarLocalizacion);
    if (searchInput)
        searchInput.addEventListener("keyup", (e) => {
            if (e.key === "Enter") buscarLocalizacion();
        });

    // 3. FILTROS
    const filterPanel = document.querySelector("#filter-panel");
    const chkImpactos = document.querySelector("#chkImpactos");
    const chkArmas = document.querySelector("#chkArmas");

    if (btnFilter && filterPanel) {
        btnFilter.addEventListener("click", () =>
            filterPanel.classList.toggle("hidden"),
        );
    }

    if (chkImpactos) {
        chkImpactos.addEventListener("change", (e) => {
            if (e.target.checked) map.addLayer(impactoLayer);
            else map.removeLayer(impactoLayer);
        });
    }

    if (chkArmas) {
        chkArmas.addEventListener("change", (e) => {
            if (e.target.checked) map.addLayer(armaLayer);
            else map.removeLayer(armaLayer);
        });
    }

    // ============================================
    // Lógica de la Barra Lateral Derecha
    // ============================================
    function inicializarSidebar(areas, impactos) {
        const areasContainer = document.querySelector("#areas-items-container");
        const searchInput = document.querySelector("#area-search");
        const btnBack = document.querySelector("#btn-back-sidebar");
        const listView = document.querySelector("#areas-list-view");
        const detailView = document.querySelector("#area-detail-view");

        function toggleAreaOnMap(area, isChecked) {
            if (isChecked) {
                if (area.polygon) area.polygon.addTo(map);
                if (area.objectivosGroup) area.objectivosGroup.addTo(map);
                if (area.impactsGroup) area.impactsGroup.addTo(map);
            } else {
                if (area.polygon) map.removeLayer(area.polygon);
                if (area.objectivosGroup) map.removeLayer(area.objectivosGroup);
                if (area.impactsGroup) map.removeLayer(area.impactsGroup);
            }
        }

        function renderList(filterText = "") {
            if (!areasContainer) return;
            areasContainer.innerHTML = "";
            
            const filteredAreas = areas.filter(area => 
                area.nombre.toLowerCase().includes(filterText.toLowerCase())
            );

            filteredAreas.forEach(area => {
                const areaImpacts = impactos.filter(imp => imp.id_area === area.id);
                const card = document.createElement("div");
                card.className = "area-card";
                const isSelected = area.polygon && map.hasLayer(area.polygon);
                
                card.innerHTML = `
                    <div class="area-card-checkbox">
                        <input type="checkbox" class="area-toggle-chk" data-id="${area.id}" ${isSelected ? 'checked' : ''}>
                    </div>
                    <div class="area-card-info" style="flex: 1;">
                        <div class="area-card-name">${area.nombre}</div>
                        <div class="area-card-stats">${areaImpacts.length} impactos</div>
                    </div>
                    <span class="badge-count">${areaImpacts.length}</span>
                `;
                
                const chk = card.querySelector(".area-toggle-chk");
                chk.addEventListener("click", (e) => {
                    e.stopPropagation(); // Evitar abrir detalle
                    toggleAreaOnMap(area, e.target.checked);
                });

                card.addEventListener("click", () => showDetail(area, areaImpacts));
                areasContainer.appendChild(card);
            });
        }

        function showDetail(area, areaImpacts) {
            listView.classList.add("hidden");
            detailView.classList.remove("hidden");

            document.querySelector("#detail-area-name").textContent = area.nombre;
            document.querySelector("#stat-impacts").textContent = areaImpacts.length;

            const totalEficacia = areaImpacts.reduce((sum, imp) => sum + (parseFloat(imp.eficacia) || 0), 0);
            const avgEficacia = areaImpacts.length > 0 ? (totalEficacia / areaImpacts.length).toFixed(1) : 0;
            document.querySelector("#stat-eficacia").textContent = `${avgEficacia}%`;

            const impactosList = document.querySelector("#detail-impactos");
            impactosList.innerHTML = "";
            areaImpacts.slice(0, 5).forEach(imp => {
                const item = document.createElement("div");
                item.className = "detail-item";
                item.innerHTML = `
                    <div class="detail-item-main">
                        <span class="detail-item-title">${imp.arma || 'Arma desconocida'}</span>
                        <span class="detail-item-sub">${new Date(imp.momento_impacto).toLocaleString()}</span>
                    </div>
                    <span class="detail-item-status ${imp.efectivo ? 'status-efectivo' : 'status-fallido'}">
                        ${imp.efectivo ? 'Efectivo' : 'Fallido'} (${imp.eficacia}%)
                    </span>
                `;
                impactosList.appendChild(item);
            });

            const armasList = document.querySelector("#detail-armas");
            armasList.innerHTML = "";
            const distinctArmas = [...new Set(areaImpacts.map(imp => imp.arma).filter(Boolean))];
            distinctArmas.forEach(arma => {
                const item = document.createElement("div");
                item.className = "detail-item";
                item.innerHTML = `<span class="detail-item-title">${arma}</span>`;
                armasList.appendChild(item);
            });

            // Focus on map
            if (area.vertices && area.vertices.length > 0) {
                const coords = area.vertices.map(v => [parseFloat(v.x), parseFloat(v.y)]);
                const bounds = L.latLngBounds(coords);
                if (bounds.isValid()) {
                    map.flyToBounds(bounds, { padding: [50, 50], duration: 1 });
                }
            }
        }

        if (btnBack) {
            btnBack.addEventListener("click", () => {
                detailView.classList.add("hidden");
                listView.classList.remove("hidden");
            });
        }

        if (searchInput) {
            searchInput.addEventListener("input", (e) => {
                renderList(e.target.value);
            });
        }

        renderList();
    }
});
