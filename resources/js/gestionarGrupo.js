document.addEventListener("DOMContentLoaded", () => {
    
    // ----------------------------------------
    // TABS SWITCHING (Áreras / Grupos)
    // ----------------------------------------
    const tabs = document.querySelectorAll(".tab-item");
    const track = document.getElementById("tables-track");
    const btnCrearArea = document.getElementById("btnCrearArea"); // Botón cabecera

    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");

            const val = tab.getAttribute("data-value");
            if (val === "areas") {
                if (track) track.style.transform = "translateX(0%)";
                if (btnCrearArea) btnCrearArea.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Añadir Área`;
            } else {
                if (track) track.style.transform = "translateX(-50%)";
                if (btnCrearArea) btnCrearArea.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Añadir Grupo`;
            }
        });
    });

    // Delegación del botón principal "Añadir"
    if (btnCrearArea) {
        btnCrearArea.addEventListener("click", () => {
            const activeTab = document.querySelector(".tab-item.active");
            const val = activeTab ? activeTab.getAttribute("data-value") : "areas";

            if (val === "areas") {
                const modalArea = document.getElementById("modalArea");
                const modalAreaTitle = document.getElementById("modalAreaTitle");
                const inputAreaId = document.getElementById("area_id");
                const formArea = document.getElementById("form-area");

                if (modalAreaTitle) modalAreaTitle.textContent = "Añadir Nueva Área";
                if (inputAreaId) inputAreaId.value = "";
                if (formArea) formArea.reset();
                if (typeof tempVertices !== 'undefined') tempVertices = [];
                if (typeof renderVertices === 'function') renderVertices();

                modalArea?.classList.remove("hidden");
            } else {
                abrirModalGrupo(); // Añadir Grupo
            }
        });
    }

    // ----------------------------------------
    // ELEMENTOS MODAL GRUD GRUPOS
    // ----------------------------------------
    const modalGrupo = document.getElementById("modalGrupo");
    const closeModalGrupo = document.getElementById("closeModalGrupo");
    const modalGrupoTitle = document.getElementById("modalGrupoTitle");
    const formGrupo = document.getElementById("form-grupo");
    const inputGrupoId = document.getElementById("grupo_id");
    const inputGrupoNombre = document.getElementById("grupo_nombre");
    const inputGrupoDescripcion = document.getElementById("grupo_descripcion"); // <--- Nuevo

    const modalDeleteGrupo = document.getElementById("modalDeleteGrupo");
    const closeModalDeleteGrupo = document.getElementById("closeModalDeleteGrupo");
    const btnCancelDeleteGrupo = document.getElementById("btnCancelDeleteGrupo");
    const btnConfirmDeleteGrupo = document.getElementById("btnConfirmDeleteGrupo");

    let deleteGrupoId = null;

    function abrirModalGrupo(id = "", nombre = "", descripcion = "") {
        if (!modalGrupo) return;
        if (modalGrupoTitle) modalGrupoTitle.textContent = id ? "Editar Grupo" : "Añadir Nuevo Grupo";
        if (inputGrupoId) inputGrupoId.value = id;
        if (inputGrupoNombre) inputGrupoNombre.value = nombre;
        if (inputGrupoDescripcion) inputGrupoDescripcion.value = descripcion; // <--- Nuevo
        modalGrupo.classList.remove("hidden");
    }

    if (closeModalGrupo) closeModalGrupo.addEventListener("click", () => modalGrupo.classList.add("hidden"));
    if (modalGrupo) {
        modalGrupo.addEventListener("click", (e) => { if (e.target === modalGrupo) modalGrupo.classList.add("hidden"); });
    }

    if (closeModalDeleteGrupo) closeModalDeleteGrupo.addEventListener("click", () => modalDeleteGrupo.classList.add("hidden"));
    if (btnCancelDeleteGrupo) btnCancelDeleteGrupo.addEventListener("click", () => modalDeleteGrupo.classList.add("hidden"));
    if (modalDeleteGrupo) {
        modalDeleteGrupo.addEventListener("click", (e) => { if (e.target === modalDeleteGrupo) modalDeleteGrupo.classList.add("hidden"); });
    }

    // ----------------------------------------
    // GUARDAR GRUPO (CREAR / EDITAR)
    // ----------------------------------------
    if (formGrupo) {
        formGrupo.addEventListener("submit", async (e) => {
            e.preventDefault();
            const id = inputGrupoId.value;
            const datos = { 
                nombre: inputGrupoNombre.value,
                descripcion: inputGrupoDescripcion ? inputGrupoDescripcion.value : ""
            };

            const url = id ? `/api/grupos/${id}` : "/api/grupos";
            const method = id ? "PUT" : "POST";

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: { 
                        "Content-Type": "application/json", 
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken || ''
                    },
                    body: JSON.stringify(datos)
                });
                if (res.ok) {
                    modalGrupo.classList.add("hidden");
                    window.location.reload();
                } else { 
                    const errObj = await res.json().catch(() => ({}));
                    alert("Error al guardar: " + (errObj.message || res.statusText)); 
                }
            } catch (error) { console.error(error); }
        });
    }

    // ----------------------------------------
    // DELEGACIÓN CLICS TABLA GRUPOS
    // ----------------------------------------
    const gruposTableBody = document.getElementById("gruposTableBody");
    if (gruposTableBody) {
        gruposTableBody.addEventListener("click", (e) => {
            
            const btnEdit = e.target.closest(".btn-edit-grupo");
            if (btnEdit) {
                const id = btnEdit.getAttribute("data-id");
                const nombre = btnEdit.getAttribute("data-nombre");
                const descripcion = btnEdit.getAttribute("data-descripcion") || ""; // <--- Nuevo
                abrirModalGrupo(id, nombre, descripcion);
                return;
            }

            const btnDelete = e.target.closest(".btn-danger-grupo");
            if (btnDelete) {
                deleteGrupoId = btnDelete.getAttribute("data-id");
                if (modalDeleteGrupo) modalDeleteGrupo.classList.remove("hidden");
                return;
            }
        });
    }

    // Confirmar Borrado Grupo
    if (btnConfirmDeleteGrupo) {
        btnConfirmDeleteGrupo.addEventListener("click", async () => {
            if (!deleteGrupoId) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const res = await fetch(`/api/grupos/${deleteGrupoId}`, { 
                    method: "DELETE",
                    headers: {
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": csrfToken || ''
                    }
                });
                if (res.ok) { 
                    window.location.reload(); 
                } else { 
                    const errObj = await res.json().catch(() => ({}));
                    alert("Error al eliminar: " + (errObj.message || res.statusText)); 
                }
            } catch (error) { console.error(error); }
        });
    }



});
