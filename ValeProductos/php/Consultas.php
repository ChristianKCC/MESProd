<?php
require_once("../../Session/seguridad.php");
require_once "../../conexion.php";

date_default_timezone_set("America/Mexico_City");

function obtenerPeriodoDesdeServidor($conn)
{
    $query = "SELECT GETDATE() AS FechaServidor";
    $stmtFecha = sqlsrv_query($conn, $query);
    $rowFecha = sqlsrv_fetch_array($stmtFecha);
    $fechaServidor = $rowFecha["FechaServidor"];

    // Si el día actual es menor a 6, estamos en el periodo del mes anterior
    if ((int) $fechaServidor->format("d") < 6) {
        // Retroceder un mes
        $inicio = new DateTime($fechaServidor->format("Y-m-25"));
        $inicio->modify('-1 month');
        $fin = new DateTime($fechaServidor->format("Y-m-05"));
    } else {
        // Periodo normal del mes actual al siguiente
        $inicio = new DateTime($fechaServidor->format("Y-m-25"));
        $fin = new DateTime($fechaServidor->format("Y-m-05"));
        $fin->modify('+1 month');
    }

    return [
        "inicioPeriodo" => $inicio,
        "finPeriodo" => $fin,
        "fechaActual" => $fechaServidor
    ];
}

class ValeProductosConsultas
{

    function dataUserSesion()
    {
        $Conection = new ClassConexion();
        $usr = $_SESSION['ibm'];
        if (!isset($_SESSION["autentica"]) || $_SESSION["autentica"] != "SIP") {
            echo json_encode(["status" => false]);
        } else {
            $conn = $Conection->conexion("TLX032MXDB");
            $query = "SELECT tblEmpleados.NoEmp,tblEmpleados.Nombre,tblEmpleados.NombreDepartamento as departamento, 
                    tblEmpleados.puesto as puesto, tblEmpleados.EmpleadoSindicalizado as sindicalizado FROM tblEmpleados WHERE NoEmp=$usr";
            $result = sqlsrv_query($conn, $query);
            $array = array();
            while ($row = sqlsrv_fetch_array($result)) {
                array_push($array, [
                    "noemp" => $row['NoEmp'],
                    "nombre" => $row['Nombre'],
                    "departamento" => $row['departamento'],
                    "puesto" => $row['puesto'],
                    "sindicalizado" => $row['sindicalizado'],
                    "adminValeProducto" => $_SESSION["adminValeProducto"],
                    'status' => true
                ]);
            }
            sqlsrv_close($conn);
            echo json_encode($array);
        }
    }
    function comprobarFecha()
    {
        $Conection = new ClassConexion();
        $conn = $Conection->conexion('TLX002MXDB');

        $periodo = obtenerPeriodoDesdeServidor($conn);
        sqlsrv_close($conn);

        echo json_encode([
            "inicioPeriodo" => $periodo["inicioPeriodo"]->format('Y-m-d'),
            "finPeriodo" => $periodo["finPeriodo"]->format('Y-m-d'),
            "fechaActual" => $periodo["fechaActual"]->format('Y-m-d')
        ]);
    }

    function validarCantidadValesTipo()
    {
        $noemp = $_POST['noemp'];
        $Conecta = new ClassConexion();

        // Obtener tipo de empleado
        $connEmpleados = $Conecta->conexion('TLX032MXDB');
        $queryTipo = "SELECT EmpleadoSindicalizado FROM tblEmpleados WHERE NoEmp = ?";
        $stmtTipo = sqlsrv_query($connEmpleados, $queryTipo, [$noemp]);
        $rowTipo = sqlsrv_fetch_array($stmtTipo);
        $tipoEmpleado = $rowTipo["EmpleadoSindicalizado"];

        // Obtener periodo desde servidor
        $connVales = $Conecta->conexion('TLX002MXDB');
        $periodo = obtenerPeriodoDesdeServidor($connVales);

        // Contar vales registrados en el periodo
        $queryVales = "SELECT COUNT(*) AS total FROM tblValeProductosAlmacen 
                    WHERE NoEmp = ? AND FechaVale BETWEEN ? AND ?";
        $paramsVales = [
            $noemp,
            $periodo["inicioPeriodo"]->format("Y-m-d"),
            $periodo["finPeriodo"]->format("Y-m-d")
        ];
        $stmtVales = sqlsrv_query($connVales, $queryVales, $paramsVales);
        $rowVales = sqlsrv_fetch_array($stmtVales);
        $total = $rowVales["total"];

        // Validación
        $puedePedir = $total < 2;

        echo json_encode([
            "puedePedir" => $puedePedir,
            "tipo" => $tipoEmpleado,
            "valesRegistrados" => $total,
            "inicioPeriodo" => $periodo["inicioPeriodo"]->format('Y-m-d'),
            "finPeriodo" => $periodo["finPeriodo"]->format('Y-m-d'),
            "fechaActual" => $periodo["fechaActual"]->format('Y-m-d')
        ]);
    }

    function dataUserCompleate()
    {
        $noemp = $_POST["noemp"];
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX032MXDB");
        $query = "SELECT tblEmpleados.NoEmp,tblEmpleados.Nombre,tblEmpleados.NombreDepartamento as departamento, 
                    tblEmpleados.puesto as puesto, tblEmpleados.EmpleadoSindicalizado as sindicalizado, tblEmpleados.ContrasenaOpcional as contrasena FROM tblEmpleados WHERE NoEmp=$noemp";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "noemp" => $row[0],
                "nombre" => $row[1],
                "departamento" => $row[2],
                "puesto" => $row[3],
                "sindicalizado" => $row[4],
                "contrasena" => $row[5],
            ]);
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function dataProductsCompleate()
    {
        $idproducto = $_POST["idproducto"];
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX003MXDB");
        $query = "SELECT ProductoSub.IdProductSub, ProductoSub.PaqueteCorrugado, ProductoSub.PaqueteContenido, ProductoSub.PrecioIVA FROM ProductoSub WHERE IdProductSub=$idproducto";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push(
                $array,
                [
                    "idproducto" => $row[0],
                    "cantidad" => $row[1],
                    "descripcion" => $row[2],
                    "precio" => $row[3],
                ]
            );
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function dataProductsCompleteDos()
    {
        $numProduct = $_POST["numProduct"];
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX003MXDB");
        $query = "SELECT ProductoSub.IdProductSub AS idProduct, ProductoSub.DescProductSub + ' ' + ProductoSub.PaqueteContenido AS descProducto, 
                ProductoSub.PaqueteCorrugado AS paqueteCorr, ProductoSub.PrecioIVA AS precio,
                ProductoSub.IdProductSec AS IdCategoria, ProductoSec.DescProductSec AS categoria,
                ProductoSub.rutaImagen AS imagen
                FROM ProductoSub 
                INNER JOIN [TLX003MXDB].dbo.ProductoSec ON ProductoSec.IdProductSec = ProductoSub.IdProductSec
                WHERE ClaveProductSub=$numProduct";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push(
                $array,
                [
                    "idProducto" => $row["idProduct"],
                    "descProducto" => $row["descProducto"],
                    "paqueteCorr" => $row["paqueteCorr"],
                    "precio" => $row["precio"],
                    "IdCategoria" => $row["IdCategoria"],
                    "categoria" => $row["categoria"],
                    "RutaImagen" => $row["imagen"],
                ]
            );
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function saveValeProductos()
    {
        $noemp = $_POST["noemp"];
        $empleadoActivo = $_POST["empleadoActivo"];
        $departamento = $_POST["departamento"];
        $puesto = $_POST["puesto"];
        $categoria = $_POST["categoria"];
        $subcategoria = $_POST["subcategoria"];
        $cantidad = $_POST["cantidad"];
        $precio = $_POST["precio"];

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $array = array(
            $noemp,
            $empleadoActivo,
            $departamento,
            $puesto,
            $categoria,
            $subcategoria,
            $cantidad,
            $precio,
        );
        $query = "INSERT INTO tblValeProductosAlmacen(NoEmp, EmpleadoSindicalizado, NoDepto, NoPuesto, NoCategoria, NoSubcategoria, Cantidad, Precio) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        var_dump($query);
        $result = sqlsrv_query($conn, $query, $array);
        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }
    }

    function updateValeProductos()
    {
        $folio = $_POST["folio"];
        $noemp = $_POST["noemp"];
        $empleadoActivo = $_POST["empleadoActivo"];
        $departamento = $_POST["departamento"];
        $puesto = $_POST["puesto"];
        $categoria = $_POST["categoria"];
        $subcategoria = $_POST["subcategoria"];
        $cantidad = $_POST["cantidad"];
        $precio = $_POST["precio"];

        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $array = array(
            $noemp,
            $empleadoActivo,
            $departamento,
            $puesto,
            $categoria,
            $subcategoria,
            $cantidad,
            $precio,
            $folio
        );
        $querr = "UPDATE tblValeProductosAlmacen SET NoEmp=?, EmpleadoSindicalizado=?, NoDepto=?, NoPuesto=?, NoCategoria=?, NoSubcategoria=?, Cantidad=?, Precio=? WHERE IdVale=?";
        $result = sqlsrv_query($conn, $querr, $array);
        if ($result === false) {
            http_response_code(500);
            die(print_r(sqlsrv_errors(), true));
        } else {
            http_response_code(200);
        }
    }

    function tblConsultas()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query = "SELECT TOP 99 tblValeProductosAlmacen.IdVale as id, tblValeProductosAlmacen.NoEmp as NoEmp, tblValeProductosAlmacen.EmpleadoSindicalizado as sindicalizado,
        tblEmpleados.Nombre as nombre, tblDepartamentos.NombreDepto as departamento, tblPuestos.nombre as puesto, ProductoSec.DescProductSec as categoria, ProductoSub.DescProductSub as subcategoria,
        ProductoSub.PaqueteContenido as descripcion, tblValeProductosAlmacen.Cantidad as cantidad, tblValeProductosAlmacen.Precio as precio, tblValeProductosAlmacen.FechaVale as fecha
            FROM tblValeProductosAlmacen
            INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblValeProductosAlmacen.NoEmp
            INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblValeProductosAlmacen.NoDepto
            INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblValeProductosAlmacen.NoPuesto
            INNER JOIN TLX003MXDB.dbo.ProductoSec ON ProductoSec.IdProductSec = tblValeProductosAlmacen.NoCategoria
            INNER JOIN TLX003MXDB.dbo.ProductoSub ON ProductoSub.IdProductSub = tblValeProductosAlmacen.NoSubcategoria
            ORDER BY tblValeProductosAlmacen.IdVale DESC
            ";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            array_push($array, [
                "idVale" => $row["id"],
                "NoEmp" => $row["NoEmp"],
                "sindicalizado" => $row["sindicalizado"],
                "nombre" => $row["nombre"],
                "departamento" => $row["departamento"],
                "puesto" => $row["puesto"],
                "categoria" => $row["categoria"],
                "subcategoria" => $row["subcategoria"],
                "descripcion" => $row["descripcion"],
                "cantidad" => $row["cantidad"],
                "precio" => $row["precio"],
                "fecha" => $row["fecha"]->format('Y-m-d H:i:s'),
                "adminValeProducto" => $_SESSION["adminValeProducto"]
            ]);
        }
        // echo $_SESSION["autentica"];
        sqlsrv_close($conn);
        echo json_encode($array);

    }

    function dataforeditVale()
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion('TLX002MXDB');
        $id = $_GET['id'];
        $query = "SELECT tblValeProductosAlmacen.*, tblEmpleados.Nombre,
                    ProductoSub.ClaveProductSub AS claveProducto, 
                    ProductoSub.DescProductSub + ' ' + ProductoSub.PaqueteContenido as descripcion,
                    ProductoSec.DescProductSec AS categoria 
                    FROM tblValeProductosAlmacen 
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = tblValeProductosAlmacen.NoEmp
                    INNER JOIN TLX003MXDB.dbo.ProductoSub ON ProductoSub.IdProductSub = tblValeProductosAlmacen.NoSubcategoria
                    INNER JOIN TLX003MXDB.dbo.ProductoSec ON ProductoSec.IdProductSec = tblValeProductosAlmacen.NoCategoria
                    WHERE IdVale =$id";
        $result = sqlsrv_query($conn, $query);
        $array = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = $row;
        }
        sqlsrv_close($conn);
        echo json_encode($array);
    }

    function nombreMes($numMes)
    {
        $numMes == 1 && $numMes = "Enero";
        $numMes == 2 && $numMes = "Febrero";
        $numMes == 3 && $numMes = "Marzo";
        $numMes == 4 && $numMes = "Abril";
        $numMes == 5 && $numMes = "Mayo";
        $numMes == 6 && $numMes = "Junio";
        $numMes == 7 && $numMes = "Julio";
        $numMes == 8 && $numMes = "Agosto";
        $numMes == 9 && $numMes = "Septiembre";
        $numMes == 10 && $numMes = "Octubre";
        $numMes == 11 && $numMes = "Noviembre";
        $numMes == 12 && $numMes = "Diciembre";

        return $numMes;
    }

    public function generarValePDF($folio)
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion('TLX002MXDB');
        $query = "SELECT vlpa.IdVale, vlpa.NoEmp, vlpa.NoDepto, vlpa.NoCategoria, vlpa.NoSubcategoria, 
		            vlpa.Cantidad, vlpa.Precio, vlpa.FechaVale, tblEmpleados.Nombre,
                    ProductoSub.ClaveProductSub AS claveProducto, 
                    TRIM(ProductoSec.DescProductSec) + ' ' + TRIM(ProductoSub.DescProductSub) + '- ' + TRIM(ProductoSub.PaqueteContenido) as descripcion,
                    MONTH(GETDATE()) AS mes
                    FROM tblValeProductosAlmacen vlpa
                    INNER JOIN TLX032MXDB.dbo.tblEmpleados ON tblEmpleados.NoEmp = vlpa.NoEmp
                    INNER JOIN TLX003MXDB.dbo.ProductoSub ON ProductoSub.IdProductSub = vlpa.NoSubcategoria
                    INNER JOIN TLX003MXDB.dbo.ProductoSec ON ProductoSec.IdProductSec = vlpa.NoCategoria
                    WHERE IdVale ='$folio'";
        $result = sqlsrv_query($conn, $query);
        $datos = array();
        $row = sqlsrv_fetch_array($result);
        array_push($datos, [
            'FolioVale' => $row['IdVale'],
            'NoEmp' => $row['NoEmp'],
            'NoDepto' => $row['NoDepto'],
            'Nombre' => $row['Nombre'],
            'ClaveProducto' => $row['claveProducto'],
            'Descripcion' => $row['descripcion'],
            'Precio' => $row['Precio'],
            'FechaVale' => $row['FechaVale']->format('d-m-Y'),
            "Mes" => $this->nombreMes($row["mes"]),
        ]);

        return $datos;
    }
}

if (isset($_GET['dataUserCompleate'])) {
    $Consultas = new ValeProductosConsultas();
    $Consultas->dataUserCompleate();
} else if (isset($_GET['dataProductsCompleate'])) {
    $Consultas = new ValeProductosConsultas();
    $Consultas->dataProductsCompleate();
} else if (isset($_GET['saveValeProductos'])) {
    $Consultas = new ValeProductosConsultas();
    $Consultas->saveValeProductos();
} else if (isset($_GET['updateValeProductos'])) {
    $Consultas = new ValeProductosConsultas();
    $Consultas->updateValeProductos();
} else if (isset($_GET['tblConsultas'])) {
    $Consultas = new ValeProductosConsultas();
    $Consultas->tblConsultas();
} else if (isset($_GET['dataforeditVale'])) {
    $Consultas = new ValeProductosConsultas();
    $Consultas->dataforeditVale();
} else if (isset($_GET['dataUserSesion'])) {
    $Consultas = new ValeProductosConsultas();
    $Consultas->dataUserSesion();
} else if (isset($_GET['comprobarFecha'])) {
    $Consultas = new ValeProductosConsultas();
    $Consultas->comprobarFecha();
} else if (isset($_GET['validarCantidadValesTipo'])) {
    $Consultas = new ValeProductosConsultas();
    $Consultas->validarCantidadValesTipo();
} else if (isset($_GET['dataProductsCompleteDos'])) {
    $Consultas = new ValeProductosConsultas();
    $Consultas->dataProductsCompleteDos();
}