<?php require_once("../index/header.php"); ?>

<style>
    .section-header {
        font-size: 0.875rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #34495e;
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        border-left: 3px solid #3498db;
        padding-left: 0.75rem;
    }

    .section-header:first-child {
        margin-top: 0;
    }

    .form-label {
        font-weight: 600;
        color: #2c3e50;
    }

    .form-label .required {
        color: #e74c3c;
    }

    .help-text {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .inspection-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 1200px) {
        .inspection-container {
            grid-template-columns: 1fr;
        }

        .preview-sticky {
            position: relative;
            top: 0;
        }
    }

    .preview-sticky {
        position: sticky;
        top: 1.5rem;
        height: fit-content;
    }

    .certificate {
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        font-size: 0.75rem;
        line-height: 1.5;
        min-height: 600px;
        display: flex;
        flex-direction: column;
    }

    .cert-header {
        text-align: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #2c3e50;
    }

    .cert-logo {
        font-size: 1.5rem;
        margin-bottom: 0.3rem;
    }

    .cert-title {
        font-size: 1rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .cert-subtitle {
        font-size: 0.7rem;
        color: #6c757d;
    }

    .cert-info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.4rem 0;
        border-bottom: 1px solid #dee2e6;
    }

    .cert-info-label {
        font-weight: 700;
        color: #34495e;
        width: 35%;
    }

    .cert-info-value {
        flex: 1;
        color: #2c3e50;
    }

    .cert-info-value.empty {
        color: #adb5bd;
        font-style: italic;
    }

    .cert-section {
        margin-top: 1rem;
        flex: 1;
    }

    .cert-section-title {
        font-size: 0.7rem;
        font-weight: 700;
        color: #2c3e50;
        text-transform: uppercase;
        border-bottom: 1px solid #34495e;
        padding-bottom: 0.3rem;
        margin-bottom: 0.6rem;
    }

    .cert-data {
        padding: 0.3rem 0;
        border-bottom: 1px dotted #dee2e6;
    }

    .cert-data-label {
        font-weight: 700;
        color: #34495e;
        font-size: 0.65rem;
    }

    .cert-data-value {
        color: #2c3e50;
        margin-top: 0.1rem;
        padding-left: 0.5rem;
        font-size: 0.75rem;
    }

    .cert-footer {
        margin-top: auto;
        text-align: center;
        padding-top: 1rem;
        border-top: 1px solid #dee2e6;
        font-size: 0.65rem;
        color: #6c757d;
    }

    .progress-simple {
        height: 0.25rem;
        background: #dee2e6;
        margin-top: 1rem;
        overflow: hidden;
    }

    .progress-simple-bar {
        height: 100%;
        background: #27ae60;
        width: 33.33%;
    }
</style>

<div class="container rounded shadow p-4">
    <h4 class="tittlecont">FCalidad de líquidos y formulados</h4>

    <div class="inspection-container">
        <!-- COLUMNA IZQUIERDA: FORMULARIO -->
        <div>
            <div class="card">
                <div class="card-body">
                    <form method="POST" id="inspectionForm">
                        <!-- INSPECTOR -->
                        <div class="section-header"><i class="fa-solid fa-magnifying-glass"></i> Inspección</div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <label for="fecha_inspeccion" class="form-label">Fecha de Emisión <span
                                            class="required">*</span></label>
                                    <input type="date" class="form-control form-control-sm" id="fecha_inspeccion"
                                        data-preview="preview-fecha_inspeccion" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-4">
                                    <label for="clave_inspeccion" class="form-label">Clave
                                        <span class="required">*</span>
                                    </label>
                                <input type="text" name="clave" id="clave_inspeccion" class="form-control form-control-sm" data-preview="preview-clave_inspeccion" required>
                                </div>
                                <div class="col-8">
                                    <label for="descripcion_inspeccion" class="form-label">Descripción</label>
                                    <input type="text" name="descripcion" id="descripcion_inspeccion" class="form-control form-control-sm" data-preview="preview-descripcion_inspeccion" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <label for="categoria_inspeccion" class="form-label">Categoria</label>
                                    <input type="text" name="categoria" id="categoria_inspeccion" class="form-control form-control-sm" data-preview="preview-categoria_inspeccion" readonly>
                                </div>
                                <div class="col-6">
                                    <label for="producto_inspeccion" class="form-label">Producto</label>
                                    <input type="text" name="producto" id="producto_inspeccion" class="form-control form-control-sm" data-preview="preview-producto_inspeccion" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <label for="fabricante_inspeccion" class="form-label">Nombre del fabricante
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" name="fabricant e" id="fabricante_inspeccion" class="form-control form-control-sm" data-preview="preview-fabricante_inspeccion">
                                </div>
                                <div class="col-6">
                                    <label for="pais_inspeccion" class="form-label">País de origen
                                        <span class="required">*</span>
                                    </label>
                                    <input type="text" name="fabricant e" id="pais_inspeccion" class="form-control form-control-sm" data-preview="preview-pais_inspeccion">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <label for="fecha_fabricacion_inspeccion" class="form-label">Fecha de Fabricación
                                        <span class="required">*</span>
                                    </label>
                                    <input type="date" name="fecha" id="fecha_fabricacion_inspeccion" class="form-control form-control-sm" data-preview="preview-fecha_fabricacion_inspeccion">
                                </div>
                                <div class="col-6">
                                    <label for="fecha_caducidad_inspeccion" class="form-label">Fecha de Caducidad
                                    </label>
                                    <input type="date" name="fecha" id="fecha_caducidad_inspeccion" class="form-control form-control-sm" data-preview="preview-fecha_caducidad_inspeccion">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Número de lote
                                <span class="required">*</span>
                            </label>
                            <input type="text" name="lote" id="lote_inspeccion" class="form-control form-control-sm" data-preview="preview-lote_inspeccion">
                        </div>

                        <!-- BOTONES -->
                        <div class="d-grid gap-2 d-md-flex">
                            <button type="submit" class="btn btn-success btn-sm flex-grow-1">✓ Completar</button>
                            <button type="reset" class="btn btn-secondary btn-sm">↻ Limpiar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: CERTIFICADO PREVIEW -->
        <div class="preview-sticky">
            <div class="certificate">
                <div class="cert-header">
                    <img src="../img/imglogoprosede.png" alt="" width="200" class="cert-logo">
                    <div class="cert-title">CERTIFICADO DE CALIDAD DE LÍQUIDOS Y FORMULADOS</div>
                    <div class="cert-subtitle">Etapa 1: Inspección</div>
                </div>

                <div class="cert-section">
                    <div class="cert-section-title">Datos de Inspección</div>
                    <div class="cert-data">
                        <div class="row">
                            <div class="col-5">
                                <div class="cert-info-label">Clave del Producto</div>
                            </div>
                            <div class="col-7">
                                <div class="cert-data-value empty" id="preview-clave_inspeccion">(completar)</div>
                            </div>
                        </div>                        
                    </div>
                    <div class="cert-data">
                        <div class="cert-data-label">Fecha</div>
                        <div class="cert-data-value empty" id="preview-fecha_inspeccion">(completar)</div>
                    </div>
                    <div class="cert-data">
                        <div class="cert-data-label">Lugar</div>
                        <div class="cert-data-value empty" id="preview-lugar_inspeccion">(completar)</div>
                    </div>
                </div>

                <div class="cert-section">
                    <div class="cert-section-title">Condiciones</div>
                    <div class="cert-data">
                        <div class="cert-data-label">Temperatura</div>
                        <div class="cert-data-value empty" id="preview-temperatura">(completar)</div>
                    </div>
                    <div class="cert-data">
                        <div class="cert-data-label">Humedad</div>
                        <div class="cert-data-value empty" id="preview-humedad">(completar)</div>
                    </div>
                    <div class="cert-data">
                        <div class="cert-data-label">Estado</div>
                        <div class="cert-data-value empty" id="preview-condiciones_ambientales">(completar)</div>
                    </div>
                </div>

                <div class="cert-section">
                    <div class="cert-section-title">Observaciones</div>
                    <div class="cert-data-value empty" id="preview-observaciones">(completar)</div>
                </div>

                <div class="cert-section">
                    <div class="cert-section-title">Anomalías</div>
                    <div class="cert-data">
                        <div class="cert-data-label">Detectadas</div>
                        <div class="cert-data-value empty" id="preview-anomalias">(completar)</div>
                    </div>
                    <div class="cert-data" id="cert-detalle-anomalias" style="display: none;">
                        <div class="cert-data-label">Detalle</div>
                        <div class="cert-data-value" id="preview-detalle_anomalias"></div>
                    </div>
                </div>

                <div class="cert-footer">
                    <div>Etapa 1 de 3 - Inspección</div>
                    <div style="margin-top: 0.25rem; font-size: 0.6rem;">Actualizado en vivo</div>
                </div>

                <div class="progress-simple">
                    <div class="progress-simple-bar"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once("../index/footer.php") ?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('[data-preview]');
        inputs.forEach(input => {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        });
    });

    function updatePreview(event) {
        const input = event.target;
        console.log(input);
        const previewId = input.getAttribute('data-preview');
        console.log(previewId);
        const preview = document.getElementById(previewId);

        if (preview) {
            const value = input.value || '';
            if (value.trim() === '') {
                preview.textContent = '(completar)';
                preview.classList.add('empty');
            } else {
                preview.textContent = value;
                preview.classList.remove('empty');
            }
        }
    }
</script>