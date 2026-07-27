<?php
require_once '../conexion.php';
require_once "../Session/seguridad.php";
class Catalogos
{
    // Metodo publico para acceso a BD con parametro de acceso a query
    // public static function getDataSlcDB($db, $query)
    // {
    //     $conexion = new ClassConexion();
    //     $conn = $conexion->conexion($db);
    //     $result = sqlsrv_query($conn, $query);
    //     $array = array();
    //     while ($row = sqlsrv_fetch_array($result)) {
    //         array_push($array, ["id" => $row[0], "nombre" => $row[1]]);
    //     }
    //     echo json_encode($array);
    //     sqlsrv_close($conn);
    // }

    public static function getDataSlcDB($db, $query)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion($db);
        $result = sqlsrv_query($conn, $query);
     
        if ($result === false) {
            echo json_encode(["error" => sqlsrv_errors()]);
            sqlsrv_close($conn);
            return;
        }

        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ["id" => $row[0], "nombre" => $row[1]]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }

    // Metodo de obtencion de valores con parametros de:
    // BD + Query mas el valor de la cadena
    public static function getDataSlcDBValor($db, $query)
    {
        $conexion = new ClassConexion();
        $conn = $conexion->conexion($db);
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, ["id" => $row[0], "nombre" => $row[1], "valor" => $row[2]]);
        }
        echo json_encode($array);
        sqlsrv_close($conn);
    }
}