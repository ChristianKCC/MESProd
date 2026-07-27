
<?php require_once("header.php"); ?>
<!-- Contenido -->
<div class="container rounded shadow p-2">
	<h3>Maquina <?php echo $_SESSION['usuario']; ?></h3>
    <div class="centrar">
    <div class="row">
     <div class="col-6">
        <a href="../trazabilidad/index.php"><button class="box">Trazabilidad</button></a>
     </div>
     <!-- <div class="col-6">
     <a href="../bitacora/index.php"><button class="box">Paros</button></a>
     </div> -->
     <div class="col-6">
     <a href="../bitacora/bitacora.php"><button class="box">Bitacora</button></a>
     </div>
     <div class="col-6">
     <a href="../noconformidad/index.php"><button class="box">No Conformidad</button></a>
     </div>
     <div class="col-6">
     <a href="../Session/salir.php"><button class="box">Salir</button></a>
     </div>
    </div>
    </div>
 </div>
<?php require_once("footer.php") ?>

       