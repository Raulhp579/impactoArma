import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css'; // Estilos base de DT

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

    cargarSelectsEdit('#edit_id_area', '/api/areas');
    cargarSelectsEdit('#edit_id_arma', '/api/armas');

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
            const idArma = btnEdit.getAttribute('data-id-arma');

            editImpactoId.value = id;
            editXImpacto.value = x;
            editYImpacto.value = y;
            editMomentoImpacto.value = momento;

            if (editIdArea) editIdArea.value = idArea || "";
            if (editIdArma) editIdArma.value = idArma || "";

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

            const datos = {
                x_impacto: editXImpacto.value,
                y_impacto: editYImpacto.value,
                momento_impacto: editMomentoImpacto.value,
                id_area: editIdArea.value,           
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
