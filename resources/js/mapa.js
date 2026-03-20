////////VISTA HOME/////////////
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import '@fontsource/inter'; // Fuente descargada localmente

// CORRECCIÓN PARA ICONOS DE LEAFLET EN VITE (Marcadores que no se ven)
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
    iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
    shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar el mapa de Leaflet en el div con id "map"
    const map = L.map('map', {
        zoomControl: false 
    }).setView([40.4168, -3.7038], 6);

    // Añadir una capa base (Map tiles). 
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        maxZoom: 19
    }).addTo(map);

    // ============================================
    // Capas de Marcadores (Layer Groups)
    // ============================================
    const impactoLayer = L.layerGroup().addTo(map);
    const armaLayer = L.layerGroup().addTo(map);

    // Cargar Marcadores desde la API
    async function cargarMarcadores() {
        try {
            // 1. Cargar Impactos
            const resImpactos = await fetch('/api/impactos');
            const dataImpactos = await resImpactos.json();
            const impactos = dataImpactos.data ? dataImpactos.data : dataImpactos;

            impactos.forEach(imp => {
                if (imp.x_impacto && imp.y_impacto) {
                    const circle = L.circle([imp.x_impacto, imp.y_impacto], {
                        color: imp.es_eficaz ? '#3b82f6' : '#ef4444',
                        weight: 2,
                        fillColor: imp.es_eficaz ? '#2563eb' : '#dc2626',
                        fillOpacity: 0.5,
                        radius: 80
                    });
                    circle.bindPopup(`<b>Impacto ID: ${imp.id}</b><br>Eficacia: ${imp.es_eficaz ? 'Eficaz' : 'Fallido'}`);
                    impactoLayer.addLayer(circle);
                }
            });

            // 2. Cargar Armas
            const resArmas = await fetch('/api/armas');
            const dataArmas = await resArmas.json();
            const armas = dataArmas.data ? dataArmas.data : dataArmas;

            armas.forEach(arma => {
                if (arma.cord_x && arma.cord_y) {
                    const marker = L.marker([arma.cord_x, arma.cord_y]);
                    marker.bindPopup(`<b>Arma: ${arma.nombre}</b><br>Tipo: ${arma.tipo}`);
                    armaLayer.addLayer(marker);
                }
            });

        } catch (error) {
            console.error("Error cargando marcadores:", error);
        }
    }

    cargarMarcadores();

    // ============================================
    // Lógica para enlazar Toolbar Custom
    // ============================================
    const btnZoomIn = document.querySelector('#btnZoomIn');
    const btnZoomOut = document.querySelector('#btnZoomOut');
    const btnSearch = document.querySelector('#btnSearch');
    const btnFilter = document.querySelector('#btnFilter');

    // 1. ZOOM
    if (btnZoomIn) btnZoomIn.addEventListener('click', () => map.zoomIn());
    if (btnZoomOut) btnZoomOut.addEventListener('click', () => map.zoomOut());

    // 2. BÚSQUEDA (API Nominatim)
    const searchContainer = document.querySelector('#search-container');
    const searchInput = document.querySelector('#search-input');
    const btnSearchSubmit = document.querySelector('#btnSearchSubmit');

    if (btnSearch && searchContainer) {
        btnSearch.addEventListener('click', () => {
            searchContainer.classList.toggle('hidden');
            if (!searchContainer.classList.contains('hidden')) searchInput.focus();
        });
    }

    async function buscarLocalizacion() {
        const query = searchInput.value.trim();
        if (!query) return;

        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.length > 0) {
                const first = data[0];
                map.flyTo([first.lat, first.lon], 13, { duration: 1.5 });
                searchContainer.classList.add('hidden');
            } else {
                alert("No se encontró la localización.");
            }
        } catch (e) { console.error(e); }
    }

    if (btnSearchSubmit) btnSearchSubmit.addEventListener('click', buscarLocalizacion);
    if (searchInput) searchInput.addEventListener('keyup', (e) => { if (e.key === 'Enter') buscarLocalizacion(); });

    // 3. FILTROS
    const filterPanel = document.querySelector('#filter-panel');
    const chkImpactos = document.querySelector('#chkImpactos');
    const chkArmas = document.querySelector('#chkArmas');

    if (btnFilter && filterPanel) {
        btnFilter.addEventListener('click', () => filterPanel.classList.toggle('hidden'));
    }

    if (chkImpactos) {
        chkImpactos.addEventListener('change', (e) => {
            if (e.target.checked) map.addLayer(impactoLayer);
            else map.removeLayer(impactoLayer);
        });
    }

    if (chkArmas) {
        chkArmas.addEventListener('change', (e) => {
            if (e.target.checked) map.addLayer(armaLayer);
            else map.removeLayer(armaLayer);
        });
    }
});
