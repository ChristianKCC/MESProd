<?php require_once("../index/header.php"); ?>

<link rel="stylesheet" href="css/estilos.css">
<div class="container p-4">
    <h5 class="tittlecont">Certificados de Calidad</h5>
    <div class="row">
        <div class="col-12">
            <small class="alert alert-info d-inline-block">
                Desde este apartado crea y realiza tus certificados para líquidos y formulados
            </small>
        </div>
    </div>

    <!-- Tabs discretos -->
    <ul class="nav cf-tabs mt-2" id="cfTabs">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabEspacio"
                type="button"><i class="fa-solid fa-inbox me-1"></i> Mi espacio</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabFolios" type="button"><i
                    class="fa-solid fa-circle-plus me-1"></i> Iniciar certificado</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCerts" type="button"><i
                    class="fa-regular fa-file-pdf me-1"></i> Generar / Ver certificados</button></li>
        <li class="nav-item">
            <button class="nav-link d-none" id="tabBtnConfig" data-bs-toggle="tab" data-bs-target="#tabConfig"
                type="button">
                <i class="fa-solid fa-gear me-1"></i> Configuraciones
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link d-none" id="tabBtnLiberacion" data-bs-toggle="tab" data-bs-target="#tabLiberacion"
                type="button">
                <i class="fa-solid fa-truck-fast me-1"></i> Liberación por folio
            </button>
        </li>
    </ul>

    <!-- IMPORTANTE: este contenedor .tab-content es el que hace que solo se vea un pane -->
    <div class="tab-content card card-body">

        <!-- MI ESPACIO -->
        <div class="tab-pane fade show active" id="tabEspacio">
            <div class="cf-toolbar">
                <div class="cf-card-title"><i class="fa-solid fa-inbox"></i> Certificaciones pendientes </div>
                <div class="cf-buscar"><input type="text" class="form-control form-control-sm" id="buscarEspacio"
                        placeholder="Buscar folio, clave o producto…"></div>
            </div>

            <div id="listaEspacio" class="cf-lista" style="max-height:640px; overflow-y:auto; padding-right:.3rem;">
            </div>
        </div>

        <!-- INICIAR -->
        <div class="tab-pane fade" id="tabFolios">
            <div class="cf-toolbar">
                <div class="cf-card-title"><i class="fa-solid fa-circle-plus"></i> Folios de bajadas sin certificado
                </div>
                <div class="cf-buscar"><input type="text" class="form-control form-control-sm" id="buscarFolios"
                        placeholder="Buscar folio o clave…"></div>
            </div>
            <div class="table-responsive" style="max-height:520px;">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>FECHA</th>
                            <th>MÁQUINA</th>
                            <th>FOLIO</th>
                            <th>TURNO</th>
                            <th>CLAVE</th>
                            <th>PRODUCTO</th>
                            <th>PALETS</th>
                            <th>ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody id="tblFolios"></tbody>
                </table>
            </div>
        </div>

        <!-- CERTIFICADOS -->
        <div class="tab-pane fade" id="tabCerts">
            <div class="cf-toolbar">
                <div class="cf-card-title"><i class="fa-regular fa-file-pdf"></i> Certificados</div>
                <div class="cf-buscar"><input type="text" class="form-control form-control-sm" id="buscarCerts"
                        placeholder="Buscar folio, clave o estatus…"></div>
            </div>
            <div class="table-responsive" style="max-height:820px;">
                <table class="table table-bordered align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>FOLIO</th>
                            <th>CLAVE</th>
                            <th>PRODUCTO</th>
                            <th>EMISIÓN</th>
                            <th>ESTATUS</th>
                            <th>GERENTE / FIRMA</th>
                            <th>ACCIÓN</th>
                        </tr>
                    </thead>
                    <tbody id="tblCerts"></tbody>
                </table>
            </div>
        </div>

        <!-- Configuraciones -->
        <div class="tab-pane fade" id="tabConfig">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="list-group cfg-menu" id="cfgMenu">
                        <button class="list-group-item list-group-item-action active" data-sec="parametros">
                            <i class="fa-solid fa-sliders me-2"></i> Parámetros por clave
                        </button>
                        <button class="list-group-item list-group-item-action" data-sec="organolepticas">
                            <i class="fa-solid fa-eye me-2"></i> Organolépticas
                        </button>
                        <button class="list-group-item list-group-item-action" data-sec="defectos">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> Defectos
                        </button>
                        <button class="list-group-item list-group-item-action" data-sec="mo">
                            <i class="fa-solid fa-bacterium me-2"></i> Microorganismos (MO)
                        </button>
                        <button class="list-group-item list-group-item-action" data-sec="opciones">
                            <i class="fa-solid fa-list-check me-2"></i> Opciones de resultado
                        </button>
                        <button class="list-group-item list-group-item-action" data-sec="perfiles">
                            <i class="fa-solid fa-user-shield me-2"></i> Roles y permisos
                        </button>
                        <button class="list-group-item list-group-item-action" data-sec="claves">
                            <i class="fa-solid fa-clipboard-check me-2"></i> Estado por clave
                        </button>
                        <button class="list-group-item list-group-item-action" data-sec="bitacora">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i> Bitácora
                        </button>
                    </div>
                </div>
                <div class="col-md-9">
                    <div id="cfgContenido"></div>
                </div>
            </div>
        </div>

        <!-- Liberaciones y/o estatus -->
        <div class="tab-pane fade" id="tabLiberacion">
            <div class="cf-toolbar">
                <div>
                    <div class="cf-card-title mb-1">
                        <i class="fa-solid fa-truck-fast"></i> Estado de liberación por folio
                    </div>
                    <small class="text-muted">
                        Un renglón por folio y clave, sin importar cuántos palets tenga.
                    </small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <input type="date" class="form-control form-control-sm" id="libDesde" style="max-width:160px;"
                        title="Desde esta fecha de producción">
                    <div class="cf-buscar">
                        <input type="text" class="form-control form-control-sm" id="buscarLiberacion"
                            placeholder="Buscar folio, clave o producto…">
                    </div>
                </div>
            </div>
            <div id="libContenido"></div>
        </div>
    </div>

    <div class="tab-pane fade" id="tabLiberacion">
        <div class="cf-toolbar">
            <div>
                <div class="cf-card-title mb-1">
                    <i class="fa-solid fa-truck-fast"></i> Estado de liberación por folio
                </div>
                <small class="text-muted">
                    Un renglón por folio y clave, sin importar cuántos palets tenga.
                </small>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <input type="date" class="form-control form-control-sm" id="libDesde" style="max-width:160px;"
                    title="Desde esta fecha de producción">
                <div class="cf-buscar">
                    <input type="text" class="form-control form-control-sm" id="buscarLiberacion"
                        placeholder="Buscar folio, clave o producto…">
                </div>
            </div>
        </div>
        <div id="libContenido"></div>
    </div>
</div>

<!-- data-bs-focus="false" evita que el modal robe el foco y bloquee el input de SweetAlert -->
<div class="modal fade" id="modalEtapa" tabindex="-1" data-bs-focus="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="etapaTitulo"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="etapaBody"></div>
            <div class="modal-footer" id="etapaFooter"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCert" tabindex="-1" data-bs-focus="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Certificado de Calidad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="certHoja" class="cert-hoja"></div>
            </div>
            <div class="modal-footer" id="certFooter"></div>
        </div>
    </div>
</div>

<!-- ---------- 2) Modal de resumen y selección de palets ---------- -->
<div class="modal fade" id="modalGrupo" tabindex="-1" data-bs-focus="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-list-check me-1"></i>
                    Resumen del folio — <span id="grupoTitulo"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="grupoResumen"></div>

                <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                    <div class="cf-card-title mb-0">
                        <i class="fa-solid fa-layer-group"></i> Palets del folio
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="grupoTodos"
                            onchange="cfMarcarTodos(this.checked)">
                        <label class="form-check-label" for="grupoTodos">Integrar todos</label>
                    </div>
                </div>

                <div class="table-responsive" style="max-height:320px;">
                    <table class="table table-sm table-bordered align-middle text-center mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:40px;"></th>
                                <th>PALET</th>
                                <th>FECHA</th>
                                <th>HORA</th>
                                <th>CAJAS</th>
                                <th>ESTADO</th>
                            </tr>
                        </thead>
                        <tbody id="grupoPalets"></tbody>
                    </table>
                </div>

                <div class="alert alert-info py-2 mt-3 mb-0" id="grupoConteo"></div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-rectangle-xmark"></i> Cerrar
                </button>
                <button class="btn btn-primary" id="btnIniciarGrupo" onclick="cfIniciarGrupo()">
                    <i class="fa-solid fa-circle-play"></i> Iniciar certificado
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Configuraciones -->
<div class="modal fade" id="modalConfig" tabindex="-1" data-bs-focus="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cfgFormTitulo"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cfgFormId">
                <div id="cfgFormBody"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-rectangle-xmark"></i> Cancelar
                </button>
                <button class="btn btn-primary" onclick="cfgGuardar()">
                    <i class="fa-solid fa-floppy-disk"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRechazo" tabindex="-1" data-bs-focus="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#8c1d1d,#b02a2a);">
                <h5 class="modal-title">
                    <i class="fa-solid fa-ban me-1"></i>
                    Rechazar material — <span id="rechazoTitulo"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="rechazoResumen"></div>

                <div class="d-flex justify-content-between align-items-center mt-3 mb-2">
                    <div class="cf-card-title mb-0">
                        <i class="fa-solid fa-pallet"></i> Palets a rechazar
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rechazoTodos"
                            onchange="cfMarcarTodosRechazo(this.checked)">
                        <label class="form-check-label" for="rechazoTodos">Seleccionar todos</label>
                    </div>
                </div>

                <div class="table-responsive" style="max-height:280px;">
                    <table class="table table-sm table-bordered align-middle text-center mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th style="width:40px;"></th>
                                <th>PALET</th>
                                <th>FECHA</th>
                                <th>HORA</th>
                                <th>CAJAS</th>
                                <th>ESTADO</th>
                            </tr>
                        </thead>
                        <tbody id="rechazoPalets"></tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <label class="form-label">Motivo del rechazo <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="rechazoMotivo" rows="2"
                        placeholder="Describe por qué se rechaza el material…"></textarea>
                    <div class="form-text">
                        Se guarda en la bitácora y se muestra en la vista de liberación por folio.
                    </div>
                </div>

                <div class="alert alert-secondary py-2 mt-3 mb-0" id="rechazoConteo" style="font-size:.85rem;"></div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fa-solid fa-rectangle-xmark"></i> Cancelar
                </button>
                <button class="btn btn-danger" id="btnRechazarOrigen" onclick="cfRechazarOrigen()">
                    <i class="fa-solid fa-ban"></i> Rechazar material
                </button>
            </div>
        </div>
    </div>
</div>


<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script> -->
<script src="js/certificados.js"></script>
<script src="js/certificados_config.js"></script>
<script src="js/certificados_liberacion.js"></script>

<script>
    // js/certificados_ui.js — complementos del rediseño (cárgalo DESPUÉS de certificados.js)
    // No modifica la lógica: envuelve las funciones existentes para actualizar
    // contadores, nombre de usuario, buscadores y estados vacíos.

    (function () {
        // ---- Nombre de usuario en el hero ----
        const _arranque = cfArranque;
        // cfArranque ya corre en DOMContentLoaded; solo pintamos el nombre cuando el perfil exista
        const pintaUsuario = setInterval(() => {
            if (CF_PERFIL && CF_PERFIL.nombre) {
                const el = document.getElementById("cfUsuario");
                if (el) el.innerHTML = `<i class="fa-regular fa-user"></i> ${cfEsc(CF_PERFIL.nombre)}`;
                clearInterval(pintaUsuario);
            }
        }, 400);

        // ---- Contadores del resumen ----
        async function cfActualizarStats() {
            try {
                const [p, c] = await Promise.all([
                    cfApi({ accion: "pendientes" }),
                    cfApi({ accion: "certificados" }),
                ]);
                const items = p.ok ? p.items : [];
                const certs = c.ok ? c.items : [];
                const set = (id, v) => { const e = document.getElementById(id); if (e) e.textContent = v; };
                set("stPend", items.filter(i => i.tipo === "captura").length);
                set("stAut", items.filter(i => i.tipo === "autorizacion").length);
                set("stListo", certs.filter(x => x.estatus === "LISTO" || x.estatus === "ENVIADO_GT").length);
                set("stAprob", certs.filter(x => x.estatus === "APROBADO").length);
            } catch (e) { /* silencioso */ }
        }

        // Envolver las cargas para refrescar stats después de cada una
        const _espacio = cfCargarEspacio;
        cfCargarEspacio = async function () { await _espacio(); cfActualizarStats(); };
        const _certs = cfCargarCerts;
        cfCargarCerts = async function () { await _certs(); cfActualizarStats(); };

        // ---- Estados vacíos con icono (reemplaza los "Sin..." planos) ----
        const OBS = new MutationObserver(() => {
            document.querySelectorAll("#tblEspacio, #tblFolios, #tblCerts").forEach((tb) => {
                const tr = tb.querySelector("tr td[colspan]");
                if (tr && !tr.querySelector(".cf-vacio")) {
                    const txt = tr.textContent.trim();
                    tr.innerHTML = `<div class="cf-vacio"><i class="fa-regular fa-folder-open"></i>${txt}</div>`;
                }
            });
        });
        OBS.observe(document.body, { childList: true, subtree: true });

        // ---- Buscadores por tabla ----
        function filtro(inputId, tbodyId) {
            const inp = document.getElementById(inputId);
            if (!inp) return;
            inp.addEventListener("input", () => {
                const q = inp.value.toLowerCase();
                document.querySelectorAll(`#${tbodyId} tr`).forEach((tr) => {
                    if (tr.querySelector("td[colspan]")) return; // fila de vacío
                    tr.style.display = tr.textContent.toLowerCase().includes(q) ? "" : "none";
                });
            });
        }
        filtro("buscarEspacio", "tblEspacio");
        filtro("buscarFolios", "tblFolios");
        filtro("buscarCerts", "tblCerts");
    })();
</script>
<?php require_once("../index/footer.php") ?>