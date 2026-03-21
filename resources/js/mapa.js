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
                // Dibujar polígono del área (OJO: Necesitas arreglar el controlador de Laravel para que esto funcione)
                console.log(`🔍 Revisando Área: ${area.nombre}`);
                console.log(`¿Tiene vértices?:`, area.vertices);
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
                        areaLayer.addLayer(polygon);
                    }
                }

                // Dibujar Objetivos del área
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
                            areaLayer.addLayer(targetMarker);
                        }
                    });
                }
            });

            // 2. Cargar Impactos (AHORA VISIBLES)
            const resImpactos = await fetch("/api/impactos");
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

                            // CAMBIADO A circleMarker (usa PÍXELES en lugar de metros)
                            const circle = L.circleMarker([latImp, lonImp], {
                                color: esEfectivo ? "#1e40af" : "#991b1b", // Bordes oscuros
                                weight: 2,
                                fillColor: esEfectivo ? "#3b82f6" : "#ef4444",
                                fillOpacity: 0.8,
                                radius: 6, // 6 píxeles de radio. ¡Se verá en cualquier zoom!
                            });

                            circle.bindPopup(`
                                <div style="font-family: 'Inter', sans-serif;">
                                    <b style="color: ${esEfectivo ? "#3b82f6" : "#ef4444"};">Impacto ${esEfectivo ? "Efectivo" : "Fallido"}</b><br>
                                    Eficacia: <b>${imp.eficacia ?? 0}%</b>
                                </div>
                            `);
                            impactoLayer.addLayer(circle);
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
            const allFeatures = [];
            areaLayer.eachLayer((l) => allFeatures.push(l));
            impactoLayer.eachLayer((l) => allFeatures.push(l));
            armaLayer.eachLayer((l) => allFeatures.push(l));

            if (allFeatures.length > 0) {
                const group = L.featureGroup(allFeatures);
                const bounds = group.getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds.pad(0.1), {
                        animate: true,
                        duration: 1.5,
                    });
                }
            }
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
});
