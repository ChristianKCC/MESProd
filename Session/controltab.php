<?php
require_once "../conexion.php";
require_once "ldap.php";
$Conection = new ClassConexion();
$usr = $_POST["usuario"];
$clave = $_POST["clave"];
$validador = 0;
$usr = str_replace('"', '', $usr);
$usr = str_replace("'", '', $usr);
$clave = str_replace('"', '', $clave);
$clave = str_replace("'", '', $clave);
$conn = $Conection->conexion("TLX009MXDB");
$resultados = sqlsrv_query($conn, "SELECT * FROM tblMaquinasUsuarios WHERE usuario = '$usr' AND password='$clave'");
while ($consulta = sqlsrv_fetch_array($resultados)) {
    session_start();
    $_SESSION["autentica"] = "SIP";
    $_SESSION["idmaquina"] = $consulta['id_maquina'];
    $_SESSION["usuario"] = $consulta['usuario'];
    header("Location:https://10.26.45.33/dashboard/KCMes/bitacora2/bitacora.php");
    die();
}
$usr2 = substr($usr, 1);
$usr2 = intval($usr2);
$conn = $Conection->conexion("TLX032MXDB");
$resultados = sqlsrv_query($conn, "SELECT NoEmp,Nombres,ApellidoPaterno,ApellidoMaterno,tblEmpleadosnvlautoriza.* FROM tblEmpleados LEFT JOIN tblEmpleadosnvlautoriza ON tblEmpleadosnvlautoriza.ibm=tblEmpleados.NoEmp WHERE NoEmp = '$usr2' AND ContrasenaOpcional='$clave'");
while ($consulta = sqlsrv_fetch_array($resultados)) {
    session_start();
    $_SESSION["ibm"] = $consulta['NoEmp'];
    $_SESSION["nombre"] = $consulta['Nombres'];
    $_SESSION["apellidop"] = $consulta['ApellidoPaterno'];
    $_SESSION["autorizavacaiones"] = $consulta['autorizavacaiones'];
    $_SESSION["nvlctagastos"] = $consulta['nvlctagastos'];
    $_SESSION["nvlautorizacapa"] = $consulta['nvlautorizacapa'];
    $_SESSION["admincursos"] = $consulta['nvlautorizacursos'];
    $_SESSION["nvlplaticas"] = $consulta['nvlplaticas'];
    $_SESSION["permisoProact"] = $consulta['permisoProact'];
    $_SESSION["permisoIMC"] = $consulta['permisoIMC'];
    $_SESSION["permisoConfClaves"] = $consulta['permisoConfClaves'];
    $_SESSION["permisoPersonal"] = $consulta['permisoPersonal'];
    $_SESSION["ldap"] = $usuario[1];
    $_SESSION["autentica"] = "SIP";
    header("Location: ../index/index.php");
    die();
}
$usuario = mailboxpowerloginrd($usr, $clave);
if ($usuario == "0" || $usuario == '') {
    header("Location: ../login.php?ident");
    die();
}
$usr = substr($usr, 1);
$resultados = sqlsrv_query($conn, "SELECT NoEmp,Nombres,ApellidoPaterno,ApellidoMaterno,tblEmpleadosnvlautoriza.* FROM tblEmpleados LEFT JOIN tblEmpleadosnvlautoriza ON tblEmpleadosnvlautoriza.ibm=tblEmpleados.NoEmp WHERE NoEmp= '$usr'");
while ($consulta = sqlsrv_fetch_array($resultados)) {
    session_start();
    $_SESSION["ibm"] = $consulta['NoEmp'];
    $_SESSION["nombre"] = $consulta['Nombres'];
    $_SESSION["apellidop"] = $consulta['ApellidoPaterno'];
    $_SESSION["autorizavacaiones"] = $consulta['autorizavacaiones'];
    $_SESSION["nvlctagastos"] = $consulta['nvlctagastos'];
    $_SESSION["nvlautorizacapa"] = $consulta['nvlautorizacapa'];
    $_SESSION["admincursos"] = $consulta['nvlautorizacursos'];
    $_SESSION["nvlplaticas"] = $consulta['nvlplaticas'];
    $_SESSION["permisoProact"] = $consulta['permisoProact'];
    $_SESSION["permisoIMC"] = $consulta['permisoIMC'];
    $_SESSION["permisoConfClaves"] = $consulta['permisoConfClaves'];
    $_SESSION["permisoPersonal"] = $consulta['permisoPersonal'];
    $_SESSION["ldap"] = $usuario[1];
    $_SESSION["autentica"] = "SIP";
    header("Location: ../index/index.php");
    die();
}
