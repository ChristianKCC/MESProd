<?php
require_once("../Session/seguridad.php");
if($_SESSION["permisoConfClaves"]!=1){
    header('Location: ../index/index');
}
require_once("../index/header.php"); 
?>
<div class="container rounded shadow">
    <h4 class="tittlecont">Configuracion Vales</h4>
    <div class="row">
        <div class="col-5 border">
            <form>
                <div class="row mb-2">
                    <input type="hidden" id="idclase" />
                    <div class="col-2"><small>NoClase</small><input type="number" class="form-control form-control-sm" id="noclase" /> </div>
                    <div class="col-6"><small>Nombre Clase</small><input type="text" class="form-control form-control-sm" id="nombreclase" /> </div>
                    <div class="col-1"><br /><button type="reset" id="cleanclases" class="btn btn-sm btn-secondary"><i class="fas fa-undo-alt"></i></button></div>
                    <div class="col-1"><br /><button class="btn btn-sm bg-target" id="savechgclases"><i class="fas fa-save"></i></button></div>
                </div>
            </form>
            <div class="table-responsive" style="height: 200px;">
                <table class="table border" id="tblclasesenc">
                    <thead class="table-dark">
                        <th>ID</th>
                        <th>NoClase</th>
                        <th>Descripcion Clase</th>
                        <th></th>
                    </thead>
                    <tbody id="tblclases">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-7 border">
            <form>
                <div class="row mb-2">
                    <input type="hidden" id="idclave" />
                    <div class="col-2"><small>NoClave</small><input type="text" class="form-control form-control-sm" id="noclave" /> </div>
                    <div class="col-6"><small>Nombre Clave</small><input type="text" class="form-control form-control-sm" id="nombreclave" /> </div>
                    <div class="col-1"><small>UM</small><select class="form-control form-control-sm" id="umclave">
                            <option value='MM2'>MM2</option>
                            <option value='COR'>COR</option>
                        </select> </div>
                    <div class="col-1"><br /><button type="reset" id="cleanclaves" class="btn btn-sm btn-secondary"><i class="fas fa-undo-alt"></i></button></div>
                    <div class="col-1"><br /><button class="btn btn-sm bg-target" id="savechgclaves"><i class="fas fa-save"></i></button></div>
                </div>
            </form>
            <div class="table-responsive" style="height: 200px;">
                <table class="table border" id="tblclavesenc">
                    <thead class="table-dark">
                        <th>ID</th>
                        <th>No Clave</th>
                        <th>Descripcion Clave</th>
                        <th>UM</th>
                        <th></th>
                    </thead>
                    <tbody id="tblclaves">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row my-2">
        <div class="col border">

            <form>
                <div class="row mb-2">
                    <input type="hidden" id="idmaterial" />
                    <div class="col-1"><small>NoMaterial</small><input type="number" class="form-control form-control-sm" id="nomaterial" /> </div>
                    <div class="col-4"><small>Nombre Material</small><input type="text" class="form-control form-control-sm" id="nombrematerial" /> </div>
                    <div class="col-1"><small>UMMaterial</small><select class="form-control form-control-sm" id="ummaterial">
                            <option value='KGS'>KGS</option>
                            <option value='PZA'>PZA</option>
                            <option value='MM2'>MM2</option>
                        </select>
                    </div>
                    <div class="col-1"><small>UMMaterial</small><select class="form-control form-control-sm" id="ummat">
                            <option value='TAMBO'>TAMBO</option>
                            <option value='PIEZA'>PIEZA</option>
                            <option value='ROLLO'>ROLLO</option>
                            <option value='CAJA'>CAJA</option>
                            <option value='SACO'>SACO</option>
                            <option value='PAQUETE'>PAQUETE</option>
                            <option value='PZA'>PZA</option>
                        </select>
                    </div>
                    <div class="col-1"><small>Montacargas</small><select class="form-control form-control-sm" id="montacargas">
                            <option value='CARTON CLAMP'>CARTON CLAMP</option>
                            <option value='ROL CLAMP'>ROL CLAMP</option>
                            <option value='HORQUILLAS'>HORQUILLAS</option>
                        </select>
                    </div>
                    <div class="col-1"><small>Costos</small><input type="number" class="form-control form-control-sm" id="costos" /> </div>
                    <div class="col-1"><small>Tiempo</small><input type="number" class="form-control form-control-sm" id="tiempo" /> </div>
                    <div class="col-1"><br /><button type="reset" id="cleanmateriales" class="btn btn-sm btn-secondary"><i class="fas fa-undo-alt"></i></button></div>
                    <div class="col-1"><br /><button class="btn btn-sm bg-target" id="savechgmateriales"><i class="fas fa-save"></i></button></div>
                </div>
            </form>
            <div class="table-responsive" style="height: 200px;">
                <table class="table border text-center" id="tblmaterialesenc">
                    <thead class="table-dark">
                        <th>ID</th>
                        <th>No Material</th>
                        <th>Descripcion Material</th>
                        <th>UMMaterial</th>
                        <th>UM</th>
                        <th>Montacargas</th>
                        <th>Costos</th>
                        <th>Tiempo</th>
                        <th></th>
                    </thead>
                    <tbody id="tblmateriales">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="row my-4">
        <div class="col">
            <form>
                <div class="row mb-2">
                    <div class="col-2"><small>Maquina</small><select class="form-control form-control-sm" id="maquinaconv"></select></div>
                    <div class="col-2"><small>Clase</small><input type="text" class="form-control form-control-sm" id="claseconv" readonly /></div>
                    <div class="col-2"><small>Clave</small><input type="text" class="form-control form-control-sm" id="claveconv" readonly /></div>
                    <div class="col-2"><small>Material</small><input type="text" class="form-control form-control-sm" id="materialconv" readonly /></div>
                    <div class="col-1"><br /><button type="reset" id="cleanconv" class="btn btn-sm btn-secondary"><i class="fas fa-undo-alt"></i></button></div>
                    <div class="col-1"><br /><button class="btn btn-sm bg-target" id="saveconvinacion"><i class="fas fa-save"></i></button></div>
                </div>
            </form>
            <div class="table-responsive" style="height: 600px;">
                <table class="table border text-center" id="tblconvinacionesenc">
                    <thead class="table-dark">
                        <th>ID</th>
                        <th>NoMaquina</th>
                        <th>Maquina</th>
                        <th>NoClase</th>
                        <th>Clase</th>
                        <th>NoClave</th>
                        <th>Clave</th>
                        <th>Nomaterial</th>
                        <th>Material</th>
                        <th></th>
                    </thead>
                    <tbody id="tblconvinaciones">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once("../index/footer.php") ?>
<script type="module" src="js/Configuracionvales.js"></script>