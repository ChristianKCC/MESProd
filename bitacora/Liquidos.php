<div id="paginaLiquidos" class="p-4">
    <div class="card card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="tittlecont mb-0">Bitácora de registro - Formulados</h5>
            <!-- <button type="button" id="btnConfigImpresora" class="btn btn-sm btn-outline-success ms-2"
                data-bs-toggle="modal" data-bs-target="#modalImpresora"> <i class="fa-solid fa-print"></i>
                Configurar impresora Zebra <i class="fa-solid fa-arrow-right-long"></i> <i
                    class="fa-solid fa-horse-head"></i>
            </button> -->

            <button type="button" id="btnConfigImpresora" class="btn btn-sm btn-outline-success ms-2 d-none"
                data-bs-toggle="modal" data-bs-target="#modalImpresora">
                <i class="fa-solid fa-print"></i>
                Configurar impresora Zebra <i class="fa-solid fa-arrow-right-long"></i> <i
                    class="fa-solid fa-horse-head"></i>
            </button>
        </div>


        <div class="ls-sub"><code>Ingresa la cantidad de 'Cajas' y presiona 'Nueva Etiqueta' — AcumR y USTD se
            calculan solos.</code></div>
        <br />

        <!-- Renderizacion de presentaciones hasta 4 -->
        <div class="ls-bloques"></div>

        <div id="ls-toast" class="ls-toast"></div>
    </div>
</div>

<!-- Plantilla de un bloque con su tabla -->
<template id="ls-tpl-bloque">
    <div class="ls-bloque" data-fijado="0">
        <div class="ls-bloque-head">
            <span class="ls-bloque-num"></span>
            <span class="ls-bloque-prod">Sin producto</span>
        </div>
        <select class="ls-sel">
            <option value="">Elige un producto</option>
        </select>
        <div class="ls-bloque-row">
            <div class="ls-fld"><label>Cajas</label><input class="ls-cajas" type="number" min="0" placeholder="0"></div>
            <button class="ls-btn ls-accent ls-bajar"><i class="fa-solid fa-circle-arrow-down"></i> Nueva
                Etiqueta</button>
            <button class="ls-btn ls-danger ls-cancelar" title="Cancelar producto y borrar sus bajadas"><i
                    class="fa-regular fa-trash-can"></i> Eliminar Presentación</button>
        </div>
        <div class="ls-bloque-calc">
            <span>AcumR: <b class="ls-c-acum">–</b></span>
            <span>USTD: <b class="ls-c-ustd">–</b></span>
        </div>
        <div class="ls-card ls-bloque-tabla">
            <table class="ls-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th># Palet</th>
                        <th>Folio</th>
                        <th>Hora</th>
                        <th>Cajas</th>
                        <th>Pzs</th>
                        <th>AcumR</th>
                        <th>USTD</th>
                        <th>Etiqueta</th>
                    </tr>
                </thead>
                <!-- <tbody class="ls-tbody">
                    <tr>
                        <td colspan="7" class="ls-vacio">Sin bajadas.</td>
                    </tr>
                </tbody> -->
                <tbody class="ls-tbody">
                    <tr>
                        <td colspan="9" class="ls-vacio">Sin bajadas.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<!-- Modal -->
<div class="modal fade" id="modalImpresora" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-print"></i> Config. de impresora en maquina
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="impresoraAlert" class="alert d-none" role="alert"></div>
                <div class="mb-3">
                    <span class="badge bg-secondary">Máquina: <span id="impMaquina">—</span></span>
                    <span id="impEstado" class="badge bg-warning text-dark">Sin configurar</span>
                </div>
                <div class="mb-3">
                    <label for="impHost" class="form-label">IP o nombre de la impresora</label>
                    <input type="text" class="form-control" id="impHost" placeholder="192.168.1.50 o ZEBRA-MAQ5"
                        autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="impPuerto" class="form-label">Puerto</label>
                    <input type="number" class="form-control" id="impPuerto" value="9100" min="1" max="65535">
                    <div class="form-text">Zebra usa 9100 (ZPL crudo) por defecto.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="btnProbarImp"> <i
                        class="fa-solid fa-play"></i> Probar
                    conexión</button>
                <button type="button" class="btn btn-primary" id="btnGuardarImp"> <i
                        class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </div>
        </div>
    </div>
</div>