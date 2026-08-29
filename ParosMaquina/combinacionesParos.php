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
            <div class="tsec active" id="tab-secciones">
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
                                    <th>Clave</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="tb-sec">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/main.js"></script>