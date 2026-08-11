<!-- Identificador de pagina a cargar -->
<div id="paginaWR" class="p-4">
    <div>

        <div class="card card-body">
            <h5 class="tittlecont">BITACORA - WASTE RECLAIM</h5>
            <!-- · Form - 61765 -->
            <div class="wrap">

                <!-- Input ocultopara recuperacion de turno activo y lectura en seleccion de datos para los turnos -->
                <div class="page-head">
                    <div class="d-flex align-items-end gap-3">
                        <div>
                            <input hidden id="turno_activo" name="turno_activo" form="bitacora" value="1">
                        </div>
                    </div>
                </div>

                <!-- Pestañas de bitacora -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab1" type="button">
                            ① Tiempos de Operación y Tiempos Pérdidos
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab3" type="button">
                            ② Peso de Bolsas
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab4" type="button">
                            ③ Reporte de Operación
                        </button>
                    </li>
                </ul>

                <!-- Inicio de captura de datos -->
                <form method="post" id="bitacora">
                    <!-- Botones de captura de formulario  -->
                    <div class="inner">
                        <div class="d-flex gap-2">
                            <button type="reset" class="btn btn-secondary">
                                <i class="fa-solid fa-eraser"></i> Limpiar todas las pantallas
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Guardar turno
                            </button>
                        </div>
                    </div>
                    <br />

                    <div class="tab-content">

                        <!-- ============================================================= -->
                        <!-- TAB 1 · TIEMPOS DE OPERACIÓN Y TIEMPOS PÉRDIDOS               -->
                        <!-- ============================================================= -->
                        <div class="tab-pane fade show active" id="tab1">
                            <div class="grid">

                                <!-- 1 Equipos Disponibles -->
                                <?= tileOpen(1, 'Equipos disponibles') ?>
                                <table class="table table-tight table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Equipo</th>
                                            <th class="text-center">1ero.</th>
                                            <th class="text-center">2do.</th>
                                            <th class="text-center">3ero.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Recorrido de datos segun la configuracion de equipos disponibles (../WR/catalogos.php desde bitacora.php) -->
                                        <?php foreach ($EQUIPOS_DISPONIBLE as $eq):
                                            if ($eq === '')
                                                continue;
                                            $k = slug($eq); ?>
                                                <tr>
                                                    <td class="eqname"><?= htmlspecialchars($eq) ?></td>
                                                    <td><?= inp("disponible[$k][1ero]", 'data-col="disp1"') ?></td>
                                                    <td><?= inp("disponible[$k][2do]", 'data-col="disp2"') ?></td>
                                                    <td><?= inp("disponible[$k][3ero]", 'data-col="disp3"') ?></td>
                                                </tr>
                                        <?php endforeach; ?>
                                        <tr class="total-cell table-light">
                                            <td class="eqname">TOTAL</td>
                                            <td><?= inp("disponible_total[1ero]", 'data-total="disp1" readonly') ?></td>
                                            <td><?= inp("disponible_total[2do]", 'data-total="disp2" readonly') ?></td>
                                            <td><?= inp("disponible_total[3ero]", 'data-total="disp3" readonly') ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?= tileClose() ?>

                                <!-- 2 Recuperado (captura manual) -->
                                <?= tileOpen(2, 'Recuperado (% y kilogramos)', true) ?>
                                <p class="form-text mb-2">Captura el % y los kilogramos recuperados por equipo y turno.
                                    Los totales por columna y por equipo se calculan solos.</p>
                                <div class="table-responsive">
                                    <table class="table table-tight table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th rowspan="2" class="align-middle">Equipo</th>
                                                <th colspan="3" class="text-center">Porcentaje %</th>
                                                <th colspan="3" class="text-center">Kilogramos</th>
                                                <th rowspan="2" class="align-middle text-center">Total kg</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center">1ero.</th>
                                                <th class="text-center">2do.</th>
                                                <th class="text-center">3ero.</th>
                                                <th class="text-center">1ero.</th>
                                                <th class="text-center">2do.</th>
                                                <th class="text-center">3ero.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Recorrido de datos segun los equipos recuperados configurados desde (../WR/catalogos.php desde bitacora.php) -->
                                            <?php foreach ($EQUIPOS_RECUPERADO as $eq):
                                                $k = slug($eq); ?>
                                                    <tr>
                                                        <td class="eqname"><?= htmlspecialchars($eq) ?></td>
                                                        <td><?= inp("recup_pct[$k][1ero]", 'data-colpct="pctr1"') ?></td>
                                                        <td><?= inp("recup_pct[$k][2do]", 'data-colpct="pctr2"') ?></td>
                                                        <td><?= inp("recup_pct[$k][3ero]", 'data-colpct="pctr3"') ?></td>
                                                        <td><?= inp("recup_kg[$k][1ero]", "data-sum=\"recup_kg_$k\" data-colkg=\"kg1\"") ?>
                                                        </td>
                                                        <td><?= inp("recup_kg[$k][2do]", "data-sum=\"recup_kg_$k\" data-colkg=\"kg2\"") ?>
                                                        </td>
                                                        <td><?= inp("recup_kg[$k][3ero]", "data-sum=\"recup_kg_$k\" data-colkg=\"kg3\"") ?>
                                                        </td>
                                                        <td class="total-cell">
                                                            <?= inp("recup_total[$k]", "data-total=\"recup_kg_$k\" data-gtotal=\"kgtot\" readonly") ?>
                                                        </td>
                                                    </tr>
                                            <?php endforeach; ?>
                                            <tr class="total-cell table-light">
                                                <td class="eqname">TOTAL</td>
                                                <td><?= inp("recup_pct_total[1ero]", 'data-total="pctr1" readonly') ?>
                                                </td>
                                                <td><?= inp("recup_pct_total[2do]", 'data-total="pctr2" readonly') ?>
                                                </td>
                                                <td><?= inp("recup_pct_total[3ero]", 'data-total="pctr3" readonly') ?>
                                                </td>
                                                <td><?= inp("recup_kg_total[1ero]", 'data-total="kg1" readonly') ?></td>
                                                <td><?= inp("recup_kg_total[2do]", 'data-total="kg2" readonly') ?></td>
                                                <td><?= inp("recup_kg_total[3ero]", 'data-total="kg3" readonly') ?></td>
                                                <td><?= inp("recup_total_general", 'data-total="kgtot" readonly') ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?= tileClose() ?>

                                <!-- 3 Recibidas -->
                                <?= tileOpen(3, 'Pacas recibidas (por planta)') ?>
                                <table class="table table-tight table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Planta</th>
                                            <th class="text-center">1er turno</th>
                                            <th class="text-center">2do turno</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($PLANTAS as $p):
                                            $k = slug($p); ?>
                                                <tr>
                                                    <td class="eqname"><?= htmlspecialchars($p) ?></td>
                                                    <td><?= inp("pacas_recibidas[$k][1er]") ?></td>
                                                    <td><?= inp("pacas_recibidas[$k][2do]") ?></td>
                                                </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?= tileClose() ?>

                                <!-- 4 Alimentadas -->
                                <?= tileOpen(4, 'Pacas alimentadas (por planta)') ?>
                                <div class="table-responsive">
                                    <!-- Cabeceras principales para PACAS ALIMENTADAS (POR PLANTA) -->
                                    <table class="table table-tight table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Planta</th>
                                                <th class="text-center">1ero.</th>
                                                <th class="text-center">2do.</th>
                                                <th class="text-center">3ero.</th>
                                                <th class="text-center">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Recorrido de datos segun las plantas configuradas en (../WR/catalogos.php desde bitacora.php) -->
                                            <?php foreach ($PLANTAS as $p):
                                                $k = slug($p); ?>
                                                    <tr>
                                                        <td class="eqname"><?= htmlspecialchars($p) ?></td>
                                                        <td><?= inp("pacas_alimentadas[$k][1ero]", "data-sum=\"alim_$k\"") ?>
                                                        </td>
                                                        <td><?= inp("pacas_alimentadas[$k][2do]", "data-sum=\"alim_$k\"") ?>
                                                        </td>
                                                        <td><?= inp("pacas_alimentadas[$k][3ero]", "data-sum=\"alim_$k\"") ?>
                                                        </td>
                                                        <td class="total-cell">
                                                            <?= inp("pacas_alimentadas[$k][total]", "data-total=\"alim_$k\" readonly") ?>
                                                        </td>
                                                    </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?= tileClose() ?>

                                <!-- 5 Presión compactadores -->
                                <?= tileOpen(5, 'Presión de los compactadores') ?>
                                <div class="table-responsive">
                                    <p class="form-text mb-2">Valores ideales precargados (ajusta si varía). Si dejas en
                                        vacío = <strong>M.P.</strong> (Máquina Parada) al guardar.</p>
                                    <table class="table table-tight table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Turno</th>
                                                <!-- RECORRIDO DE ELEMENTOS PARA COLUMNAS DE PRESION RECUPERADOS DE (../WR/catalogos.php desde bitacora.php) -->
                                                <?php foreach ($COLS_PRESION as $c): ?>
                                                        <th class="text-center"><?= htmlspecialchars($c) ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (['1ERO', '2DO', '3ERO'] as $t):
                                                $tk = slug($t); ?>
                                                    <tr>
                                                        <td class="eqname"><?= $t ?></td>
                                                        <?php foreach ($COLS_PRESION as $ck => $cv):
                                                            $def = $PRESION_DEFAULT[$ck] ?? ''; ?>
                                                                <td><?= inp("presion[$tk][$ck]", 'class="form-control form-control-sm text-center js-presion" placeholder="M.P." value="' . htmlspecialchars($def) . '"') ?>
                                                                </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?= tileClose() ?>

                                <!-- 6 SAM recuperado -->
                                <?= tileOpen(6, 'SAM recuperado (kgs.)') ?>
                                <p class="form-text mb-2">Captura los valores del turno. Usa <strong>“+ Agregar
                                        columna”</strong> si necesitas agregar una columna mas. La fila TOTAL suma los
                                    tres turnos por columna.</p>
                                <div class="table-responsive">
                                    <table class="table table-tight table-hover mb-0" id="tablaSam">
                                        <thead class="table-dark">
                                            <tr id="samHead">
                                                <th>Turno</th>
                                                <th class="text-center sam-col">Valor 1</th>
                                                <th class="text-center sam-col">Valor 2</th>
                                                <th class="text-center sam-col">Valor 3</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Recorrido de datos para integrar las columnas segun las filas indicadas -->
                                            <?php foreach (['1ERO', '2DO', '3ERO', 'TOTAL'] as $t):
                                                $tk = slug($t); ?>
                                                    <tr data-sam-turno="<?= $tk ?>">
                                                        <td class="eqname"><?= $t ?></td>
                                                        <td><?= inp("sam_recuperado[$tk][c1]") ?></td>
                                                        <td><?= inp("sam_recuperado[$tk][c2]") ?></td>
                                                        <td><?= inp("sam_recuperado[$tk][c3]") ?></td>
                                                    </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Botones para generacion de columnas nuevas en caso de requerirlas -->
                                <div class="d-flex gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="samAgregaColumnas">
                                        <i class="fa-solid fa-plus"></i> Agregar columna
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="samDelCol">
                                        <i class="fa-solid fa-minus"></i> Quitar columna
                                    </button>
                                </div>
                                <?= tileClose() ?>

                                <!-- 7 Orden y limpieza (Limpio/Sucio) -->
                                <?= tileOpen(7, 'Orden y limpieza', true) ?>
                                <div class="row">
                                    <div class="col-lg-8">
                                        <div class="table-responsive">
                                            <table class="table table-tight table-hover mb-0 align-middle">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Área</th>
                                                        <th class="text-center">1er turno</th>
                                                        <th class="text-center">2do turno</th>
                                                        <th class="text-center">3er turno</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Recorrido de datos segun los elementos de limpieza cargados desde (../WR/catalogos.php desde bitacora.php) -->
                                                    <?php foreach ($AREAS_LIMPIEZA as $a):
                                                        $k = slug($a); ?>
                                                            <tr>
                                                                <td class="eqname small"><?= htmlspecialchars($a) ?></td>
                                                                <?php foreach (['1ero', '2do', '3ero'] as $t): ?>
                                                                        <td>
                                                                            <select name="orden[<?= $k ?>][<?= $t ?>]"
                                                                                class="form-select form-select-sm">
                                                                                <option value="">—</option>
                                                                                <option value="LIMPIO">Limpio</option>
                                                                                <option value="SUCIO">Sucio</option>
                                                                            </select>
                                                                        </td>
                                                                <?php endforeach; ?>
                                                            </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Columna de quien inspecciono el orden y limpieza -->
                                    <div class="col-lg-4">
                                        <div class="grp-title">Inspeccionó / Nombre</div>
                                        <?php foreach (['1ERO', '2DO', '3ERO'] as $t):
                                            $tk = slug($t); ?>
                                                <label class="tlbl"><?= $t ?> turno</label>
                                                <input type="text" name="inspecciono[<?= $tk ?>]"
                                                    class="form-control form-control-sm mb-2" autocomplete="off">
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?= tileClose() ?>

                                <!-- 8 Pacas merma (Basura / Recorte / 100% Toalla) -->
                                <?= tileOpen(8, 'Pacas merma', true) ?>
                                <p class="form-text mb-2">Captura kilos y pacas por turno. Totales por turno (fila
                                    TOTAL) y por concepto (columna Total) automáticos.</p>
                                <div class="table-responsive">
                                    <table class="table table-tight mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th rowspan="2" class="align-middle">Concepto</th>
                                                <th colspan="2" class="text-center">1er turno</th>
                                                <th colspan="2" class="text-center">2do turno</th>
                                                <th colspan="2" class="text-center">3er turno</th>
                                                <th colspan="2" class="text-center">Total</th>
                                            </tr>
                                            <tr>
                                                <th class="text-center">Kilos</th>
                                                <th class="text-center">Pacas</th>
                                                <th class="text-center">Kilos</th>
                                                <th class="text-center">Pacas</th>
                                                <th class="text-center">Kilos</th>
                                                <th class="text-center">Pacas</th>
                                                <th class="text-center">Kilos</th>
                                                <th class="text-center">Pacas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (['BASURA' => 'Basura', 'RECORTE' => 'Recorte (pacas)', 'TOALLA' => '100% Toalla'] as $tk => $tl): ?>
                                                    <tr>
                                                        <td class="eqname"><?= $tl ?></td>
                                                        <?php foreach (['t1', 't2', 't3'] as $t): ?>
                                                                <td><?= inpNum("wr_pacasmerma[$tk][$t][kilos]", "data-sum=\"wrmermak_$tk\" data-mk=\"$t\"") ?>
                                                                </td>
                                                                <td><?= inpNum("wr_pacasmerma[$tk][$t][pacas]", "data-sum=\"wrmermap_$tk\" data-mp=\"$t\"") ?>
                                                                </td>
                                                        <?php endforeach; ?>
                                                        <td class="total-cell">
                                                            <?= inpNum("wr_pacasmerma[$tk][total][kilos]", "data-total=\"wrmermak_$tk\" readonly") ?>
                                                        </td>
                                                        <td class="total-cell">
                                                            <?= inpNum("wr_pacasmerma[$tk][total][pacas]", "data-total=\"wrmermap_$tk\" readonly") ?>
                                                        </td>
                                                    </tr>
                                            <?php endforeach; ?>
                                            <!-- Fila TOTAL vertical (por turno) + gran total -->
                                            <tr class="total-cell table-light">
                                                <td class="eqname">TOTAL</td>
                                                <?php foreach (['t1', 't2', 't3'] as $t): ?>
                                                        <td><input type="text" class="form-control form-control-sm text-center"
                                                                data-mktot="<?= $t ?>" readonly></td>
                                                        <td><input type="text" class="form-control form-control-sm text-center"
                                                                data-mptot="<?= $t ?>" readonly></td>
                                                <?php endforeach; ?>
                                                <td><input type="text" class="form-control form-control-sm text-center"
                                                        data-mktot="tot" readonly></td>
                                                <td><input type="text" class="form-control form-control-sm text-center"
                                                        data-mptot="tot" readonly></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <?= tileClose() ?>

                                <!-- 9 Merma máquinas (se alimenta del Peso de Bolsas) -->
                                <?= tileOpen(9, 'Merma máquinas (kilos)', true) ?>
                                <p class="form-text mb-2">Estos kilos se llenan solos con el <strong>total</strong> de
                                    la pestaña ② Peso de Bolsas (por turno).</p>
                                <div class="table-responsive">
                                    <table class="table table-tight mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Tipo</th>
                                                <th class="text-center">1er turno</th>
                                                <th class="text-center">2do turno</th>
                                                <th class="text-center">3er turno</th>
                                                <th class="text-center">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach (['PANAL' => 'Pañal', 'TOALLA' => 'Toalla'] as $tk => $tl): ?>
                                                    <tr>
                                                        <td class="eqname"><?= $tl ?></td>
                                                        <?php foreach (['t1', 't2', 't3'] as $t): ?>
                                                                <td class="linked">
                                                                    <?= inpNum("wr_merma[$tk][$t]", "data-wrmerma=\"{$tk}_{$t}\" data-sum=\"wrmerma_$tk\" readonly") ?>
                                                                </td>
                                                        <?php endforeach; ?>
                                                        <td class="total-cell">
                                                            <?= inpNum("wr_merma[$tk][total]", "data-total=\"wrmerma_$tk\" readonly") ?>
                                                        </td>
                                                    </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?= tileClose() ?>

                            </div>
                        </div>

                        <!-- ============================================================= -->
                        <!-- TAB 3 · PESO DE BOLSAS (Toalla / Pañal, dos columnas)          -->
                        <!-- ============================================================= -->
                        <div class="tab-pane fade" id="tab3">
                            <p class="form-text">Captura las bolsas pesadas por turno. Solo el turno activo es editable;
                                los demás se muestran de solo lectura. Usa <strong>“+ fila”</strong> si necesitas más.
                                El total se actualiza solo. Una celda admite una suma rápida tipo <code>5+10</code>.</p>
                            <div class="row g-3">
                                <!-- Toalla a la izquierda, Pañal a la derecha (se apilan en pantallas chicas) -->
                                <?php foreach ($PESO_TIPOS as $tk => $tl): ?>
                                        <div class="col-lg-6">
                                            <h3 class="grp-title mt-2" style="font-size:.95rem;color:var(--kc-accent)">
                                                <?= htmlspecialchars($tl) ?>
                                            </h3>
                                            <!-- Recorrido de datos para la generacion de turnos segun el turno activo -->
                                            <?php foreach (['t1' => '1er turno', 't2' => '2do turno', 't3' => '3er turno'] as $t => $tlabel):
                                                $key = "{$tk}_{$t}"; ?>
                                                    <?= tileOpen((int) substr($t, 1), $tlabel, false, 'data-turno-tile="' . substr($t, 1) . '"') ?>
                                                    <table class="table table-tight mb-2">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th style="width:48px">No.</th>
                                                                <th>Peso</th>
                                                            </tr>
                                                        </thead>
                                                        <!-- Generacion del cuerpo de datos para las filas dinamicas -->
                                                        <tbody data-peso="<?= $key ?>">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                    <tr>
                                                                        <td class="text-center text-muted"><?= $i ?></td>
                                                                        <td><?= inpNum("peso[$tk][$t][]", 'data-pesocell="' . $key . '"') ?>
                                                                        </td>
                                                                    </tr>
                                                            <?php endfor; ?>
                                                        </tbody>
                                                        <!-- Obtencion de totales segun las filas indicadas -->
                                                        <tfoot>
                                                            <tr class="total-cell">
                                                                <td class="text-end eqname">Total</td>
                                                                <td><?= inpNum("peso_total[$tk][$t]", "data-pesototal=\"$key\" readonly") ?>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                    <!-- Botones dinamicos para la generacion/eliminacion de nuevas filas -->
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            data-add="<?= $key ?>" data-tipo="<?= $tk ?>" data-turno="<?= $t ?>">
                                                            <i class="fa-solid fa-plus"></i>
                                                            Agregar fila
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            data-del="<?= $key ?>">
                                                            <i class="fa-solid fa-minus"></i>
                                                            Quitar fila
                                                        </button>
                                                    </div>
                                                    <?= tileClose() ?>
                                            <?php endforeach; ?>
                                        </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- ============================================================= -->
                        <!-- TAB 4 · REPORTE DE OPERACIÓN (Waste Reclaim)                  -->
                        <!-- ============================================================= -->
                        <div class="tab-pane fade" id="tab4">
                            <div class="tile">
                                <div class="tile-head"><span class="step">1</span>
                                    <h2>Reporte de operación</h2>
                                </div>
                                <div class="tile-body">
                                    <p class="form-text">La fecha se toma de la jornada (<?= $hoy ?>). Llena el turno
                                        que corresponda en los siguientes campos:</p>
                                    <ul class="nav nav-tabs mb-3" role="tablist">
                                        <?php foreach (['1' => '1er turno', '2' => '2do turno', '3' => '3er turno'] as $n => $lbl): ?>
                                                <li class="nav-item"><button class="nav-link <?= $n === '1' ? 'active' : '' ?>"
                                                        data-bs-toggle="pill" data-bs-target="#rep<?= $n ?>"
                                                        type="button"><?= $lbl ?></button></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="tab-content">
                                        <!-- Generacion de contenidos segun el turno -->
                                        <?php foreach (['1', '2', '3'] as $n):
                                            $tk = "t$n"; ?>
                                                <div class="tab-pane fade <?= $n === '1' ? 'show active' : '' ?>"
                                                    id="rep<?= $n ?>">
                                                    <div class="row g-3">
                                                        <div class="col-md-4"><label class="tlbl">Operador</label><input
                                                                type="text" name="reporte[<?= $tk ?>][operador]"
                                                                class="form-control form-control-sm"></div>
                                                        <div class="col-md-4"><label class="tlbl">Ayudante</label><input
                                                                type="text" name="reporte[<?= $tk ?>][ayudante]"
                                                                class="form-control form-control-sm"></div>
                                                        <div class="col-md-4"><label class="tlbl">Línea Confort</label><input
                                                                type="text" name="reporte[<?= $tk ?>][linea_confort]"
                                                                class="form-control form-control-sm"></div>
                                                        <div class="col-md-4"><label class="tlbl">Supervisor</label><input
                                                                type="text" name="reporte[<?= $tk ?>][supervisor]"
                                                                class="form-control form-control-sm"></div>
                                                        <div class="col-md-4"><label class="tlbl">Trabajos
                                                                Diversos</label><input type="text"
                                                                name="reporte[<?= $tk ?>][trabajos_diversos]"
                                                                class="form-control form-control-sm"></div>
                                                        <div class="col-12"><label class="tlbl">Comentarios</label><textarea
                                                                name="reporte[<?= $tk ?>][comentarios]"
                                                                class="form-control form-control-sm" rows="6"></textarea></div>
                                                    </div>
                                                </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /tab-content -->

                    <?php if ($guardado !== null): ?>
                            <div class="alert alert-success mt-3">
                                <strong>Datos recibidos (vista previa):</strong>
                                <pre class="mb-0"
                                    style="max-height:260px;overflow:auto;"><?php echo htmlspecialchars(print_r($guardado, true)); ?></pre>
                            </div>
                    <?php endif; ?>


                </form>
            </div>
        </div>
    </div>
</div>