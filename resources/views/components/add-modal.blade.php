    <!-- MODAL COMPARTIDO AÑADIR (Oculto por defecto) -->
    <div id="addModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Añadir Elemento</h2>
                <button id="closeModal" class="btn-close">&times;</button>
            </div>
            
            <div class="modal-tabs">
                <button class="tab-btn active" data-target="form-impacto">Impacto</button>
                <button class="tab-btn" data-target="form-arma">Arma</button>
            </div>

            <div class="modal-body">
                <!-- FORMULARIO IMPACTO -->
                <div id="form-impacto" class="custom-form active-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Coordenada X</label>
                            <input type="number" step="any" id="x_impacto" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>Coordenada Y</label>
                            <input type="number" step="any" id="y_impacto" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Momento del Impacto</label>
                        <input type="datetime-local" id="momento_impacto">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>ID Área</label>
                            <select id="id_area"></select>
                        </div>
                        <div class="form-group">
                            <label>ID Arma </label>
                            <select id="id_arma"></select>
                        </div>
                    </div>
                    <button type="button" id="btnGuardarImpacto" class="btn-submit">Guardar Impacto</button>
                </div>

                <!-- FORMULARIO ARMA -->
                <div id="form-arma" class="custom-form hidden-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" id="nombre_arma" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>Tipo</label>
                            <input type="text" id="tipo_arma" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea id="descripcion_arma" rows="3"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Cord X</label>
                            <input type="number" step="any" id="cord_x_arma" autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>Cord Y</label>
                            <input type="number" step="any" id="cord_y_arma" autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>ID Grupo</label>
                        <select id="id_grupo_arma"></select>
                    </div>
                    <button type="button" id="btnGuardarArma" class="btn-submit">Guardar Arma</button>
                </div>
            </div>
        </div>
    </div>
