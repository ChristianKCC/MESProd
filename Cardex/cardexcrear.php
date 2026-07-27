<?php require_once("../Session/seguridad.php");
if($_SESSION["admincursos"] !=2 ){
      header("Location:../index/index.php");
   }
require_once("../index/header.php"); 
?>
<!-- Contenido -->
<div class="container p-2 border shadow rounded">
	<h5>Creación de cardex</h5>
<div class="row">
<div class="col">
<select class="form-control" id="slccardex">
</select>
</div>
  <div class="row mb-2"> 
       <div class="col-2">
         <small class="fw-bold">Para MCM</small>
        <select size="4" class="form-control" id="MCM"><option> </option></select>
        <button class="form-control btn" id="csltmcm">Consultar</button>
      </div>
       <div class="col-2">
         <small class="fw-bold">Para Departamento</small>
        <select size="4" class="form-control" id="NombreDepto"><option> </option></select>
        <button class="form-control btn" id="csltdep">Consultar</button>
      </div>
       <div class="col-2">
         <small class="fw-bold">Para Máquinas</small>
        <select size="4" class="form-control" id="NombreMaquina"><option> </option></select>
        <button class="form-control btn" id="csltmaq">Consultar</button>
      </div>
       <div class="col-2">
         <small class="fw-bold">Para Secciones</small>
        <select size="4" class="form-control" id="NombreSecciones"><option> </option></select>
        <button class="form-control btn" id="csltsec">Consultar</button>
      </div>
      <div class="col-2">
         <small class="fw-bold">Para Actvividades</small>
        <select size="4" class="form-control" id="DescripcionActividad"><option> </option></select>
        <button class="form-control btn" id="csltact">Consultar</button>
      </div>
      <div class="col-2">
         <small class="fw-bold">Para Tecnologías</small>
        <select size="4" class="form-control" id="NombreTecnologia"><option> </option></select>
        <button class="form-control btn" id="cslttec">Consultar</button>
      </div>
    </div>
    <div class="row">
      <div class="col">
         <small class="fw-bold">Lista de cursos para agregar al cardex</small>
        <select class="form-control" size="8" id="tblcursosadd">
        </select>
      </div>
      <div class="col">
         <small class="fw-bold">Lista de cursos del cardex</small>
        <select class="form-control" size="8" id="tblcursos">
        </select>
      </div>
    </div>
    <div id="resultadoerror"></div>
</div>
</div>
<?php require_once("../index/footer.php") ?>
<script src="js/llnarcardexcrear.js" type="text/javascript"></script>
</body>
</html>