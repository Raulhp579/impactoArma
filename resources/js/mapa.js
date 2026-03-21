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

    // Corrige error de Leaflet donde el contenedor SVG se crea con tamaño 0x0 u offset incorrecto
    setTimeout(() => {
        map.invalidateSize();
    }, 300);

    // Añadir una capa base (Map tiles) basada en el tema guardado
    const currentTheme = localStorage.getItem('mapTheme') || 'dark';
    
    const getTileUrl = (theme) => {
        switch(theme) {
            case 'light': return 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
            case 'satellite': return 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}';
            case 'normal': return 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            case 'dark':
            default: return 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
        }
    };

    const getTileOptions = (theme) => {
        let attribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
        if (theme === 'satellite') {
            attribution = 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community';
        } else if (theme === 'dark' || theme === 'light') {
            attribution += ' &copy; <a href="https://carto.com/attributions">CARTO</a>';
        }

        return {
            attribution: attribution,
            maxZoom: (theme === 'satellite' ? 18 : 19)
        };
    };

    let currentTileLayer = L.tileLayer(getTileUrl(currentTheme), getTileOptions(currentTheme)).addTo(map);

    // Escuchar cambios de tema desde los ajustes globales
    window.addEventListener('mapThemeChanged', (e) => {
        const newTheme = e.detail;
        map.removeLayer(currentTileLayer);
        currentTileLayer = L.tileLayer(getTileUrl(newTheme), getTileOptions(newTheme)).addTo(map);
    });
    // ============================================
    // Capas de Marcadores (Layer Groups)
    // ============================================
    const impactoLayer = L.layerGroup().addTo(map);
    const armaLayer = L.layerGroup().addTo(map);
    const areaLayer = L.layerGroup().addTo(map);

    // Cargar Marcadores desde la API
    async function cargarMarcadores() {
        try {
            // 1. Cargar Áreas y sus Vértices (Polígonos)
            const resAreas = await fetch('/api/areas');
            const areas = await resAreas.json();

            areas.forEach(area => {
                if (area.vertices && area.vertices.length >= 3) {
                    // Mapear vértices [lat, lon] para Leaflet
                    const coords = area.vertices.map(v => [parseFloat(v.x), parseFloat(v.y)]);
                    
                    const polygon = L.polygon(coords, {
                        color: '#059669', // Verde más oscuro para contraste
                        weight: 3,       // Borde más grueso
                        fillColor: '#10b981',
                        fillOpacity: 0.4, // Opacidad más alta para que se vea
                    });

                    polygon.bindPopup(`
                        <div style="font-family: 'Inter', sans-serif;">
                            <b style="color: #10b981;">Área: ${area.nombre}</b><br>
                            Objetivo: ${area.x_objetivo}, ${area.y_objetivo}<br>
                            Puntos del perímetro: ${area.vertices.length}
                        </div>
                    `);
                    areaLayer.addLayer(polygon);
                }

                // Dibujar Objetivo del área como un pin especial
                if (area.x_objetivo && area.y_objetivo) {
                    const targetMarker = L.circleMarker([area.x_objetivo, area.y_objetivo], {
                        radius: 6,
                        color: '#10b981',
                        fillColor: '#fff',
                        weight: 2,
                        fillOpacity: 1
                    });
                    targetMarker.bindPopup(`<b>Objetivo: ${area.nombre}</b>`);
                    areaLayer.addLayer(targetMarker);
                }
            });

            // 2. Cargar Impactos
            const resImpactos = await fetch('/api/impactos');
            const dataImpactos = await resImpactos.json();
            const impactos = dataImpactos.data ? dataImpactos.data : dataImpactos;

            impactos.forEach(imp => {
                if (imp.x_impacto && imp.y_impacto) {
                    const esEfectivo = imp.efectivo === 1 || imp.efectivo === true;
                    const circle = L.circle([imp.x_impacto, imp.y_impacto], {
                        color: esEfectivo ? '#2563eb' : '#dc2626', // Colores más saturados
                        weight: 3,
                        fillColor: esEfectivo ? '#3b82f6' : '#ef4444',
                        fillOpacity: 0.6, // Más opaco
                        radius: 50 // Un poco más grande para ser visible rápidamente
                    });

                    circle.bindPopup(`
                        <div style="font-family: 'Inter', sans-serif;">
                            <b style="color: ${esEfectivo ? '#3b82f6' : '#ef4444'};">Impacto ${esEfectivo ? 'Efectivo' : 'Fallido'}</b><br>
                            Eficacia: <b>${imp.eficacia ?? 0}%</b><br>
                            Momento: ${new Date(imp.momento_impacto).toLocaleString('es-ES')}
                        </div>
                    `);
                    impactoLayer.addLayer(circle);
                }
            });

            // 3. Cargar Armas
            const resArmas = await fetch('/api/armas');
            const dataArmas = await resArmas.json();
            const armas = dataArmas.data ? dataArmas.data : dataArmas;

            armas.forEach(arma => {
                if (arma.cord_x && arma.cord_y) {
                    const marker = L.marker([arma.cord_x, arma.cord_y]);
                    marker.bindPopup(`
                        <div style="font-family: 'Inter', sans-serif;">
                            <b style="color: #f59e0b;">Arma: ${arma.nombre}</b><br>
                            Tipo: ${arma.tipo}<br>
                            Descripción: ${arma.descripcion || 'Sin descripción'}
                        </div>
                    `);
                    armaLayer.addLayer(marker);
                }
            });

            // 4. Ajustar vista del mapa para encuadrar todos los marcadores
            const allFeatures = [];
            
            // Recoger capas
            areaLayer.eachLayer(l => allFeatures.push(l));
            impactoLayer.eachLayer(l => allFeatures.push(l));
            armaLayer.eachLayer(l => allFeatures.push(l));

            if (allFeatures.length > 0) {
                const group = L.featureGroup(allFeatures);
                map.fitBounds(group.getBounds().pad(0.1), {
                    animate: true,
                    duration: 1.5
                });
                
                // Forzar redimensionado final tras ajustar bordes
                setTimeout(() => {
                    map.invalidateSize();
                }, 100);
            }

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
