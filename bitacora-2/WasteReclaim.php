<!-- Identificador de pagina a cargar -->
<div id="paginaWR" class="p-4">
    <div >
                            
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
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab2" type="button">
                        ② Producciones W.R.
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab3" type="button">
                        ③ Peso de Bolsas
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab4" type="button">
                        ④ Reporte de Operación
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
                        <thead class="table-dark"><tr><th>Equipo</th><th class="text-center">1ero.</th><th class="text-center">2do.</th><th class="text-center">3ero.</th></tr></thead>
                        <tbody>
                        <!-- Recorrido de datos segun la configuracion de equipos disponibles (../WR/catalogos.php desde bitacora.php) -->
                        <?php foreach ($EQUIPOS_DISPONIBLE as $eq): if ($eq==='') continue; $k=slug($eq); ?>
                            <tr>
                            <td class="eqname"><?= htmlspecialchars($eq) ?></td>
                            <td><?= inp("disponible[$k][1ero]", 'data-col="disp1"') ?></td>
                            <td><?= inp("disponible[$k][2do]",  'data-col="disp2"') ?></td>
                            <td><?= inp("disponible[$k][3ero]", 'data-col="disp3"') ?></td>
                            </tr>
                        <?php endforeach; ?>
                            <tr class="total-cell table-light"><td class="eqname">TOTAL</td>
                            <td><?= inp("disponible_total[1ero]",'data-total="disp1" readonly') ?></td>
                            <td><?= inp("disponible_total[2do]", 'data-total="disp2" readonly') ?></td>
                            <td><?= inp("disponible_total[3ero]",'data-total="disp3" readonly') ?></td>
                            </tr>
                        </tbody>
                        </table>
                    <?= tileClose() ?>

                    <!-- 2 Producción de pacas (locales/recorte enlazados desde W.R.) -->
                    <?= tileOpen(2, 'Producción de pacas') ?>
                        <div class="row">
                        
                            <!-- Descripcion de segunda tabl de PRODUCCION DE PACAS con pacas locales -->
                            <p class="form-text mb-2"> El <strong> # </strong> de pacas locales, recorte y exportación se llenan de ② Producciones W.R. con la tabla de Pacas Merma tomando los % de pacas.</p>
                            <div class="col-sm-6">                                
                                <div class="grp-title">Pacas locales <span class="badge-link"> W.R. recorte</span></div>
                                <div class="row g-2">
                                <?php foreach (['1ero','2do','3ero'] as $t): ?>
                                    <div class="col-4"><label class="tlbl"><?= strtoupper($t) ?></label>
                                    <?= inp("pacas_locales[$t]", "data-sum=\"pacas_locales\" data-from-recorte=\"$t\"") ?></div>
                                <?php endforeach; ?>
                                </div>
                                <label class="tlbl mt-2">Total</label><?= inp('pacas_locales_total','data-total="pacas_locales" readonly') ?>
                            </div>
                        
                            <!-- Descripcion de segunda tabl de PRODUCCION DE PACAS con pacas de recorte -->
                            <div class="col-sm-6">
                                <div class="grp-title">Pacas de recorte <span class="badge-link"> W.R. recorte</span></div>
                                <div class="row g-2">
                                <?php foreach (['1ero','2do','3ero'] as $t): ?>
                                    <div class="col-4"><label class="tlbl"><?= strtoupper($t) ?></label>
                                    <?= inp("pacas_recorte[$t]", "data-sum=\"pacas_recorte\" data-from-recorte=\"$t\"") ?></div>
                                <?php endforeach; ?>
                                </div>
                                <label class="tlbl mt-2">Total</label><?= inp('pacas_recorte_total','data-total="pacas_recorte" readonly') ?>
                            </div>
                        </div>

                        <hr class="my-3">
                        <!-- Descripcion de segunda tabl de PRODUCCION DE PACAS con pacas de exportación -->
                        <div class="grp-title">Pacas de exportación <span class="badge-link"> W.R. desechos</span></div>
                        <div class="table-responsive">
                            <table class="table table-tight mb-0">
                                <thead class="table-dark"><tr><th>Tipo</th><th class="text-center">1ero.</th><th class="text-center">2do.</th><th class="text-center">3ero.</th><th class="text-center">Total</th></tr></thead>
                                <tbody>
                                <tr><td class="eqname">100% Pañal</td>
                                    <?php foreach (['1ero','2do','3ero'] as $t): ?>
                                    <td><?= inp("export_panal[$t]", "data-sum=\"export_panal\" data-from-basura=\"$t\"") ?></td>
                                    <?php endforeach; ?>
                                    <td class="total-cell"><?= inp("export_panal_total",'data-total="export_panal" readonly') ?></td></tr>
                                <tr><td class="eqname">100% Toalla</td>
                                    <?php foreach (['1ero','2do','3ero'] as $t): ?>
                                    <td><?= inp("export_toalla[$t]", 'data-sum="export_toalla"') ?></td>
                                    <?php endforeach; ?>
                                    <td class="total-cell"><?= inp("export_toalla_total",'data-total="export_toalla" readonly') ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    <?= tileClose() ?>

                    <!-- 3 Recuperado -->
                    <?= tileOpen(3, 'Recuperado (% y kilogramos)', true) ?>
                        <p class="form-text mb-2">KG y % se llenan solos desde ② Producciones W.R. (Máquinas alimentadas), por equipo y turno.</p>
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
                            <?php foreach ($EQUIPOS_RECUPERADO as $eq): $k=slug($eq); ?>
                            <tr><td class="eqname"><?= htmlspecialchars($eq) ?></td>                                                            
                                <td class="linked"><?= inp("recup_pct[$k][1ero]", 'data-colpct="pctr1" readonly') ?></td>
                                <td class="linked"><?= inp("recup_pct[$k][2do]",  'data-colpct="pctr2" readonly') ?></td>
                                <td class="linked"><?= inp("recup_pct[$k][3ero]", 'data-colpct="pctr3" readonly') ?></td>

                                <td class="linked"><?= inp("recup_kg[$k][1ero]", "data-sum=\"recup_kg_$k\" data-colkg=\"kg1\" readonly") ?></td>
                                <td class="linked"><?= inp("recup_kg[$k][2do]",  "data-sum=\"recup_kg_$k\" data-colkg=\"kg2\" readonly") ?></td>
                                <td class="linked"><?= inp("recup_kg[$k][3ero]", "data-sum=\"recup_kg_$k\" data-colkg=\"kg3\" readonly") ?></td>
                                <td class="total-cell"><?= inp("recup_total[$k]", "data-total=\"recup_kg_$k\" data-gtotal=\"kgtot\" readonly") ?></td></tr>
                            <?php endforeach; ?>
                            <tr class="total-cell table-light"><td class="eqname">TOTAL</td>                                
                                <td><?= inp("recup_pct_total[1ero]", 'data-total="pctr1" readonly') ?></td>
                                <td><?= inp("recup_pct_total[2do]",  'data-total="pctr2" readonly') ?></td>
                                <td><?= inp("recup_pct_total[3ero]", 'data-total="pctr3" readonly') ?></td>

                                <td><?= inp("recup_kg_total[1ero]",'data-total="kg1" readonly') ?></td>
                                <td><?= inp("recup_kg_total[2do]", 'data-total="kg2" readonly') ?></td>
                                <td><?= inp("recup_kg_total[3ero]",'data-total="kg3" readonly') ?></td>
                                <td><?= inp("recup_total_general",'data-total="kgtot" readonly') ?></td></tr>
                            </tbody>
                        </table>
                        </div>
                    <?= tileClose() ?>

                    <!-- 4 Recibidas -->
                    <?= tileOpen(4, 'Pacas recibidas (por planta)') ?>
                        <table class="table table-tight table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Planta</th>
                                    <th class="text-center">1er turno</th>
                                    <th class="text-center">2do turno</th>
                                </tr>
                            </thead>
                        <tbody>
                        <?php foreach ($PLANTAS as $p): $k=slug($p); ?>
                            <tr>
                                <td class="eqname"><?= htmlspecialchars($p) ?></td>
                                <td><?= inp("pacas_recibidas[$k][1er]") ?></td>
                                <td><?= inp("pacas_recibidas[$k][2do]") ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        </table>
                    <?= tileClose() ?>

                    <!-- 5 Alimentadas -->
                    <?= tileOpen(5, 'Pacas alimentadas (por planta)') ?>
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
                            <?php foreach ($PLANTAS as $p): $k=slug($p); ?>
                            <tr><td class="eqname"><?= htmlspecialchars($p) ?></td>
                                <td><?= inp("pacas_alimentadas[$k][1ero]", "data-sum=\"alim_$k\"") ?></td>
                                <td><?= inp("pacas_alimentadas[$k][2do]",  "data-sum=\"alim_$k\"") ?></td>
                                <td><?= inp("pacas_alimentadas[$k][3ero]", "data-sum=\"alim_$k\"") ?></td>
                                <td class="total-cell"><?= inp("pacas_alimentadas[$k][total]", "data-total=\"alim_$k\" readonly") ?></td></tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?= tileClose() ?>

                    <!-- 6 Presión compactadores -->
                    <?= tileOpen(6, 'Presión de los compactadores') ?>
                        <div class="table-responsive">
                        <p class="form-text mb-2">Valores ideales precargados (ajusta si varía). Si dejas en vacío = <strong>M.P.</strong> (Máquina Parada) al guardar.</p>
                        <table class="table table-tight table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Turno</th>
                                    <!-- RECORRIDO DE ELEMENTOS PARA COLUMNAS DE PRESION RECUOPERADOS DE (../WR/catalogos.php desde bitacora.php) -->
                                    <?php foreach ($COLS_PRESION as $c): ?>
                                    <th class="text-center"><?= htmlspecialchars($c) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach (['1ERO','2DO','3ERO'] as $t): $tk=slug($t); ?>
                            <tr>
                                <td class="eqname"><?= $t ?></td>
                                <?php foreach ($COLS_PRESION as $ck=>$cv): $def = $PRESION_DEFAULT[$ck] ?? ''; ?>
                                <td><?= inp("presion[$tk][$ck]", 'class="form-control form-control-sm text-center js-presion" placeholder="M.P." value="'.htmlspecialchars($def).'"') ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                        
                    <?= tileClose() ?>
                    
                    <!-- 7 SAM recuperado -->
                    <?= tileOpen(7, 'SAM recuperado (kgs.)') ?>
                        <p class="form-text mb-2">Captura los valores del turno. Usa <strong>“+ Agregar columna”</strong> si necesitas agregar una columna mas.</p>
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
                                <?php foreach (['1ERO','2DO','3ERO','TOTAL'] as $t): $tk=slug($t); ?>
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

                    <!-- 8 Orden y limpieza (Limpio/Sucio) -->
                    <?= tileOpen(8, 'Orden y limpieza', true) ?>
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
                                <?php foreach ($AREAS_LIMPIEZA as $a): $k=slug($a); ?>
                                <tr><td class="eqname small"><?= htmlspecialchars($a) ?></td>
                                    <?php foreach (['1ero','2do','3ero'] as $t): ?>
                                    <td>
                                        <select name="orden[<?= $k ?>][<?= $t ?>]" class="form-select form-select-sm">
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
                            <?php foreach (['1ERO','2DO','3ERO'] as $t): $tk=slug($t); ?>
                            <label class="tlbl"><?= $t ?> turno</label>
                            <input type="text" name="inspecciono[<?= $tk ?>]" class="form-control form-control-sm mb-2" autocomplete="off">
                            <?php endforeach; ?>
                        </div>
                        </div>
                    <?= tileClose() ?>

                    </div>
                </div>

                <!-- ============================================================= -->
                <!-- TAB 2 · PRODUCCIONES W.R.                                      -->
                <!-- ============================================================= -->
                <div class="tab-pane fade" id="tab2">
                    <div class="grid">

                    <!-- Merma máquinas (desde Peso de Bolsas) -->
                    <?= tileOpen(1, 'Merma máquinas (kilos)', true) ?>
                        <p class="form-text mb-2">Estos kilos se llenan solos con el <strong>total</strong> de la pestaña ③ Peso de Bolsas (por turno).</p>
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
                            <?php foreach (['PANAL'=>'Pañal','TOALLA'=>'Toalla'] as $tk=>$tl): ?>
                            <tr><td class="eqname"><?= $tl ?></td>
                                <?php foreach (['t1','t2','t3'] as $t): ?>
                                <td class="linked"><?= inpNum("wr_merma[$tk][$t]", "data-wrmerma=\"{$tk}_{$t}\" data-sum=\"wrmerma_$tk\" readonly") ?></td>
                                <?php endforeach; ?>
                                <td class="total-cell"><?= inpNum("wr_merma[$tk][total]", "data-total=\"wrmerma_$tk\" readonly") ?></td></tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?= tileClose() ?>

                    <!-- SAM recuperado por tolva -->
                    <?= tileOpen(2, 'SAM recuperado por tolva') ?>
                        <div class="row">
                        <!-- Recorrido de datos para mostrar las dos tablas para las tolvas recuperados de (../WR/catalogos.php desde bitacora.php) -->
                        <?php foreach ($WR_TOLVAS as $tolva): ?>
                        <div class="col-6">
                            <div class="grp-title">Tolva <?= $tolva ?></div>
                            <table class="table table-tight mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Turno</th>
                                    <th class="text-center">Kg</th>
                                </tr>
                            </thead>
                            <tbody>
                            <!-- Impresion de valores segun los turnos por cada tabla de tolva -->
                            <?php foreach (['t1'=>'1','t2'=>'2','t3'=>'3'] as $t=>$lbl): ?>
                                <tr>
                                    <td class="eqname"><?= $lbl ?></td>
                                    <td><?= inpNum("wr_sam[$tolva][$t]", "data-sum=\"samtolva_$tolva\" data-samtolva=\"$tolva\"") ?></td>
                                </tr>
                            <!-- Campos de totales para ambas tolvas -->
                            <?php endforeach; ?>
                                <tr class="total-cell">
                                    <td class="eqname">Total</td>
                                    <td><?= inpNum("wr_sam[$tolva][total]", "data-total=\"samtolva_$tolva\" readonly") ?></td>
                                </tr>
                            </tbody>
                            </table>
                        </div>
                        <?php endforeach; ?>
                        </div>
                        <!-- Acumulado total de ambas tolvas -->
                        <label class="tlbl mt-2">SAM total del día</label>
                        <?= inpNum("wr_sam_total_dia", 'data-total="samdia" readonly') ?>
                    <?= tileClose() ?>

                    <!-- Pacas alimentadas WR -->
                    <?= tileOpen(3, 'Pacas alimentadas (por planta)') ?>
                        <div class="table-responsive">
                            <table class="table table-tight mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Planta</th>
                                        <th class="text-center">1er</th>
                                        <th class="text-center">2do</th>
                                        <th class="text-center">3er</th>
                                        <th class="text-center">Total</th>
                                    </tr>
                                </thead>

                                <!-- instancia de plantas configuradas desde (../WR/catalogos.php desde bitacora.php) -->
                                <tbody>
                                    <?php foreach ($WR_PLANTAS as $p): $k=slug($p); ?>
                                    <tr>
                                        <td class="eqname small"><?= htmlspecialchars($p) ?></td>
                                        <?php foreach (['t1','t2','t3'] as $t): ?>
                                        <td><?= inpNum("wr_pacas_alim[$k][$t]", "data-sum=\"wralim_$k\"") ?></td>
                                        <?php endforeach; ?>
                                        <td class="total-cell"><?= inpNum("wr_pacas_alim[$k][total]", "data-total=\"wralim_$k\" readonly") ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?= tileClose() ?>

                    <!-- Máquinas alimentadas KG/% -->
                    <?= tileOpen(4, 'Máquinas alimentadas (KG y %)', true) ?>
                        <div class="table-responsive">
                            <table class="table table-tight mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th rowspan="2" class="text-center">Máquina</th>
                                        <th colspan="2" class="text-center">1er turno</th>
                                        <th colspan="2" class="text-center">2do turno</th>
                                        <th colspan="2" class="text-center">3er turno</th>
                                        <th colspan="2" class="text-center">Total</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">KG</th>
                                        <th class="text-center">%</th>
                                        <th class="text-center">KG</th>
                                        <th class="text-center">%</th>
                                        <th class="text-center">KG</th>
                                        <th class="text-center">%</th>
                                        <th class="text-center">KG</th>
                                        <th class="text-center">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <!-- Recorrio de datos segun los configurados desde (../WR/catalogos.php desde bitacora.php) -->
                                <?php foreach ($WR_MAQUINAS as $m):
                                    $k = slug($m);                                
                                    // Cortado de string para almacenar la pura maquina en la BD
                                    $equipo = strpos($m, '/') !== false ? substr($m, strrpos($m, '/') + 1) : $m;
                                    $ek = slug($equipo); ?>                            

                                    <tr>
                                        <td class="eqname small"><?= htmlspecialchars($m) ?></td>
                                        <?php foreach (['t1' => 1, 't2' => 2, 't3' => 3] as $t => $ci): ?>
                                        <td><?= inpNum("wr_maq[$k][$t][kg]",  "data-sum=\"wrmaqkg_$k\" data-colwrkg=\"wrkg$ci\" data-recup=\"{$ek}|{$t}|kg\"") ?></td>
                                        <td><?= inpNum("wr_maq[$k][$t][pct]", "data-sum=\"wrmaqpct_$k\" data-colwrpct=\"wrpct$ci\" data-recup=\"{$ek}|{$t}|pct\"") ?></td>
                                    <?php endforeach; ?>
                                        <td class="total-cell"><?= inpNum("wr_maq[$k][total][kg]",  "data-total=\"wrmaqkg_$k\" data-gtotal=\"wrkgtot\" readonly") ?></td>
                                        <td class="total-cell"><?= inpNum("wr_maq[$k][total][pct]", "data-total=\"wrmaqpct_$k\" data-gtotal=\"wrpcttot\" readonly") ?></td>
                                    </tr>

                                <!-- Recorrido de datos -->
                                <?php endforeach; ?>                            
                                    <tr class="total-cell table-light"><td class="eqname">TOTAL POR TURNO</td>
                                        <td><?= inpNum("wr_tot_kg[t1]",  'data-total="wrkg1" readonly') ?></td>
                                        <td><?= inpNum("wr_tot_pct[t1]", 'data-total="wrpct1" readonly') ?></td>
                                        <td><?= inpNum("wr_tot_kg[t2]",  'data-total="wrkg2" readonly') ?></td>
                                        <td><?= inpNum("wr_tot_pct[t2]", 'data-total="wrpct2" readonly') ?></td>
                                        <td><?= inpNum("wr_tot_kg[t3]",  'data-total="wrkg3" readonly') ?></td>
                                        <td><?= inpNum("wr_tot_pct[t3]", 'data-total="wrpct3" readonly') ?></td>
                                        <td><?= inpNum("wr_total_kg_recuperados",  'data-total="wrkgtot" readonly') ?></td>
                                        <td><?= inpNum("wr_total_pct_recuperados", 'data-total="wrpcttot" readonly') ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    <?= tileClose() ?>

                    <!-- Pacas merma (Basura / Recorte) -> envio total a Tiempos de operacion y Tiempos perdidos -->
                    <?= tileOpen(5, 'Pacas merma', true) ?>
                        <p class="form-text mb-2">Las <strong>pacas</strong> de Basura y Recorte se copian a la pestaña ① Tiempos (Basura → Exportación, Recorte → Locales y Recorte).</p>
                        <div class="table-responsive">
                        <table class="table table-tight mb-0">
                            <thead class="table-dark">
                            <tr>
                                <th rowspan="2" class="align-middle">Concepto</th>
                                <th colspan="2" class="text-center">1er turno</th>
                                <th colspan="2" class="text-center">2do turno</th>
                                <th colspan="2" class="text-center">3er turno</th>
                                <th colspan="2" class="text-center">Total</th></tr>
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
                            <!-- Recorrido de datos segun los obtenidos por turno -->
                            <?php foreach (['BASURA'=>'Basura','RECORTE'=>'Recorte (pacas)'] as $tk=>$tl): ?>
                            <tr><td class="eqname"><?= $tl ?></td>
                                <?php foreach (['t1','t2','t3'] as $t): ?>
                                <td><?= inpNum("wr_pacasmerma[$tk][$t][kilos]", "data-sum=\"wrmermak_$tk\"") ?></td>
                                <td><?= inpNum("wr_pacasmerma[$tk][$t][pacas]", "data-sum=\"wrmermap_$tk\" data-mermapacas=\"{$tk}_{$t}\"") ?></td>
                                <?php endforeach; ?>
                                <td class="total-cell"><?= inpNum("wr_pacasmerma[$tk][total][kilos]", "data-total=\"wrmermak_$tk\" readonly") ?></td>
                                <td class="total-cell"><?= inpNum("wr_pacasmerma[$tk][total][pacas]", "data-total=\"wrmermap_$tk\" readonly") ?></td></tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?= tileClose() ?>

                    <!-- Notas + conductores -->
                    <?= tileOpen(6, 'Notas y conductores', true) ?>
                        <div class="row">
                        <div class="col-lg-7">
                            <div class="grp-title">Notas por máquina</div>
                            <?php foreach (array_merge($WR_MAQUINAS, ['OTROS']) as $m): $k=slug($m); ?>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text" style="min-width:130px"><?= htmlspecialchars($m) ?></span>
                                <input type="text" name="wr_nota[<?= $k ?>]" class="form-control" autocomplete="off">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="col-lg-5">
                            <div class="grp-title">Nombre y firma del conductor</div>
                            <?php foreach (['1'=>'1er turno','2'=>'2do turno','3'=>'3er turno'] as $n=>$lbl): ?>
                            <label class="tlbl"><?= $lbl ?></label>
                            <input type="text" name="wr_conductor[<?= $n ?>]" class="form-control form-control-sm mb-2" autocomplete="off">
                            <?php endforeach; ?>
                        </div>
                        </div>
                    <?= tileClose() ?>

                    </div>
                </div>

                <!-- ============================================================= -->
                <!-- TAB 3 · PESO DE BOLSAS (Toalla / Pañal)                        -->
                <!-- ============================================================= -->
                <div class="tab-pane fade" id="tab3">
                    <p class="form-text">Captura las bolsas pesadas por turno. Usa <strong>“+ fila”</strong> si necesitas agergar más. El total se actualiza solo y se copia a ② Producciones W.R. (Merma Máquinas). Una celda admite una suma rápida tipo <code>5+10</code>.</p>
                    <!-- Recorrido de datos para la generacion de tablas con el calculo de peso de toallas y pañal -->
                    <?php foreach ($PESO_TIPOS as $tk => $tl): ?>
                    <h3 class="grp-title mt-3" style="font-size:.95rem;color:var(--kc-accent)"><?= htmlspecialchars($tl) ?></h3>
                    <div class="grid" style="grid-template-columns:repeat(3,minmax(0,1fr))">
                        <!-- Recorrido de datos para la generacion de turnos segun el turno activo -->
                        <?php foreach (['t1'=>'1er turno','t2'=>'2do turno','t3'=>'3er turno'] as $t=>$tlabel):
                            $key = "{$tk}_{$t}"; ?>
                        <?= tileOpen((int)substr($t,1), $tlabel, false, 'data-turno-tile="'.substr($t,1).'"') ?>
                            <table class="table table-tight mb-2">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width:48px">No.</th>
                                    <th>Peso</th>
                                </tr>
                            </thead>
                            <!-- Generacion del cuerpo de datos para las columnas dinamicas -->
                            <tbody data-peso="<?= $key ?>">
                                <?php for ($i=1;$i<=5;$i++): ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $i ?></td>
                                    <td><?= inpNum("peso[$tk][$t][]", 'data-pesocell="'.$key.'"') ?></td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                            <!-- Obtencion de totales segun las filas indicadas -->
                            <tfoot>
                                <tr class="total-cell"><td class="text-end eqname">Total</td>
                                <td><?= inpNum("peso_total[$tk][$t]", "data-pesototal=\"$key\" readonly") ?></td></tr>
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

                <!-- ============================================================= -->
                <!-- TAB 4 · REPORTE DE OPERACIÓN (Waste Reclaim)                  -->
                <!-- ============================================================= -->
                <div class="tab-pane fade" id="tab4">
                    <div class="tile">
                    <div class="tile-head"><span class="step">1</span><h2>Reporte de operación</h2></div>
                    <div class="tile-body">
                        <p class="form-text">La fecha se toma de la jornada (<?= $hoy ?>). Llena el turno que corresponda en los siguientes campos:</p>
                        <ul class="nav nav-tabs mb-3" role="tablist">
                        <?php foreach (['1'=>'1er turno','2'=>'2do turno','3'=>'3er turno'] as $n=>$lbl): ?>
                            <li class="nav-item"><button class="nav-link <?= $n==='1'?'active':'' ?>" data-bs-toggle="pill" data-bs-target="#rep<?= $n ?>" type="button"><?= $lbl ?></button></li>
                        <?php endforeach; ?>
                        </ul>
                        <div class="tab-content">
                        <!-- Generacion de contenidos segun el turno -->
                        <?php foreach (['1','2','3'] as $n): $tk="t$n"; ?>
                            <div class="tab-pane fade <?= $n==='1'?'show active':'' ?>" id="rep<?= $n ?>">
                            <div class="row g-3">
                                <div class="col-md-4"><label class="tlbl">Operador</label><input type="text" name="reporte[<?= $tk ?>][operador]" class="form-control form-control-sm"></div>
                                <div class="col-md-4"><label class="tlbl">Ayudante</label><input type="text" name="reporte[<?= $tk ?>][ayudante]" class="form-control form-control-sm"></div>
                                <div class="col-md-4"><label class="tlbl">Línea Confort</label><input type="text" name="reporte[<?= $tk ?>][linea_confort]" class="form-control form-control-sm"></div>
                                <div class="col-md-4"><label class="tlbl">Supervisor</label><input type="text" name="reporte[<?= $tk ?>][supervisor]" class="form-control form-control-sm"></div>
                                <div class="col-md-4"><label class="tlbl">Trabajos Diversos</label><input type="text" name="reporte[<?= $tk ?>][trabajos_diversos]" class="form-control form-control-sm"></div>
                                <div class="col-12"><label class="tlbl">Comentarios</label><textarea name="reporte[<?= $tk ?>][comentarios]" class="form-control form-control-sm" rows="6"></textarea></div>
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
                <pre class="mb-0" style="max-height:260px;overflow:auto;"><?php echo htmlspecialchars(print_r($guardado, true)); ?></pre>
            </div>
            <?php endif; ?>
            
            
            </form>
        </div>
        </div>
    </div>
</div>