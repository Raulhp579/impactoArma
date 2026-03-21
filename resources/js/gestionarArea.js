document.addEventListener("DOMContentLoaded", () => {
    // ----------------------------------------
    // ELEMENTOS MODAL CREAR/EDITAR
    // ----------------------------------------
    const btnCrearArea = document.getElementById("btnCrearArea");
    const modalArea = document.getElementById("modalArea");
    const closeModalArea = document.getElementById("closeModalArea");
    const modalAreaTitle = document.getElementById("modalAreaTitle");

    let tempVertices = [];

    // Formulario Área
    const formArea = document.getElementById("form-area");
    const inputAreaId = document.getElementById("area_id");
    const inputAreaNombre = document.getElementById("area_nombre");
    const inputAreaX = document.getElementById("area_x");
    const inputAreaY = document.getElementById("area_y");

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
            const x = parseFloat(inputVerticeX.value);
            const y = parseFloat(inputVerticeY.value);
            
            if (isNaN(x) || isNaN(y)) {
                alert("Introduce coordenadas X e Y válidas para el vértice.");
                return;
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
    if (btnCrearArea) {
        btnCrearArea.addEventListener("click", () => {
            modalAreaTitle.textContent = "Añadir Nueva Área";
            formArea.reset();
            inputAreaId.value = "";
            tempVertices = [];
            renderVertices();
            modalArea.classList.remove("hidden");
        });
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
        const btnEdit = e.target.closest(".btn-edit");
        if (btnEdit) {
            const row = btnEdit.closest("tr");
            const id = row.cells[0].textContent.trim();
            const nombre = row.cells[1].textContent.trim();
            const x = row.cells[2].textContent.trim();
            const y = row.cells[3].textContent.trim();

            modalAreaTitle.textContent = "Editar Área";
            inputAreaId.value = id;
            inputAreaNombre.value = nombre;
            inputAreaX.value = x;
            inputAreaY.value = y;

            tempVertices = [];
            renderVertices();
            // Fetch vertices for this area
            fetch(`/api/areas/${id}`)
                .then(r => r.json())
                .then(data => {
                    if(data && data.vertices) {
                        tempVertices = data.vertices.map(v => ({x: v.x, y: v.y}));
                        renderVertices();
                    }
                })
                .catch(err => console.error("Error fetching vertices:", err));

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
                x_objetivo: inputAreaX.value,
                y_objetivo: inputAreaY.value,
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
});
