<?php
require_once("../Session/seguridad.php");
// if ($_SESSION["permisoConfClaves"] != 1) {
//     header('Location: ../index/index');
// }
require_once("../index/header.php");
?>
<link rel="stylesheet" href="css/style.css">
<div class="container rounded shadow p-4">
    <div class="main">
        <div class="topbar">
            <div>
                <div class="ttl" id="tt">Asignación de combinaciones</div>
                <div class="tbc" id="tbc">Configuración › Combinaciones</div>
            </div>
        </div>
        <div class="content">
            <div class="tabs-bar">
                <div class="tb active" data-tab="secciones"><i class="fa-solid fa-table-columns"></i> Secciones</div>
                <div class="tb"><i class="fa-solid fa-puzzle-piece"></i> Modulos</div>
                <div class="tb"><i class="fa-solid fa-triangle-exclamation"></i> Fallas</div>
                <div class="tb"><i class="fa-solid fa-object-group"></i> Combinaciones</div>
            </div>

            <!-- Secciones -->
            <div class="tsec" id="tab-secciones">
                <div class="two-col">
                    <div class="card">
                        <div class="ctitle"><span class="ci"><i class="fa-solid fa-table-columns"></i></span> Secciones
                            registradas</div>
                        <div class="srow"><input type="text" placeholder="Buscar sección..."
                                oninput="fTbl('tb-sec',this.value)"></div>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tb-sec">

                            </tbody>
                        </table>
                    </div>
                    <div class="card">
                        <div class="ctitle"><span class="ci">✚</span><span id="sec-ftitle">Nueva sección</span></div>
                        <div class="eh" id="sec-hint">✏️ Editando — <a href="#" onclick="cSec();return false"
                                style="color:var(--ac)">cancelar</a></div>
                        <input type="hidden" id="sec-id">
                        <div class="fg"><label class="fl">Nombre</label><input type="text" id="sec-nom"
                                placeholder="Ej. Sellado"></div>
                        <div class="fg"><label class="fl">Descripción</label><textarea id="sec-desc"
                                placeholder="Descripción breve..."></textarea></div>
                        <div class="brow">
                            <button class="btn bp" onclick="savSec()">💾 Guardar</button>
                            <button class="btn bg" onclick="cSec()">Limpiar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modulos -->
            <div class="tsec" id="tab-modulos">
                <div class="two-col">
                    <div class="card">
                        <div class="ctitle"><span class="ci"><i class="fa-solid fa-puzzle-piece"></i></span> Módulos
                            registrados</div>
                        <div class="srow"><input type="text" placeholder="Buscar módulo..."
                                oninput="fTbl('tb-mod',this.value)"></div>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tb-mod">
                            </tbody>
                        </table>
                    </div>
                    <div class="card">
                        <div class="ctitle"><span class="ci">✚</span><span id="mod-ftitle">Nuevo módulo</span></div>
                        <div class="eh" id="mod-hint">✏️ Editando — <a href="#" onclick="cMod();return false"
                                style="color:var(--ac)">cancelar</a></div>
                        <input type="hidden" id="mod-id">
                        <div class="fg"><label class="fl">Nombre del módulo</label><input type="text" id="mod-nom"
                                placeholder="Ej. Módulo A3"></div>
                        <div class="fg"><label class="fl">Descripción</label><textarea id="mod-desc"
                                placeholder="Descripción breve..."></textarea></div>
                        <div class="brow">
                            <button class="btn bp" onclick="savMod()">💾 Guardar</button>
                            <button class="btn bg" onclick="cMod()">Limpiar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fallas -->
            <div class="tsec" id="tab-fallas">
                <div class="two-col">
                    <div class="card">
                        <div class="ctitle">
                            <span class="ci"><i class="fa-solid fa-exclamation-triangle"></i></span>
                            Fallas registradas
                        </div>
                        <div class="srow"><input type="text" placeholder="Buscar falla..."
                                oninput="fTbl('tb-fal',this.value)"></div>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tb-fal">
                            </tbody>
                        </table>
                    </div>
                    <div class="card">
                        <div class="ctitle"><span class="ci">✚</span><span id="fal-ftitle">Nueva falla</span></div>
                        <div class="eh" id="fal-hint">✏️ Editando — <a href="#" onclick="cFal();return false"
                                style="color:var(--ac)">cancelar</a></div>
                        <input type="hidden" id="fal-id">
                        <div class="fg"><label class="fl">Nombre de la falla</label><input type="text" id="fal-nom"
                                placeholder="Ej. Falla eléctrica"></div>
                        <div class="fg"><label class="fl">Descripción</label><textarea id="fal-desc"
                                placeholder="Descripción breve..."></textarea></div>
                        <div class="brow">
                            <button class="btn bp" onclick="savFal()">💾 Guardar</button>
                            <button class="btn bg" onclick="cFal()">Limpiar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combinaciones -->
            <div class="tsec active" id="tab-combinaciones"></div>
            <div class="card" style="margin: bottom 12px;">
                <div style="display: flex;align-items: center; gap: 16px; flex-wrap:wrap;">
                    <div style="display: flex; align-items: center; gap: 9px">
                        <span style="font-size:  20px"><i class="fa-solid fa-robot"></i></span>
                        <div>
                            <div style="font-size:11px;color:var(--tx3);margin-bottom:2px">Máquina objetivo</div>
                            <div style="font-weight:600;font-size:14px" id="maq-lbl"></div>
                            <div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/main.js"></script>