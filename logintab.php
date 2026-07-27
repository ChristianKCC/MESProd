<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" type="text/css" href="assets/bootstrap5/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="css/config.css">
  <link rel="stylesheet" type="text/css" href="assets/awesome5/css/all.min.css">
  <link rel="shortcut icon" href="./img/favicon.ico" type="image/x-icon">
  <title>Manufacturing Execution System Login</title>
</head>
<body class="m-0 vh-100 row justify-content-center align-items-center">
  <div class="col-12 col-md-4">
  <form class="form-validate"  method="post" action="./Session/controltab.php" id="formlog" name="formlog">
    <div class="col bg-white p-4 rounded shadow">
      <h4 class="text-center">Iniciar Sesión</h4>
      <div class="row">
      <div class="col-12 col-md-6">
          <div class="row justify-content-center text-center">
          <div class="col-12 col-md-12 my-4">
          <span class="fontstyle">Ingresa tu usuario</span>
          <input autocomplete="off" type="text" name="usuario" id="usuario" class="form-control form-control-sm" required>
          </div>
          <div class="col-12 col-md-12 my-4">
          <span class="fontstyle">Ingresa tu contraseña</span>
          <input type="password" name="clave" id="clave" class="form-control form-control-sm" required>
          </div>
          <div class="col-12 col-md-12 mb-2 ">
          <button class="btn bg-target fontstyle fw-bolt">Iniciar Sesión</button><br>
          <small>Haz click <a href="../" class="text-primary">aquí</a> para volver a la pagina anterior</small>
          </div>
          </div>
          <?php 
          echo isset($_GET["ident"]) ? "<span class ='text-danger'>Tu usuario o contraseña es incorrecta, si tienes problemas para iniciar sesión contacta al administrador</span>" : null
          ?>
      </div>
      <div class="col-6 mt-2">
      <img src="img/kcmplogo.png" width="100%">
    </div>
  </div>
</div>
</form>
</div>
<script src="assets/jquery.min.js"></script>
<script type="text/javascript" src="assets/bootstrap5/js/bootstrap.min.js"></script>
</body>
</html>