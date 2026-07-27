<?php

// Archivo de pruebas para validaciones principales:
/*
  -- Busqueda y agrupamiento de datos para empleados, transacciones y descansos
  -- Creacion de PDF segun el caso
  -- Optimizaciones para query's

  -- El archivo actual y usado es el creartarjetasClas.php
  -- Divide responsabilidades entre clases por repositorio para cada obligacion (Empleados, Descansos, Transacciones, Generacion de PDF(TarjetaPdf.php))
*/


// Libreria para PDF
require_once('../../fpdf/fpdf.php');
// Conexion a la BD
require_once('../../conexion.php');
// Conexion a la BD
$conection = new ClassConexion();
$conn = $conection->conexion("TLX001MXDB");
// Recuperacion de datos de fechas segun form
$fechai = $_POST['fechai'];
$tipemp = $_POST['tipemp'];
// Recuperacion de centro de costos y depto.
$ctrocstos = $_POST['ctrocstos'];
$departamento = $_POST['departamento'];

// Validacion de datos para su uso segun el caso:
// Si la variable esta vacia le agrega una cadena vacia
// Si la variable no esta vacia se agrega una condicion sql a cada variable
$departamento == '' ? $departamento = '' : $departamento = " AND tblEmpleados.NombreDepartamento=" . $_POST["departamento"];
$tipemp === '' ? $tipemp = "" : $tipemp = " AND tblEmpleados.EmpleadoSindicalizado = " . $tipemp;
$ctrocstos == '' ? $ctrocstos = "" : $ctrocstos = " AND tblEmpleados.IdCentroCosto = " . $ctrocstos;

// Para el calculo de la fecha final se toma la de inicio y con strtotime se le suma 6 dias y develve un tiemestap
// date(" ") formatea el timestap resultante a una fecha en formato YYYY-MM-DD
$fechaf = date("Y-m-d", strtotime($fechai . "+ 6 days"));

// Catálogo de nóminas por rango de fechas
$nominas = [
	["nomina" => 1, "inicio" => "2025-12-29", "fin" => "2026-01-04"],
	["nomina" => 2, "inicio" => "2026-01-05", "fin" => "2026-01-11"],
	["nomina" => 3, "inicio" => "2026-01-12", "fin" => "2026-01-18"],
	["nomina" => 4, "inicio" => "2026-01-19", "fin" => "2026-01-25"],
	["nomina" => 5, "inicio" => "2026-01-26", "fin" => "2026-02-01"],
	["nomina" => 6, "inicio" => "2026-02-02", "fin" => "2026-02-08"],
	["nomina" => 7, "inicio" => "2026-02-09", "fin" => "2026-02-15"],
	["nomina" => 8, "inicio" => "2026-02-16", "fin" => "2026-02-22"],
	["nomina" => 9, "inicio" => "2026-02-23", "fin" => "2026-03-01"],
	["nomina" => 10, "inicio" => "2026-03-02", "fin" => "2026-03-08"],
	["nomina" => 11, "inicio" => "2026-03-09", "fin" => "2026-03-15"],
	["nomina" => 12, "inicio" => "2026-03-16", "fin" => "2026-03-22"],
	["nomina" => 13, "inicio" => "2026-03-23", "fin" => "2026-03-29"],
	["nomina" => 14, "inicio" => "2026-03-30", "fin" => "2026-04-05"],
	["nomina" => 15, "inicio" => "2026-04-06", "fin" => "2026-04-12"],
	["nomina" => 16, "inicio" => "2026-04-13", "fin" => "2026-04-19"],
	["nomina" => 17, "inicio" => "2026-04-20", "fin" => "2026-04-26"],
	["nomina" => 18, "inicio" => "2026-04-27", "fin" => "2026-05-03"],
	["nomina" => 19, "inicio" => "2026-05-04", "fin" => "2026-05-10"],
	["nomina" => 20, "inicio" => "2026-05-11", "fin" => "2026-05-17"],
	["nomina" => 21, "inicio" => "2026-05-18", "fin" => "2026-05-24"],
	["nomina" => 22, "inicio" => "2026-05-25", "fin" => "2026-05-31"],
	["nomina" => 23, "inicio" => "2026-06-01", "fin" => "2026-06-07"],
	["nomina" => 24, "inicio" => "2026-06-08", "fin" => "2026-06-14"],
	["nomina" => 25, "inicio" => "2026-06-15", "fin" => "2026-06-21"],
	["nomina" => 26, "inicio" => "2026-06-22", "fin" => "2026-06-28"],
	["nomina" => 27, "inicio" => "2026-06-29", "fin" => "2026-07-05"],
	["nomina" => 28, "inicio" => "2026-07-06", "fin" => "2026-07-12"],
	["nomina" => 29, "inicio" => "2026-07-13", "fin" => "2026-07-19"],
	["nomina" => 30, "inicio" => "2026-07-20", "fin" => "2026-07-26"],
	["nomina" => 31, "inicio" => "2026-07-27", "fin" => "2026-08-02"],
	["nomina" => 32, "inicio" => "2026-08-03", "fin" => "2026-08-09"],
	["nomina" => 33, "inicio" => "2026-08-10", "fin" => "2026-08-16"],
	["nomina" => 34, "inicio" => "2026-08-17", "fin" => "2026-08-23"],
	["nomina" => 35, "inicio" => "2026-08-24", "fin" => "2026-08-30"],
	["nomina" => 36, "inicio" => "2026-08-31", "fin" => "2026-09-06"],
	["nomina" => 37, "inicio" => "2026-09-07", "fin" => "2026-09-13"],
	["nomina" => 38, "inicio" => "2026-09-14", "fin" => "2026-09-20"],
	["nomina" => 39, "inicio" => "2026-09-21", "fin" => "2026-09-27"],
	["nomina" => 40, "inicio" => "2026-09-28", "fin" => "2026-10-04"],
	["nomina" => 41, "inicio" => "2026-10-05", "fin" => "2026-10-11"],
	["nomina" => 42, "inicio" => "2026-10-12", "fin" => "2026-10-18"],
	["nomina" => 43, "inicio" => "2026-10-19", "fin" => "2026-10-25"],
	["nomina" => 44, "inicio" => "2026-10-26", "fin" => "2026-11-01"],
	["nomina" => 45, "inicio" => "2026-11-02", "fin" => "2026-11-08"],
	["nomina" => 46, "inicio" => "2026-11-09", "fin" => "2026-11-15"],
	["nomina" => 47, "inicio" => "2026-11-16", "fin" => "2026-11-22"],
	["nomina" => 48, "inicio" => "2026-11-23", "fin" => "2026-11-29"],
	["nomina" => 49, "inicio" => "2026-11-30", "fin" => "2026-12-06"],
	["nomina" => 50, "inicio" => "2026-12-07", "fin" => "2026-12-13"],
	["nomina" => 51, "inicio" => "2026-12-14", "fin" => "2026-12-20"],
	["nomina" => 52, "inicio" => "2026-12-21", "fin" => "2026-12-27"],
];

// Buscar a qué nómina pertenece el rango seleccionado
$nominaActual = "N/A";
foreach ($nominas as $n) {
	if ($fechai >= $n["inicio"] && $fechaf <= $n["fin"]) {
		$nominaActual = $n["nomina"];
		break;
	}
}

// Validacion de campos para saber accion tomar
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $empno = trim(($_POST['empno']));

    if (empty($empno)){
        // Consulta principal de empleados
        // Query principal para obtencion y rellenado de datos
        /*
        Consulta anterior:
        Anteriormente se seleccionaba emplados que tenian registros en acc_transaction con INNER JOIN,
        tambien se traia NoEmp, Nombre, Puesto y clave de departamento y se filtraba empleados activos
        aplicando filtros extra y finalmente ordenaba por NoEmp, al ejecutarse la query dentro 
        del while se ejecutaban mas querys para cada empleado haciendo una consulta pesada

        Optimizacion realizada:
        Esta misma consulta se usa para iterar empleados, y las consultas mas pesadas como las transacciones
        y busqueda de descansos se ejecutan una sola vez fuera del bucle para mejorar el rendimiento
        */
        $queryEmpleados = "
        SELECT DISTINCT
            tblEmpleados.NoEmp,
            tblEmpleados.Nombre,
            tblPuestos.nombre AS Puesto,
            tblDepartamentos.clave AS DepartamentoClave
        FROM TLX032MXDB.dbo.tblEmpleados
        INNER JOIN acc_transaction ON tblEmpleados.NoEmp = acc_transaction.pin
        INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEmpleados.Puesto
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
        WHERE tblEmpleados.Bajas = 0
            $tipemp $ctrocstos $departamento
        ORDER BY tblEmpleados.NoEmp ASC;
        ";
        $resultEmpleados = sqlsrv_query($conn, $queryEmpleados);
        if ($resultEmpleados === false) {
            die("Error consulta empleados: " . print_r(sqlsrv_errors(), true));
        }

        // Consulta de transacciones
        /*
        Consulta anterior:
        Anteriormente se ejecutaba todo por cada empleado, ahora se ejecuta una sola vez y se agrupa para su uso

        Optimizacion realizada:
        - CTE permite aplicar ROW_NUMBER para (pin y hora redondeada) para seleccionar la primera marca por hora
        - Se filtra por rango de fechas
        - Se evitan duplicados cercanos como aquellos dentro de 1 hora con NOT EXISTS
        */
        $queryTransacciones = "
        WITH cte AS (
            SELECT id, pin, event_time,
                ROW_NUMBER() OVER (
                    PARTITION BY pin, DATEADD(hour, DATEDIFF(hour, 0, event_time), 0)
                    ORDER BY event_time
                ) AS rn
            FROM acc_transaction
            WHERE CONVERT(date, event_time) BETWEEN ? AND ?
        )
        SELECT cte.pin AS NoEmp,
            cte.event_time,
            DATEPART(WEEKDAY, cte.event_time) AS dia_semana
        FROM cte
        WHERE rn = 1
        AND NOT EXISTS (
            SELECT 1
            FROM cte c2
            WHERE c2.id = cte.id
                AND c2.rn = 1
                AND c2.event_time <> cte.event_time
                AND DATEDIFF(hour, c2.event_time, cte.event_time) BETWEEN 0 AND 1
        )
        ORDER BY cte.pin, cte.event_time ASC;
        ";
        $paramsDates = [$fechai, $fechaf];
        $resultTrans = sqlsrv_query($conn, $queryTransacciones, $paramsDates);
        if ($resultTrans === false) {
            die("Error consulta transacciones: " . print_r(sqlsrv_errors(), true));
        }

        // Agrupar transacciones por empleado en array para ahorro de tiempos al crear PDF
        $transacciones = [];
        while ($r = sqlsrv_fetch_array($resultTrans, SQLSRV_FETCH_ASSOC)) {
            $noemp = $r['NoEmp'];
            $transacciones[$noemp][] = $r;
        }

        // Consulta de descansos
        /*
        Contexto anterior:
        La busqueda de descansos por empleado se ejecutaba por cada uno de ellos haciendo una consulta mas lenta y pesada.

        Optimizacion realizada:
        - En esta nueva consulta se traen los registros por empleado basados en el rango de fechas que fue proporcionado
        - Se hace un LEFT JOIN a la tabla de tipos para obtener nombres por dia
        - Se almacena en array el resultado de la consulta a fin de tenerla lista para su posterior uso
        */

        $queryDescansos = "
        SELECT d.noemp,
            d.fecha,
            tipoL.nombre AS lunes,
            tipoM.nombre AS martes,
            tipoMi.nombre AS miercoles,
            tipoJ.nombre AS jueves,
            tipoV.nombre AS viernes,
            tipoS.nombre AS sabado,
            tipoD.nombre AS domingo
        FROM TLX002MXDB.dbo.tblAsistenciasDescansos d
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoL ON d.lunes = tipoL.id
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoM ON d.martes = tipoM.id
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoMi ON d.miercoles = tipoMi.id
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoJ ON d.jueves = tipoJ.id
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoV ON d.viernes = tipoV.id
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoS ON d.sabado = tipoS.id
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoD ON d.domingo = tipoD.id
        WHERE d.fecha BETWEEN ? AND ?;
        ";
        // 2026-01-26  2026-01-26
        $resultDesc = sqlsrv_query($conn, $queryDescansos, $paramsDates);
        if ($resultDesc === false) {
            die("Error consulta descansos: " . print_r(sqlsrv_errors(), true));
        }

        // Agrupar descansos por empleado en array basados en la query anterior de la busqueda de descansos
        $descansos = [];
        while ($r = sqlsrv_fetch_array($resultDesc, SQLSRV_FETCH_ASSOC)) {
            $noemp = $r['noemp'];
            $descansos[$noemp][] = $r;
        }

        // Generación del PDF usando el array (transaccion y descansos)
        $pdf = new FPDF('P');
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->Ln(20);
        $pdf->SetFont('Arial', '', 8);        
        $x = 2;
        $y = 2;
        $cont = 0;

        // Recorrido de empleados para consulta y llenado de datos
        while ($fila = sqlsrv_fetch_array($resultEmpleados, SQLSRV_FETCH_NUMERIC)) {
            $noemp  = $fila[0];
            $nombre = $fila[1];
            $puesto = $fila[2];
            $depto  = $fila[3];

            // Dibujo del marco y datos básicos
            $pdf->rect($x, $y, 205, 142);
            $pdf->Image('../../img/logo.jpg', $x + 5, $y + 5, 60);
            $pdf->SetXY($x + 5, $y + 10);            
            $pdf->Cell(15, 10, "DEPTO:");
            $pdf->Cell(20, 10, $depto);
            $pdf->Cell(15, 10, "NUM:");
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(110, 10, "0" . $noemp);
            $pdf->Cell(20, 10, "NUM:");
            $pdf->Cell(20, 10, $noemp);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Ln(5);
            $pdf->SetX($x + 5);
            $pdf->Cell(20, 10, utf8_decode($nombre));
            $pdf->Ln(5);
            $pdf->SetXY($x + 178, $y + 5);
            $pdf->Cell(20, 10, "NOMINA NO." . $nominaActual);
            $pdf->Ln(15);
            $pdf->SetX($x+5);
            $pdf->Cell(20, 10, "DEL   $fechai   AL   $fechaf");
            $pdf->Ln(5);
            $pdf->SetX($x + 5);
            $pdf->Cell(20, 10, "14");
            $pdf->SetX($x + 30);
            $pdf->Cell(20, 10, "020006");
            $pdf->SetX($x + 60);
            $pdf->Cell(20, 10, "87");
            $pdf->Ln(5);
            $pdf->SetX($x + 5);
            $pdf->Cell(20, 10, utf8_decode($puesto));
            $pdf->rect($x + 5, $y + 50, 80, 90);
            $pdf->SetXY($x + 5, $y + 50);

            $pdf->SetFont('Arial', 'B', 7);
            $pdf->multiCell(2, 5, "DIA L M M J V S D");
            $pdf->SetXY($x + 10, $y + 50);
            $pdf->Cell(15, 5, "I TURNO");
            $pdf->Cell(15, 5, "I TURNO");
            $pdf->Cell(24, 5, "III TURNO");
            $yi = $pdf->GETY();
            $xi = $pdf->GETX();
            $pdf->multiCell(20, 20, "TIEMPOS");
            $pdf->SETY($yi);
            $pdf->SETX($xi + 15);
            $pdf->SetXY($x + 10, $y + 55);
            $pdf->Cell(15, 5, "ENTRADA");
            $pdf->Cell(15, 5, "SALIDA");
            $pdf->Cell(15, 5, "ENTRADA");
            $pdf->SetXY($x + 10, $y + 60);
            $pdf->Cell(15, 5, "III TURNO");
            $pdf->Cell(15, 5, "II TURNO");
            $pdf->Cell(15, 5, "II TURNO");
            $pdf->SetXY($x + 10, $y + 65);
            $pdf->Cell(15, 5, "SALIDA");
            $pdf->Cell(15, 5, "ENTRADA");
            $pdf->Cell(15, 5, "SALIDA");
            $pdf->line($x + 10, $y + 55, $x + 55, $y + 55);
            $pdf->line($x + 10, $y + 60, $x + 55, $y + 60);
            $pdf->line($x + 10, $y + 65, $x + 55, $y + 65);
            $pdf->line($x + 5, $y + 70, $x + 85, $y + 70);
            $pdf->line($x + 5, $y + 80, $x + 85, $y + 80);
            $pdf->line($x + 5, $y + 90, $x + 85, $y + 90);
            $pdf->line($x + 5, $y + 100, $x + 85, $y + 100);
            $pdf->line($x + 5, $y + 110, $x + 85, $y + 110);
            $pdf->line($x + 5, $y + 120, $x + 85, $y + 120);
            $pdf->line($x + 5, $y + 130, $x + 85, $y + 130);
            $pdf->line($x + 10, $y + 50, $x + 10, $y + 140);
            $pdf->line($x + 25, $y + 50, $x + 25, $y + 140);
            $pdf->line($x + 40, $y + 50, $x + 40, $y + 140);
            $pdf->line($x + 55, $y + 50, $x + 55, $y + 140);

            $pdf->rect($x + 90, $y + 20, 110, 120);
            $pdf->SetXY($x + 90, $y + 20);
            $pdf->Cell(2, 5, "");
            $pdf->Cell(22, 5, "CONCEPTO");
            $pdf->Cell(20, 5, "# DIAS");
            $pdf->Cell(16, 5, "# HRS");
            $pdf->Cell(22, 5, "CAMBIO PROV");
            $pdf->Cell(20, 5, "OBSERVACIONES");
            $pdf->SetXY($x + 152, $y + 25);
            $pdf->Cell(20, 5, "AL PUESTO");
            $pdf->line($x + 110, $y + 20, $x + 110, $y + 140);
            $pdf->line($x + 130, $y + 20, $x + 130, $y + 140);
            $pdf->line($x + 150, $y + 20, $x + 150, $y + 140);
            $pdf->line($x + 170, $y + 20, $x + 170, $y + 140);
            for ($sub = 30; $sub <= 130; $sub += 10) {
                $pdf->line($x + 90, $y + $sub, $x + 200, $y + $sub);
            }
            $pdf->SetFont('Arial', '', 7);

            // Variables para manejo de horas
            $horasx = 12;
            $horasy = $y;
            $antrow = 0;

            // Relleno de transacciones con foreach desde el array 
            if (isset($transacciones[$noemp])) {
                foreach ($transacciones[$noemp] as $t) {
                    $eventTime = $t['event_time'];
                    if ($eventTime instanceof DateTime) {
                        $hora = $eventTime->format("H:i:s");
                    } else {
                        $hora = date("H:i:s", strtotime($eventTime));
                    }
                    $dia = (int)$t['dia_semana'];

                    if ($dia === $antrow) {
                        $horasx += 15;
                    } else {
                        $horasx = 12;
                    }

                    switch ($dia) {
                        case 2: $pdf->SetXY($x + $horasx, $horasy + 70); break;
                        case 3: $pdf->SetXY($x + $horasx, $horasy + 80); break;
                        case 4: $pdf->SetXY($x + $horasx, $horasy + 90); break;
                        case 5: $pdf->SetXY($x + $horasx, $horasy + 100); break;
                        case 6: $pdf->SetXY($x + $horasx, $horasy + 110); break;
                        case 7: $pdf->SetXY($x + $horasx, $horasy + 120); break;
                        case 1: $pdf->SetXY($x + $horasx, $horasy + 130); break;
                    }
                    $pdf->Cell(15, 5, $hora);
                    $antrow = $dia;
                }
            }

            // Relleno de transacciones con foreach desde el array (Query de transacciones)
            $pdf->SetFont('Arial', 'B', 10);
            if (isset($descansos[$noemp])) {
                foreach ($descansos[$noemp] as $d) {
                    if (!empty($d['lunes'])) {
                        $pdf->SetXY(60, $horasy + 70);
                        $pdf->Cell(15, 5, $d['lunes']);
                    }
                    if (!empty($d['martes'])) {
                        $pdf->SetXY(60, $horasy + 80);
                        $pdf->Cell(15, 5, $d['martes']);
                    }
                    if (!empty($d['miercoles'])) {
                        $pdf->SetXY(60, $horasy + 90);
                        $pdf->Cell(15, 5, $d['miercoles']);
                    }
                    if (!empty($d['jueves'])) {
                        $pdf->SetXY(60, $horasy + 100);
                        $pdf->Cell(15, 5, $d['jueves']);
                    }
                    if (!empty($d['viernes'])) {
                        $pdf->SetXY(60, $horasy + 110);
                        $pdf->Cell(15, 5, $d['viernes']);
                    }
                    if (!empty($d['sabado'])) {
                        $pdf->SetXY(60, $horasy + 120);
                        $pdf->Cell(15, 5, $d['sabado']);
                    }
                    if (!empty($d['domingo'])) {
                        $pdf->SetXY(60, $horasy + 130);
                        $pdf->Cell(15, 5, $d['domingo']);
                    }
                }
            }

            $y += 150;
            $cont++;
            if ($cont % 2 === 0) {
                $pdf->AddPage();
                $y = 2;
            }
        }

        $pdf->Output();
            
    }
    // Manejo en caso de que se escriba un numero de empleado
    // Buscar centro de costos, tipo de empleado y departamento
    // Imprimir PDF para solo este empleado 
    else {
        // Recibir número de empleado desde el formulario
        $empno = $_POST['empno'] ?? '';

        // Armar filtro adicional
        $empnoFiltro = $empno === '' ? '' : " AND tblEmpleados.NoEmp=" . intval($empno);

        // Consulta principal de empleados con el nuevo filtro
        $queryEmpleados = "
        SELECT DISTINCT
            tblEmpleados.NoEmp,
            tblEmpleados.Nombre,
            tblPuestos.nombre AS Puesto,
            tblDepartamentos.clave AS DepartamentoClave
        FROM TLX032MXDB.dbo.tblEmpleados
        INNER JOIN acc_transaction ON tblEmpleados.NoEmp = acc_transaction.pin
        INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEmpleados.Puesto
        INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
        WHERE tblEmpleados.Bajas = 0
            $tipemp $ctrocstos $departamento $empnoFiltro
        ORDER BY tblEmpleados.NoEmp ASC;
        ";

        $resultEmpleados = sqlsrv_query($conn, $queryEmpleados);
        if ($resultEmpleados === false) {
            die("Error consulta empleados: " . print_r(sqlsrv_errors(), true));
        }

        // Consulta de transacciones
        /*
        Consulta anterior:
        Anteriormente se ejecutaba todo por cada empleado, ahora se ejecuta una sola vez y se agrupa para su uso

        Optimizacion realizada:
        - CTE permite aplicar ROW_NUMBER para (pin y hora redondeada) para seleccionar la primera marca por hora
        - Se filtra por rango de fechas
        - Se evitan duplicados cercanos como aquellos dentro de 1 hora con NOT EXISTS
        */
        $queryTransacciones = "
        WITH cte AS (
            SELECT id, pin, event_time,
                ROW_NUMBER() OVER (
                    PARTITION BY pin, DATEADD(hour, DATEDIFF(hour, 0, event_time), 0)
                    ORDER BY event_time
                ) AS rn
            FROM acc_transaction
            WHERE CONVERT(date, event_time) BETWEEN ? AND ?
        )
        SELECT cte.pin AS NoEmp,
            cte.event_time,
            DATEPART(WEEKDAY, cte.event_time) AS dia_semana
        FROM cte
        WHERE rn = 1
        AND NOT EXISTS (
            SELECT 1
            FROM cte c2
            WHERE c2.id = cte.id
                AND c2.rn = 1
                AND c2.event_time <> cte.event_time
                AND DATEDIFF(hour, c2.event_time, cte.event_time) BETWEEN 0 AND 1
        )
        ORDER BY cte.pin, cte.event_time ASC;
        ";
        $paramsDates = [$fechai, $fechaf];
        $resultTrans = sqlsrv_query($conn, $queryTransacciones, $paramsDates);
        if ($resultTrans === false) {
            die("Error consulta transacciones: " . print_r(sqlsrv_errors(), true));
        }

        // Agrupar transacciones por empleado en array para ahorro de tiempos al crear PDF
        $transacciones = [];
        while ($r = sqlsrv_fetch_array($resultTrans, SQLSRV_FETCH_ASSOC)) {
            $noemp = $r['NoEmp'];
            $transacciones[$noemp][] = $r;
        }

        // Consulta de descansos
        /*
        Contexto anterior:
        La busqueda de descansos por empleado se ejecutaba por cada uno de ellos haciendo una consulta mas lenta y pesada.

        Optimizacion realizada:
        - En esta nueva consulta se traen los registros por empleado basados en el rango de fechas que fue proporcionado
        - Se hace un LEFT JOIN a la tabla de tipos para obtener nombres por dia
        - Se almacena en array el resultado de la consulta a fin de tenerla lista para su posterior uso
        */

        $queryDescansos = "
        SELECT d.noemp,
            d.fecha,
            tipoL.nombre AS lunes,
            tipoM.nombre AS martes,
            tipoMi.nombre AS miercoles,
            tipoJ.nombre AS jueves,
            tipoV.nombre AS viernes,
            tipoS.nombre AS sabado,
            tipoD.nombre AS domingo
        FROM TLX002MXDB.dbo.tblAsistenciasDescansos d
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoL ON d.lunes = tipoL.id
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoM ON d.martes = tipoM.id
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoMi ON d.miercoles = tipoMi.id
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoJ ON d.jueves = tipoJ.id
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoV ON d.viernes = tipoV.id
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoS ON d.sabado = tipoS.id
        LEFT JOIN TLX002MXDB.dbo.tblAsistenciaDescansosTipo tipoD ON d.domingo = tipoD.id
        WHERE d.fecha BETWEEN ? AND ?;
        ";
        // 2026-01-26  2026-01-26
        $resultDesc = sqlsrv_query($conn, $queryDescansos, $paramsDates);
        if ($resultDesc === false) {
            die("Error consulta descansos: " . print_r(sqlsrv_errors(), true));
        }

        // Agrupar descansos por empleado en array basados en la query anterior de la busqueda de descansos
        $descansos = [];
        while ($r = sqlsrv_fetch_array($resultDesc, SQLSRV_FETCH_ASSOC)) {
            $noemp = $r['noemp'];
            $descansos[$noemp][] = $r;
        }

        // Generación del PDF usando el array (transaccion y descansos)
        $pdf = new FPDF('P');
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();
        $pdf->Ln(20);
        $pdf->SetFont('Arial', '', 8);
        $x = 2;
        $y = 2;
        $cont = 0;

        // Recorrido de empleados para consulta y llenado de datos
        while ($fila = sqlsrv_fetch_array($resultEmpleados, SQLSRV_FETCH_NUMERIC)) {
            $noemp  = $fila[0];
            $nombre = $fila[1];
            $puesto = $fila[2];
            $depto  = $fila[3];

            // Dibujo del marco y datos básicos
            $pdf->rect($x, $y, 205, 142);
            $pdf->Image('../../img/logo.jpg', $x + 5, $y + 5, 60);
            $pdf->SetXY($x + 5, $y + 10);
            $pdf->Cell(15, 10, "DEPTO:");
            $pdf->Cell(20, 10, $depto);
            $pdf->Cell(15, 10, "NUM:");
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(110, 10, "0" . $noemp);
            $pdf->Cell(20, 10, "NUM:");
            $pdf->Cell(20, 10, $noemp);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Ln(5);
            $pdf->SetX($x + 5);
            $pdf->Cell(20, 10, utf8_decode($nombre));
            $pdf->Ln(5);
            $pdf->SetXY($x + 178, $y + 5);
            $pdf->Cell(20, 10, "NOMINA NO." . $nominaActual);
            $pdf->Ln(15);
            $pdf->SetX($x+5);
            $pdf->Cell(20, 10, "DEL   $fechai   AL   $fechaf");
            $pdf->Ln(5);
            $pdf->SetX($x + 5);
            $pdf->Cell(20, 10, "14");
            $pdf->SetX($x + 30);
            $pdf->Cell(20, 10, "020006");
            $pdf->SetX($x + 60);
            $pdf->Cell(20, 10, "87");
            $pdf->Ln(5);
            $pdf->SetX($x + 5);
            $pdf->Cell(20, 10, utf8_decode($puesto));
            $pdf->rect($x + 5, $y + 50, 80, 90);
            $pdf->SetXY($x + 5, $y + 50);

            $pdf->SetFont('Arial', 'B', 7);
            $pdf->multiCell(2, 5, "DIA L M M J V S D");
            $pdf->SetXY($x + 10, $y + 50);
            $pdf->Cell(15, 5, "I TURNO");
            $pdf->Cell(15, 5, "I TURNO");
            $pdf->Cell(24, 5, "III TURNO");
            $yi = $pdf->GETY();
            $xi = $pdf->GETX();
            $pdf->multiCell(20, 20, "TIEMPOS");
            $pdf->SETY($yi);
            $pdf->SETX($xi + 15);
            $pdf->SetXY($x + 10, $y + 55);
            $pdf->Cell(15, 5, "ENTRADA");
            $pdf->Cell(15, 5, "SALIDA");
            $pdf->Cell(15, 5, "ENTRADA");
            $pdf->SetXY($x + 10, $y + 60);
            $pdf->Cell(15, 5, "III TURNO");
            $pdf->Cell(15, 5, "II TURNO");
            $pdf->Cell(15, 5, "II TURNO");
            $pdf->SetXY($x + 10, $y + 65);
            $pdf->Cell(15, 5, "SALIDA");
            $pdf->Cell(15, 5, "ENTRADA");
            $pdf->Cell(15, 5, "SALIDA");
            $pdf->line($x + 10, $y + 55, $x + 55, $y + 55);
            $pdf->line($x + 10, $y + 60, $x + 55, $y + 60);
            $pdf->line($x + 10, $y + 65, $x + 55, $y + 65);
            $pdf->line($x + 5, $y + 70, $x + 85, $y + 70);
            $pdf->line($x + 5, $y + 80, $x + 85, $y + 80);
            $pdf->line($x + 5, $y + 90, $x + 85, $y + 90);
            $pdf->line($x + 5, $y + 100, $x + 85, $y + 100);
            $pdf->line($x + 5, $y + 110, $x + 85, $y + 110);
            $pdf->line($x + 5, $y + 120, $x + 85, $y + 120);
            $pdf->line($x + 5, $y + 130, $x + 85, $y + 130);
            $pdf->line($x + 10, $y + 50, $x + 10, $y + 140);
            $pdf->line($x + 25, $y + 50, $x + 25, $y + 140);
            $pdf->line($x + 40, $y + 50, $x + 40, $y + 140);
            $pdf->line($x + 55, $y + 50, $x + 55, $y + 140);

            $pdf->rect($x + 90, $y + 20, 110, 120);
            $pdf->SetXY($x + 90, $y + 20);
            $pdf->Cell(2, 5, "");
            $pdf->Cell(22, 5, "CONCEPTO");
            $pdf->Cell(20, 5, "# DIAS");
            $pdf->Cell(16, 5, "# HRS");
            $pdf->Cell(22, 5, "CAMBIO PROV");
            $pdf->Cell(20, 5, "OBSERVACIONES");
            $pdf->SetXY($x + 152, $y + 25);
            $pdf->Cell(20, 5, "AL PUESTO");
            $pdf->line($x + 110, $y + 20, $x + 110, $y + 140);
            $pdf->line($x + 130, $y + 20, $x + 130, $y + 140);
            $pdf->line($x + 150, $y + 20, $x + 150, $y + 140);
            $pdf->line($x + 170, $y + 20, $x + 170, $y + 140);
            for ($sub = 30; $sub <= 130; $sub += 10) {
                $pdf->line($x + 90, $y + $sub, $x + 200, $y + $sub);
            }
            $pdf->SetFont('Arial', '', 7);

            // Variables para manejo de horas
            $horasx = 12;
            $horasy = $y;
            $antrow = 0;

            // Relleno de transacciones con foreach desde el array 
            if (isset($transacciones[$noemp])) {
                foreach ($transacciones[$noemp] as $t) {
                    $eventTime = $t['event_time'];
                    if ($eventTime instanceof DateTime) {
                        $hora = $eventTime->format("H:i:s");
                    } else {
                        $hora = date("H:i:s", strtotime($eventTime));
                    }
                    $dia = (int)$t['dia_semana'];

                    if ($dia === $antrow) {
                        $horasx += 15;
                    } else {
                        $horasx = 12;
                    }

                    switch ($dia) {
                        case 2: $pdf->SetXY($x + $horasx, $horasy + 70); break;
                        case 3: $pdf->SetXY($x + $horasx, $horasy + 80); break;
                        case 4: $pdf->SetXY($x + $horasx, $horasy + 90); break;
                        case 5: $pdf->SetXY($x + $horasx, $horasy + 100); break;
                        case 6: $pdf->SetXY($x + $horasx, $horasy + 110); break;
                        case 7: $pdf->SetXY($x + $horasx, $horasy + 120); break;
                        case 1: $pdf->SetXY($x + $horasx, $horasy + 130); break;
                    }
                    $pdf->Cell(15, 5, $hora);
                    $antrow = $dia;
                }
            }

            // Relleno de transacciones con foreach desde el array (Query de transacciones)
            $pdf->SetFont('Arial', 'B', 10);
            if (isset($descansos[$noemp])) {
                foreach ($descansos[$noemp] as $d) {
                    if (!empty($d['lunes'])) {
                        $pdf->SetXY(60, $horasy + 70);
                        $pdf->Cell(15, 5, $d['lunes']);
                    }
                    if (!empty($d['martes'])) {
                        $pdf->SetXY(60, $horasy + 80);
                        $pdf->Cell(15, 5, $d['martes']);
                    }
                    if (!empty($d['miercoles'])) {
                        $pdf->SetXY(60, $horasy + 90);
                        $pdf->Cell(15, 5, $d['miercoles']);
                    }
                    if (!empty($d['jueves'])) {
                        $pdf->SetXY(60, $horasy + 100);
                        $pdf->Cell(15, 5, $d['jueves']);
                    }
                    if (!empty($d['viernes'])) {
                        $pdf->SetXY(60, $horasy + 110);
                        $pdf->Cell(15, 5, $d['viernes']);
                    }
                    if (!empty($d['sabado'])) {
                        $pdf->SetXY(60, $horasy + 120);
                        $pdf->Cell(15, 5, $d['sabado']);
                    }
                    if (!empty($d['domingo'])) {
                        $pdf->SetXY(60, $horasy + 130);
                        $pdf->Cell(15, 5, $d['domingo']);
                    }
                }
            }

            $y += 150;
            $cont++;
            if ($cont % 2 === 0) {
                $pdf->AddPage();
                $y = 2;
            }
        }

        $pdf->Output();

    }
}