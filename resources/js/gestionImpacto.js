import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css'; // Estilos base de DT
import proj4 from 'proj4';

const UTM_ZONES = {
    'ES': "+proj=utm +zone=30 +ellps=GRS80 +towgs84=0,0,0,0,0,0,0 +units=m +no_defs",
    'LV': "+proj=utm +zone=35 +ellps=WGS84 +datum=WGS84 +units=m +no_defs"
};

document.addEventListener('DOMContentLoaded', () => {
    // ----------------------------------------
    // 1. INICIALIZAR DATATABLE
    // ----------------------------------------
    let table = new DataTable('#impactosTable', {
        responsive: true,
        lengthChange: false, 
        searching: true, 
        dom: 't', 
        paging: false,       
        info: false,         
        columnDefs: [
            { orderable: false, targets: 7 } 
        ],
        language: {
            "emptyTable": "No hay datos disponibles",
            "search": "Buscar:"
        }
    });

    // 1.b INICIALIZAR DATATABLE ARMAS
    let tableArmas = new DataTable('#armasTable', {
        responsive: true,
        lengthChange: false, 
        searching: true, 
        dom: 't', 
        paging: false,       
        info: false,         
        ajax: { url: '/api/armas', dataSrc: '' },
        columns: [
            { data: 'id', className: 'text-muted' },
            { data: 'nombre' },
            { data: 'tipo' },
            { data: 'descripcion', defaultContent: '' },
            { data: 'grupo' },
            { data: null, render: function(row) { return `${row.x} / ${row.y}`; } },
            { data: null, render: function(row) {
                const desc = row.descripcion ? row.descripcion.replace(/"/g, '&quot;') : '';
                return `<div class="td-actions">
                            <button class="btn btn-edit btn-edit-arma" 
                                data-id="${row.id}" 
                                data-nombre="${row.nombre}" 
                                data-tipo="${row.tipo}" 
                                data-descripcion="${desc}" 
                                data-id_grupo="${row.id_grupo}" 
                                data-x="${row.x}" 
                                data-y="${row.y}">Editar</button>
                            <button class="btn btn-danger btn-delete-arma-modal" data-id="${row.id}">Borrar</button>
                        </div>`;
            }}
        ],
        language: {
            "emptyTable": "No hay armas registradas",
            "search": "Buscar:"
        }
    });

    // ----------------------------------------
    // 1.c LÓGICA DE DESLIZAMIENTO DE TABLAS (Tabs)
    // ----------------------------------------
    document.querySelectorAll('.tab-item').forEach(tab => {
        tab.addEventListener('click', () => {
            const value = tab.getAttribute('data-value');

            document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            const slider = document.getElementById('table-slider');
            const filterEficacia = document.getElementById('filter-eficacia');

            if (slider) {
                slider.style.transform = value === 'armas' ? 'translateX(-50%)' : 'translateX(0%)';
            }
            
            if (filterEficacia) {
                filterEficacia.style.display = value === 'armas' ? 'none' : 'block';
            }
        });
    });

    // ----------------------------------------
    // 1.d EVENTOS PARA TABLA ARMAS (Editar/Borrar Modales)
    // ----------------------------------------
    const modalEditArma = document.getElementById('modalEditArma');
    const modalDeleteArma = document.getElementById('modalDeleteArma');

    document.querySelector('#armasTable tbody')?.addEventListener('click', (e) => {
        // EDITAR ARMA
        const btnEdit = e.target.closest('.btn-edit-arma');
        if (btnEdit) {
            document.getElementById('edit_arma_id').value = btnEdit.getAttribute('data-id');
            document.getElementById('edit_nombre_arma').value = btnEdit.getAttribute('data-nombre');
            document.getElementById('edit_tipo_arma').value = btnEdit.getAttribute('data-tipo');
            document.getElementById('edit_descripcion_arma').value = btnEdit.getAttribute('data-descripcion');
            document.getElementById('edit_x_arma').value = btnEdit.getAttribute('data-x');
            document.getElementById('edit_y_arma').value = btnEdit.getAttribute('data-y');

            const idGrupo = btnEdit.getAttribute('data-id_grupo');
            const selectGrupo = document.getElementById('edit_id_grupo_arma');
            if (selectGrupo) selectGrupo.value = idGrupo || "";

            modalEditArma.classList.remove('hidden');
        }

        // ELIMINAR ARMA
        const btnDelete = e.target.closest('.btn-delete-arma-modal');
        if (btnDelete) {
            document.getElementById('delete_arma_id').value = btnDelete.getAttribute('data-id');
            modalDeleteArma.classList.remove('hidden');
        }
    });

    // Cargar select grupos en edit de arma
    setTimeout(() => {
        if (typeof cargarSelectsEdit === 'function') {
            cargarSelectsEdit('#edit_id_grupo_arma', '/api/grupos');
        }
    }, 500);

    // Cerrar Modales Arma
    document.getElementById('closeModalEditArma')?.addEventListener('click', () => modalEditArma.classList.add('hidden'));
    document.getElementById('btnCancelDeleteArma')?.addEventListener('click', () => modalDeleteArma.classList.add('hidden'));

    modalEditArma?.addEventListener('click', (e) => { if (e.target === modalEditArma) modalEditArma.classList.add('hidden'); });
    modalDeleteArma?.addEventListener('click', (e) => { if (e.target === modalDeleteArma) modalDeleteArma.classList.add('hidden'); });

    // Submit Editar Arma
    document.getElementById('form-edit-arma')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('edit_arma_id').value;
        
        let xObj = parseFloat(document.getElementById('edit_x_arma').value);
        let yObj = parseFloat(document.getElementById('edit_y_arma').value);

        if (Math.abs(xObj) > 180 || Math.abs(yObj) > 180) {
            try {
                const countryCode = document.getElementById('utm_country_edit_arma')?.value || 'ES';
                const epsg = UTM_ZONES[countryCode] || UTM_ZONES['ES'];
                const [lon, lat] = proj4(epsg, "EPSG:4326", [xObj, yObj]);
                xObj = lat;
                yObj = lon;
            } catch(er) {
                alert("Error convirtiendo UTM: " + er.message);
                return;
            }
        }

        const datos = {
            nombre: document.getElementById('edit_nombre_arma').value,
            tipo: document.getElementById('edit_tipo_arma').value,
            descripcion: document.getElementById('edit_descripcion_arma').value,
            id_grupo: document.getElementById('edit_id_grupo_arma').value,
            cord_x: xObj,
            cord_y: yObj
        };

        try {
            const response = await fetch(`/api/armas/${id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(datos)
            });
            if (response.ok) {
                modalEditArma.classList.add('hidden');
                tableArmas.ajax.reload();
            } else {
                alert("Error al actualizar arma.");
            }
        } catch (err) { console.error(err); }
    });

    // Confirmar Eliminar Arma
    document.getElementById('btnConfirmDeleteArma')?.addEventListener('click', async () => {
        const id = document.getElementById('delete_arma_id').value;
        try {
            const response = await fetch(`/api/armas/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json' }
            });
            if (response.ok) {
                modalDeleteArma.classList.add('hidden');
                tableArmas.ajax.reload();
            } else {
                alert("Error al eliminar arma.");
            }
        } catch (err) { console.error(err); }
    });

    // ----------------------------------------
    // 2. FILTRO POR EFICACIA
    // ----------------------------------------
    document.getElementById('filter-eficacia')?.addEventListener('change', (e) => {
        const val = e.target.value;
        if (val === "") {
            table.column(5).search('').draw();
        } else {
            table.column(5).search(val, false, false).draw();
        }
    });

    // ----------------------------------------
    // 3. CARGA DE SELECTS (EDIT MODAL)
    // ----------------------------------------
    const editIdArea = document.getElementById('edit_id_area');
    const editIdArma = document.getElementById('edit_id_arma');

    async function cargarSelectsEdit(selector, endpoint) {
        try {
            const selectElement = document.querySelector(selector);
            if (!selectElement) return;

            selectElement.innerHTML = '<option value="">Cargando...</option>';
            const response = await fetch(endpoint);
            const data = await response.json();
            const items = data.data ? data.data : data;

            selectElement.innerHTML = '<option value="">Seleccione una opción</option>';
            items.forEach(item => {
                const text = item.nombre ? item.nombre : `ID: ${item.id}`;
                const option = document.createElement("option");
                option.value = item.id;
                option.textContent = text;
                selectElement.appendChild(option);
            });
        } catch (error) {
            console.error(`Error cargando los datos de ${endpoint}:`, error);
        }
    }

    // NUEVO: Cargar objetivos para el editar
    async function cargarObjetivosEdit(areaId, selectElement, selectedId = null) {
        if (!selectElement) return;
        if (!areaId) {
            selectElement.innerHTML = '<option value="">Selecciona un área...</option>';
            return;
        }

        selectElement.innerHTML = '<option value="">Cargando objetivos...</option>';
        try {
            const response = await fetch(`/api/areas/${areaId}`);
            const data = await response.json();
            const objetivos = data.objetivos || [];

            selectElement.innerHTML = '<option value="">Seleccione un objetivo</option>';
            objetivos.forEach(obj => {
                const option = document.createElement("option");
                option.value = obj.id;
                option.textContent = obj.nombre || `Objetivo ID: ${obj.id}`;
                if (selectedId && obj.id == selectedId) {
                    option.selected = true;
                }
                selectElement.appendChild(option);
            });
        } catch (error) { console.error(error); }
    }

    cargarSelectsEdit('#edit_id_area', '/api/areas');
    cargarSelectsEdit('#edit_id_arma', '/api/armas');

    const editIdObjetivo = document.getElementById('edit_id_objetivo');
    if (editIdArea) {
        editIdArea.addEventListener('change', (e) => {
            cargarObjetivosEdit(e.target.value, editIdObjetivo);
        });
    }

    // ----------------------------------------
    // 4. ELEMENTOS MODALES
    // ----------------------------------------
    const modalEditImpacto = document.getElementById('modalEditImpacto');
    const closeModalEditImpacto = document.getElementById('closeModalEditImpacto');
    const formEditImpacto = document.getElementById('form-edit-impacto');
    
    const editImpactoId = document.getElementById('edit_impacto_id');
    const editXImpacto = document.getElementById('edit_x_impacto');
    const editYImpacto = document.getElementById('edit_y_impacto');
    const editMomentoImpacto = document.getElementById('edit_momento_impacto');


    const modalDeleteImpacto = document.getElementById('modalDeleteImpacto');
    const btnCancelDeleteImpacto = document.getElementById('btnCancelDeleteImpacto');
    const btnConfirmDeleteImpacto = document.getElementById('btnConfirmDeleteImpacto');
    const deleteImpactoId = document.getElementById('delete_impacto_id');

    const btnGuardarImpacto = document.getElementById('btnGuardarImpacto');
    const btnGuardarArma = document.getElementById('btnGuardarArma');
    const addModal = document.getElementById('addModal');

    // ----------------------------------------
    // 5. EVENT DELEGATION PARA TABLA
    // ----------------------------------------
    document.querySelector('#impactosTable tbody')?.addEventListener('click', (e) => {
        // EDITAR
        const btnEdit = e.target.closest('.btn-edit');
        if (btnEdit) {
            const id = btnEdit.getAttribute('data-id');
            const x = btnEdit.getAttribute('data-x');
            const y = btnEdit.getAttribute('data-y');
            let momento = btnEdit.getAttribute('data-momento');
            if (momento) momento = momento.replace(' ', 'T').substring(0, 16);
            
            const efectivo = btnEdit.getAttribute('data-efectivo') == '1' || btnEdit.getAttribute('data-efectivo') == 'true';
            const idArea = btnEdit.getAttribute('data-id-area');
            const idObjetivo = btnEdit.getAttribute('data-id-objetivo'); // <--- Nuevo
            const idArma = btnEdit.getAttribute('data-id-arma');

            editImpactoId.value = id;
            editXImpacto.value = x;
            editYImpacto.value = y;
            editMomentoImpacto.value = momento;

            if (editIdArea) editIdArea.value = idArea || "";
            if (editIdArma) editIdArma.value = idArma || "";

            // Cargar objetivos del área y seleccionar el guardado
            if (idArea && editIdObjetivo) {
                cargarObjetivosEdit(idArea, editIdObjetivo, idObjetivo);
            } else if (editIdObjetivo) {
                editIdObjetivo.innerHTML = '<option value="">Selecciona un área...</option>';
            }

            modalEditImpacto.classList.remove('hidden');
        }

        // ELIMINAR
        const btnDelete = e.target.closest('.btn-danger');
        if (btnDelete) {
            const id = btnDelete.getAttribute('data-id');
            deleteImpactoId.value = id;
            modalDeleteImpacto.classList.remove('hidden');
        }
    });

    // ----------------------------------------
    // 6. CERRAR MODALES
    // ----------------------------------------
    closeModalEditImpacto?.addEventListener('click', () => modalEditImpacto.classList.add('hidden'));
    
    modalEditImpacto?.addEventListener('click', (e) => {
        if (e.target === modalEditImpacto) modalEditImpacto.classList.add('hidden');
    });

    btnCancelDeleteImpacto?.addEventListener('click', () => modalDeleteImpacto.classList.add('hidden'));
    
    modalDeleteImpacto?.addEventListener('click', (e) => {
        if (e.target === modalDeleteImpacto) modalDeleteImpacto.classList.add('hidden');
    });

    // ----------------------------------------
    // 7. ACCIONES API (AJAX)
    // ----------------------------------------

    // Actualizar Impacto
    if (formEditImpacto) {
        formEditImpacto.addEventListener('submit', async (e) => {
            e.preventDefault();
            const id = editImpactoId.value;
            if (!id) return;

            let xImp = parseFloat(editXImpacto.value);
            let yImp = parseFloat(editYImpacto.value);

            if (Math.abs(xImp) > 180 || Math.abs(yImp) > 180) {
                try {
                    const countryCode = document.getElementById('utm_country_edit_impacto')?.value || 'ES';
                    const epsg = UTM_ZONES[countryCode] || UTM_ZONES['ES'];
                    const [lon, lat] = proj4(epsg, "EPSG:4326", [xImp, yImp]);
                    xImp = lat;
                    yImp = lon;
                } catch(er) {
                    alert("Error convirtiendo UTM: " + er.message);
                    return;
                }
            }

            const datos = {
                x_impacto: xImp,
                y_impacto: yImp,
                momento_impacto: editMomentoImpacto.value,
                id_area: editIdArea.value,           
                id_objetivo: document.getElementById('edit_id_objetivo')?.value || null, // <--- Nuevo
                id_arma: editIdArma.value            
            };

            try {
                const response = await fetch(`/api/impactos/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify(datos)
                });

                if (response.ok) {
                    modalEditImpacto.classList.add('hidden');
                    window.location.reload();
                } else {
                    const errorData = await response.json();
                    alert("Error: " + (errorData.message || response.statusText));
                }
            } catch (error) {
                console.error(error);
                alert("Error de conexión");
            }
        });
    }

    // Eliminar Impacto
    if (btnConfirmDeleteImpacto) {
        btnConfirmDeleteImpacto.addEventListener('click', async () => {
            const id = deleteImpactoId.value;
            if (!id) return;

            try {
                const response = await fetch(`/api/impactos/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json' }
                });

                if (response.ok) {
                    modalDeleteImpacto.classList.add('hidden');
                    window.location.reload();
                } else {
                    const errorData = await response.json();
                    alert("Error: " + (errorData.message || response.statusText));
                }
            } catch (error) {
                console.error(error);
                alert("Error de conexión");
            }
        });
    }
});
