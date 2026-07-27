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
    header("Location: ../bitacora/bitacora.php");
    die();
}

$usr2 = intval(substr($usr, 1));
if ($usr2 > 0 && $clave !== '') {
    $conn = $Conection->conexion("TLX032MXDB");
    $tsql = "SELECT NoEmp,Nombres,Nombre ,ApellidoPaterno,ApellidoMaterno,NombreDepartamento , tblEmpleadosnvlautoriza.* 
            FROM tblEmpleados 
            LEFT JOIN tblEmpleadosnvlautoriza ON tblEmpleadosnvlautoriza.ibm=tblEmpleados.NoEmp 
            WHERE NoEmp = ? AND ContrasenaOpcional = ?";
    // --  WHERE NoEmp IN (?, ?) AND NoEmp = ? AND ContrasenaOpcional = ?";
    // $params = [58998, 34374, $usr2, $clave];
    $params = [$usr2, $clave];
    $stmt = sqlsrv_query($conn, $tsql, $params);
    if ($stmt !== false) {
        if ($consulta = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if (session_status() !== PHP_SESSION_ACTIVE)
                session_start();
            $_SESSION["ibm"] = $consulta['NoEmp'];
            $_SESSION["nombre"] = $consulta['Nombres'];
            $_SESSION["nombreFull"] = $consulta['Nombre'];
            // Obtencion del departamento
            $_SESSION["clvDepartamento"] = $consulta['NombreDepartamento'];
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
            $_SESSION["adminReportesProduccion"] = $consulta['adminReportesProduccion'];
            $_SESSION["ldap"] = ''; // se asigna después tras login LDAP
            $_SESSION["autentica"] = "SIP";
            sqlsrv_free_stmt($stmt);
            header("Location: ../index/index.php");
            die();
        }
        sqlsrv_free_stmt($stmt);
    }
}
$usuario = mailboxpowerloginrd($usr, $clave);
if ($usuario == "0" || $usuario == '') {
    header("Location: ../login.php?ident");
    die();
}
$usr = substr($usr, 1);
$resultados = sqlsrv_query($conn, "SELECT NoEmp,Nombres, Nombre, ApellidoPaterno,ApellidoMaterno,NombreDepartamento,tblEmpleadosnvlautoriza.* FROM tblEmpleados LEFT JOIN tblEmpleadosnvlautoriza ON tblEmpleadosnvlautoriza.ibm=tblEmpleados.NoEmp WHERE NoEmp= '$usr'");
while ($consulta = sqlsrv_fetch_array($resultados)) {
    session_start();
    $_SESSION["ibm"] = $consulta['NoEmp'];
    $_SESSION["nombre"] = $consulta['Nombres'];
    $_SESSION["nombreFull"] = $consulta['Nombre'];
    // Obtencion del departamento
    $_SESSION["clvDepartamento"] = $consulta['NombreDepartamento'];
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
    $_SESSION["adminReportesProduccion"] = $consulta['adminReportesProduccion'];
    $_SESSION["ldap"] = $usuario[1];
    $_SESSION["autentica"] = "SIP";
    header("Location: ../index/index.php");
    die();
}
