document.addEventListener('DOMContentLoaded', () => {
    // ============================================
    // DOM ELEMENTS - MODAL (Global)
    // ============================================
    const btnAnyadir = document.querySelector("#añadir");
    const modal = document.querySelector("#addModal");
    const btnCloseModal = document.querySelector("#closeModal");
    const tabBtns = document.querySelectorAll(".tab-btn");
    const forms = document.querySelectorAll(".custom-form");

    // Abrir modal
    if (btnAnyadir && modal) {
        btnAnyadir.addEventListener("click", () => {
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

    // Lógica de navegación entre pestañas (Impacto / Arma)
    tabBtns.forEach(btn => {
        btn.addEventListener("click", () => {
            // Deseleccionar todas las pestañas
            tabBtns.forEach(t => t.classList.remove("active"));
            forms.forEach(f => f.classList.add("hidden-form"));

            // Activar la seleccionada
            btn.classList.add("active");
            const targetId = btn.getAttribute("data-target");
            const targetForm = document.getElementById(targetId);
            if (targetForm) targetForm.classList.remove("hidden-form");
        });
    });

    // ============================================
    // CARGA DE RED DE SELECTS NORMALES
    // ============================================
    async function loadSelectOptions(selector, endpoint) {
        try {
            const selectElement = document.querySelector(selector);
            if (!selectElement) return;

            selectElement.innerHTML = '<option value="">Cargando...</option>';
            
            const response = await fetch(endpoint);
            const data = await response.json();
            
            // Laravel api resource usually returns an array in data.data or just data directly
            const items = data.data ? data.data : data;
            
            selectElement.innerHTML = '<option value="">Seleccione una opción</option>';
            items.forEach(item => {
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

    // Inicializamos una vez que el DOM está listo en la vista
    loadSelectOptions('#id_area', '/api/areas');
    loadSelectOptions('#id_arma', '/api/armas');
    loadSelectOptions('#id_grupo_arma', '/api/grupos');
});
