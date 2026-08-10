<?php
/**
 * hook.php — API de HookMesh
 * 
 * Endpoints disponibles (vía GET o POST según el caso):
 *   ?DeletePresentacionHook        POST  — Eliminar Hook_Enc + Sub
 *   ?obtenerEtiquetasHook          POST  — Obtener etiquetas de impresión disponibles
 *   ?guardarEtiquetasHook          POST  — Guardar etiquetas en Hook_Etiquetas
 *   ?cargarPresentacionesAutomatico POST — Crear presentaciones Hook a partir de etiquetas
 *   ?obtenerRollosMermaHook        POST  — Obtener rollos candidatos a merma (ML < 1900)
 *   ?guardarMermaHook              JSON  — Guardar/regresar selección de merma
 *
 * Llamada desde JS (ejemplo):
 *   fetch('api/hook.php?guardarEtiquetasHook', { method: 'POST', body: formData })
 */

require_once "../../conexion.php";
require_once '../../Session/seguridad.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

class HookMesh
{
    // ─────────────────────────────────────────────
    // HELPERS INTERNOS
    // ─────────────────────────────────────────────

    /**
     * Devuelve las horas del turno desde tblBitTurnohoras (TLX002MXDB).
     */
    private function consultaHorasxturno($turno)
    {
        $Conecta = new ClassConexion();
        $conn = $Conecta->conexion("TLX002MXDB");
        $query  = "SELECT * FROM tblBitTurnohoras WHERE turno = ?";
        $result = sqlsrv_query($conn, $query, array($turno));
        $array  = array();
        while ($row = sqlsrv_fetch_array($result)) {
            $array[] = [
                'id'      => $row['id'],
                'turno'   => $row['turno'],
                'hora'    => $row['hora'],
                'horastr' => $row['horastr'],
            ];
        }
        return $array;
    }

    /**
     * Inserta una fila en Hook_Sub y, si no existe, en tblBitPresentacionGolpes.
     */
    private function insertarSubHook($idHE, $idHoras, $foliobit)
    {
        $Conecta = new ClassConexion();
        $conn    = $Conecta->conexion("TLX004MXDB");

        $query = "INSERT INTO tblMXPR_Produccion_Hook_Sub
                      (idPresentacionEncHook, hora, rollos, ml, mmc, accml, accmc)
                  VALUES (?, ?, 0, 0, 0, 0, 0)";
        sqlsrv_query($conn, $query, array($idHE, $idHoras));

        $Conecta2 = new ClassConexion();
        $conn2    = $Conecta2->conexion("TLX002MXDB");

        $sql  = "SELECT COUNT(*) as count FROM tblBitPresentacionGolpes
                 WHERE idbitacora = ? AND hora = ?";
        $stmt = sqlsrv_query($conn2, $sql, array($foliobit, $idHoras));
        $row  = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($row['count'] == 0) {
            $queryGolpes = "INSERT INTO tblBitPresentacionGolpes
                                (idbitacora, hora, golpes, merma)
                            VALUES (?, ?, ?, ?)";
            sqlsrv_query($conn2, $queryGolpes, array($foliobit, $idHoras, 0, 0));
        }
    }

    // ─────────────────────────────────────────────
    // ENDPOINTS PÚBLICOS
    // ─────────────────────────────────────────────
    /**
     * Elimina el encabezado Hook y sus filas Sub.
     * POST: folio, notbl
     */
    function deletePresentacionHook()
    {
        $Conecta = new ClassConexion();
        $conn    = $Conecta->conexion("TLX004MXDB");

        $folio = $_POST["folio"];
        $notbl = $_POST["notbl"];

        $queryId  = "SELECT idHE FROM tblMXPR_Produccion_Hook_Enc
                     WHERE folio = ? AND NoTabla = ?";
        $resultId = sqlsrv_query($conn, $queryId, array($folio, $notbl));
        $row      = sqlsrv_fetch_array($resultId, SQLSRV_FETCH_ASSOC);

        if (!$row) {
            http_response_code(200);
            return;
        }

        $idHE = $row['idHE'];

        $querySub = "DELETE FROM tblMXPR_Produccion_Hook_Sub WHERE idPresentacionEncHook = ?";
        sqlsrv_query($conn, $querySub, array($idHE));

        $queryEnc = "DELETE FROM tblMXPR_Produccion_Hook_Enc WHERE idHE = ?";
        $result   = sqlsrv_query($conn, $queryEnc, array($idHE));

        $result === false ? http_response_code(500) : http_response_code(200);
    }

    /**
     * Devuelve las etiquetas de impresión disponibles para un folio/clave/turno.
     * Excluye los rollos ya marcados como merma (esMerma = 1).
     * POST: folio, notbl, [clave opcional]
     */
    function obtenerEtiquetasHook()
    {
        $Conecta = new ClassConexion();
        $conn    = $Conecta->conexion("TLX004MXDB");

        $folio          = $_POST['folio']  ?? null;
        $notbl          = $_POST['notbl']  ?? null;
        $claveOpcional  = $_POST['clave']  ?? null;

        if (!$folio || !$notbl) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros folio y notbl son requeridos']);
            return;
        }

        $clave = null;
        $turno = null;

        if ($claveOpcional) {
            // Clave viene directo en POST
            $clave = $claveOpcional;

            $queryTurno  = "SELECT Turno FROM tblEncabezadoBitacora WHERE IdEncabezadoBItacora = ?";
            $resultTurno = sqlsrv_query($conn, $queryTurno, array($folio));

            if ($resultTurno === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Error al obtener turno']);
                return;
            }
            $rowTurno = sqlsrv_fetch_array($resultTurno, SQLSRV_FETCH_ASSOC);
            if (!$rowTurno) {
                http_response_code(404);
                echo json_encode(['error' => 'Folio no encontrado']);
                return;
            }
            $turno = $rowTurno['Turno'];

        } else {
            // Obtener clave desde Hook_Enc
            $queryClaveHook  = "SELECT clave FROM tblMXPR_Produccion_Hook_Enc
                                WHERE folio = ? AND NoTabla = ?";
            $resultClaveHook = sqlsrv_query($conn, $queryClaveHook, array($folio, $notbl));

            if ($resultClaveHook === false) {
                error_log("Error obtenerEtiquetasHook (clave): " . print_r(sqlsrv_errors(), true));
                http_response_code(500);
                echo json_encode(['error' => 'Error al obtener clave de Hook_Enc']);
                return;
            }
            $rowClave = sqlsrv_fetch_array($resultClaveHook, SQLSRV_FETCH_ASSOC);
            if (!$rowClave) {
                http_response_code(404);
                echo json_encode(['error' => 'No se encontró registro en Hook_Enc']);
                return;
            }
            $clave = $rowClave['clave'];

            $queryTurno  = "SELECT Turno FROM tblEncabezadoBitacora WHERE IdEncabezadoBItacora = ?";
            $resultTurno = sqlsrv_query($conn, $queryTurno, array($folio));

            if ($resultTurno === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Error al obtener turno de Encabezado']);
                return;
            }
            $rowTurno = sqlsrv_fetch_array($resultTurno, SQLSRV_FETCH_ASSOC);
            if (!$rowTurno) {
                http_response_code(404);
                echo json_encode(['error' => 'No se encontró turno en Encabezado']);
                return;
            }
            $turno = $rowTurno['Turno'];
        }

        // Etiquetas disponibles (excluye las ya marcadas como merma)
        $query = "SELECT e.NumeroRollo, e.MetrosLineales, e.Clave,
                         e.Turno, e.IdEncabezadoBitacora, e.FechaCaptura,
                         tblVEC.factor
                  FROM tblMXPRBitacoraEtiquetasImpresion e
                  INNER JOIN TLX002MXDB.dbo.tblValeEClaves tblVEC
                      ON tblVEC.NoClave = CAST(e.Clave AS VARCHAR(10))
                  LEFT JOIN tblMXPR_Hook_Merma m
                      ON m.folio = e.IdEncabezadoBitacora
                     AND m.turno = e.Turno
                     AND m.NumeroRollo = e.NumeroRollo
                     AND m.esMerma = 1
                  WHERE e.IdEncabezadoBitacora = ?
                    AND e.Clave = ?
                    AND e.Turno = ?
                    AND m.id IS NULL
                  ORDER BY e.FechaCaptura ASC";

        $result = sqlsrv_query($conn, $query, array($folio, $clave, $turno));

        if ($result === false) {
            error_log("Error obtenerEtiquetasHook (etiquetas): " . print_r(sqlsrv_errors(), true));
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar etiquetas']);
            return;
        }

        $array = array();
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $array[] = [
                'NumeroRollo'    => intval($row['NumeroRollo']),
                'MetrosLineales' => floatval($row['MetrosLineales']),
                'Clave'          => $row['Clave'],
                'factor'         => floatval($row['factor']),
            ];
        }

        http_response_code(200);
        echo json_encode($array);
    }

    /**
     * Guarda etiquetas en tblMXPR_Produccion_Hook_Etiquetas (evita duplicados).
     * POST: folio, clave, idEncabezadoHook
     */
    function guardarEtiquetasHook()
    {
        $Conecta = new ClassConexion();
        $conn    = $Conecta->conexion("TLX004MXDB");

        $folio            = $_POST['folio']            ?? null;
        $clave            = $_POST['clave']            ?? null;
        $idEncabezadoHook = $_POST['idEncabezadoHook'] ?? null;

        if (!$folio || !$clave || !$idEncabezadoHook) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros folio, clave e idEncabezadoHook son requeridos']);
            return;
        }

        // Obtener turno
        $queryTurno  = "SELECT Turno FROM tblEncabezadoBitacora WHERE IdEncabezadoBItacora = ?";
        $resultTurno = sqlsrv_query($conn, $queryTurno, array($folio));

        if ($resultTurno === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener turno']);
            return;
        }
        $rowTurno = sqlsrv_fetch_array($resultTurno, SQLSRV_FETCH_ASSOC);
        if (!$rowTurno) {
            http_response_code(404);
            echo json_encode(['error' => 'Folio no encontrado']);
            return;
        }
        $turno = $rowTurno['Turno'];

        // Obtener etiquetas fuente
        $queryEtiquetas = "SELECT e.NumeroEtiqueta, e.NumeroRollo, e.MetrosLineales,
                                  e.Clave, e.Turno, e.Supervisor,
                                  e.DiametroSEWEmpalmeHMUFlechaA, e.DiametroSEWEmpalmeNBLFlechaA,
                                  e.DiametroSEWEmpalmeHBUFlechaA, e.Flecha,
                                  e.DiametroRolloMetros,
                                  e.DiametroSEWEmpalmeHMUFlechaB, e.DiametroSEWEmpalmeNBLFlechaB,
                                  e.DiametroSEWEmpalmeHBUFlechaB, e.FechaCaptura,
                                  tblVEC.factor
                           FROM tblMXPRBitacoraEtiquetasImpresion e
                           INNER JOIN TLX002MXDB.dbo.tblValeEClaves tblVEC
                               ON tblVEC.NoClave = CAST(e.Clave AS VARCHAR(10))
                           WHERE e.IdEncabezadoBitacora = ?
                             AND e.Clave = ?
                             AND e.Turno = ?
                           ORDER BY e.FechaCaptura ASC";

        $resultEtiquetas = sqlsrv_query($conn, $queryEtiquetas, array($folio, $clave, $turno));

        if ($resultEtiquetas === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener etiquetas']);
            return;
        }

        $cantidadGuardadas  = 0;
        $cantidadDuplicadas = 0;
        $accML = 0;
        $accMC = 0;

        while ($row = sqlsrv_fetch_array($resultEtiquetas, SQLSRV_FETCH_ASSOC)) {
            // Verificar duplicado
            $queryExiste  = "SELECT TOP 1 id FROM tblMXPR_Produccion_Hook_Etiquetas
                             WHERE folio = ? AND Clave = ? AND NumeroEtiqueta = ?";
            $resultExiste = sqlsrv_query($conn, $queryExiste,
                                         array($folio, $clave, $row['NumeroEtiqueta']));

            if ($resultExiste === false) {
                error_log("Error verificando duplicados: " . print_r(sqlsrv_errors(), true));
                continue;
            }

            if (sqlsrv_fetch_array($resultExiste, SQLSRV_FETCH_ASSOC)) {
                $cantidadDuplicadas++;
                continue;
            }

            $mm2    = (floatval($row['MetrosLineales']) * floatval($row['factor'])) / 1000;
            $accML += floatval($row['MetrosLineales']) / 1000;
            $accMC += $mm2;

            $queryInsert = "INSERT INTO tblMXPR_Produccion_Hook_Etiquetas
                (idEncabezadoHook, folio, Clave, NumeroEtiqueta, NumeroRollo,
                 MetrosLineales, MM2, Factor, Supervisor,
                 DiametroSEWEmpalmeHMUFlechaA, DiametroSEWEmpalmeNBLFlechaA,
                 DiametroSEWEmpalmeHBUFlechaA, Flecha, DiametroRolloMetros,
                 DiametroSEWEmpalmeHMUFlechaB, DiametroSEWEmpalmeNBLFlechaB,
                 DiametroSEWEmpalmeHBUFlechaB, FechaCaptura, AccML, AccMC)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $resultInsert = sqlsrv_query($conn, $queryInsert, array(
                $idEncabezadoHook,
                $folio, $clave,
                $row['NumeroEtiqueta'], $row['NumeroRollo'],
                floatval($row['MetrosLineales']), $mm2, floatval($row['factor']),
                $row['Supervisor'],
                floatval($row['DiametroSEWEmpalmeHMUFlechaA']),
                floatval($row['DiametroSEWEmpalmeNBLFlechaA']),
                floatval($row['DiametroSEWEmpalmeHBUFlechaA']),
                $row['Flecha'], floatval($row['DiametroRolloMetros']),
                floatval($row['DiametroSEWEmpalmeHMUFlechaB']),
                floatval($row['DiametroSEWEmpalmeNBLFlechaB']),
                floatval($row['DiametroSEWEmpalmeHBUFlechaB']),
                $row['FechaCaptura'], $accML, $accMC
            ));

            if ($resultInsert === false) {
                error_log("Error insertando etiqueta: " . print_r(sqlsrv_errors(), true));
                continue;
            }

            $cantidadGuardadas++;
        }

        http_response_code(200);
        echo json_encode([
            'folio'      => $folio,
            'clave'      => $clave,
            'guardadas'  => $cantidadGuardadas,
            'duplicadas' => $cantidadDuplicadas,
        ]);
    }

    /**
     * Crea automáticamente Hook_Enc para cada clave distinta en etiquetas del folio.
     * POST: folio
     */
    function cargarPresentacionesAutomatico()
    {
        $folio = $_POST['folio'] ?? null;

        if (!$folio) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetro folio es requerido']);
            return;
        }

        $Conecta    = new ClassConexion();
        $connTLX004 = $Conecta->conexion("TLX004MXDB");

        // Turno del folio
        $queryTurno  = "SELECT Turno FROM tblEncabezadoBitacora WHERE IdEncabezadoBItacora = ?";
        $resultTurno = sqlsrv_query($connTLX004, $queryTurno, array($folio));

        if ($resultTurno === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener turno']);
            return;
        }
        $rowTurno = sqlsrv_fetch_array($resultTurno, SQLSRV_FETCH_ASSOC);
        if (!$rowTurno) {
            http_response_code(404);
            echo json_encode(['error' => 'Folio no encontrado']);
            return;
        }
        $turno = $rowTurno['Turno'];

        // Claves distintas en etiquetas
        $queryEtiquetas  = "SELECT DISTINCT Clave
                            FROM tblMXPRBitacoraEtiquetasImpresion
                            WHERE IdEncabezadoBitacora = ? AND Turno = ?
                            ORDER BY Clave ASC";
        $resultEtiquetas = sqlsrv_query($connTLX004, $queryEtiquetas, array($folio, $turno));

        if ($resultEtiquetas === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener etiquetas']);
            return;
        }

        $clavesEnEtiquetas = array();
        while ($row = sqlsrv_fetch_array($resultEtiquetas, SQLSRV_FETCH_ASSOC)) {
            $clavesEnEtiquetas[] = $row['Clave'];
        }

        if (empty($clavesEnEtiquetas)) {
            http_response_code(200);
            echo json_encode(['mensaje' => 'No hay etiquetas para este folio']);
            return;
        }

        $connTLX008 = $Conecta->conexion("TLX008MXDB");

        // Claves ya existentes en Hook_Enc
        $queryExistentes  = "SELECT DISTINCT clave, NoTabla
                             FROM tblMXPR_Produccion_Hook_Enc
                             WHERE folio = ?";
        $resultExistentes = sqlsrv_query($connTLX008, $queryExistentes, array($folio));

        if ($resultExistentes === false) {
            error_log("Error cargarPresentacionesAutomatico (existentes): "
                      . print_r(sqlsrv_errors(), true));
        }

        $clavesExistentes = array();
        $noTablaUsados    = array();
        while ($row = sqlsrv_fetch_array($resultExistentes, SQLSRV_FETCH_ASSOC)) {
            $clavesExistentes[$row['clave']] = $row['NoTabla'];
            $noTablaUsados[] = $row['NoTabla'];
        }

        $resultado   = array();
        $proxNoTabla = 1;

        foreach ($clavesEnEtiquetas as $clave) {
            if (isset($clavesExistentes[$clave])) {
                $queryIdHE  = "SELECT idHE FROM tblMXPR_Produccion_Hook_Enc
                               WHERE folio = ? AND clave = ?";
                $resultIdHE = sqlsrv_query($connTLX008, $queryIdHE, array($folio, $clave));
                $rowIdHE    = sqlsrv_fetch_array($resultIdHE, SQLSRV_FETCH_ASSOC);

                $resultado[$clave] = [
                    'NoTabla' => $clavesExistentes[$clave],
                    'idHE'    => $rowIdHE['idHE'],
                    'accion'  => 'ya_existe',
                ];
                continue;
            }

            // Buscar el próximo NoTabla libre (máximo 3 tablas)
            while (in_array($proxNoTabla, $noTablaUsados) && $proxNoTabla <= 3) {
                $proxNoTabla++;
            }

            if ($proxNoTabla > 3) {
                $resultado[$clave] = [
                    'idHE'    => null,
                    'NoTabla' => null,
                    'accion'  => 'no_hay_espacio',
                ];
                continue;
            }

            $queryInsert  = "INSERT INTO tblMXPR_Produccion_Hook_Enc (folio, clave, NoTabla)
                             OUTPUT INSERTED.idHE
                             VALUES (?, ?, ?)";
            $resultInsert = sqlsrv_query($connTLX008, $queryInsert,
                                         array($folio, $clave, $proxNoTabla));

            if ($resultInsert === false) {
                error_log("Error insertando Hook_Enc: " . print_r(sqlsrv_errors(), true));
                $resultado[$clave] = [
                    'NoTabla' => $proxNoTabla,
                    'accion'  => 'error_al_crear',
                ];
                continue;
            }

            $rowInsert = sqlsrv_fetch_array($resultInsert, SQLSRV_FETCH_ASSOC);
            $idHE      = $rowInsert['idHE'];

            $arrayHoras = $this->consultaHorasxturno($turno);
            foreach ($arrayHoras as $hora) {
                $this->insertarSubHook($idHE, $hora['id'], $folio);
            }

            $noTablaUsados[]   = $proxNoTabla;
            $resultado[$clave] = [
                'NoTabla' => $proxNoTabla,
                'idHE'    => $idHE,
                'accion'  => 'creado',
            ];
            $proxNoTabla++;
        }

        http_response_code(200);
        echo json_encode([
            'folio'          => $folio,
            'turno'          => $turno,
            'presentaciones' => $resultado,
        ]);
    }

    /**
     * Devuelve rollos con ML < 1900 de las claves activas del folio,
     * indicando cuáles ya están marcados como merma.
     * POST: folio
     */
    function obtenerRollosMermaHook()
    {
        $folio = $_POST['folio'] ?? null;

        if (!$folio) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetro folio requerido']);
            return;
        }

        $Conecta = new ClassConexion();
        $conn    = $Conecta->conexion("TLX004MXDB");

        $queryTurno = "SELECT Turno FROM tblEncabezadoBitacora WHERE IdEncabezadoBItacora = ?";
        $resTurno   = sqlsrv_query($conn, $queryTurno, array($folio));

        if ($resTurno === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener turno']);
            return;
        }
        $rowTurno = sqlsrv_fetch_array($resTurno, SQLSRV_FETCH_ASSOC);
        if (!$rowTurno) {
            http_response_code(404);
            echo json_encode(['error' => 'Folio no encontrado']);
            return;
        }
        $turno = $rowTurno['Turno'];

        $queryClaves = "SELECT clave FROM tblMXPR_Produccion_Hook_Enc WHERE folio = ?";
        $resClaves   = sqlsrv_query($conn, $queryClaves, array($folio));

        if ($resClaves === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener claves Hook']);
            return;
        }

        $claves = [];
        while ($r = sqlsrv_fetch_array($resClaves, SQLSRV_FETCH_ASSOC)) {
            $claves[] = $r['clave'];
        }

        if (empty($claves)) {
            echo json_encode([]);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($claves), '?'));

        $query = "SELECT e.NumeroRollo, e.MetrosLineales, e.Clave, e.Turno,
                         e.IdEncabezadoBitacora, tblVEC.factor,
                         ISNULL(m.esMerma, 0) AS esMerma
                  FROM tblMXPRBitacoraEtiquetasImpresion e
                  INNER JOIN TLX002MXDB.dbo.tblValeEClaves tblVEC
                      ON tblVEC.NoClave = CAST(e.Clave AS VARCHAR(10))
                  LEFT JOIN tblMXPR_Hook_Merma m
                      ON m.folio = e.IdEncabezadoBitacora
                     AND m.turno = e.Turno
                     AND m.NumeroRollo = e.NumeroRollo
                  WHERE e.IdEncabezadoBitacora = ?
                    AND e.Turno = ?
                    AND e.Clave IN ($placeholders)
                    AND e.MetrosLineales < 1900
                  ORDER BY e.Clave ASC, e.FechaCaptura ASC";

        $params = array_merge([$folio, $turno], $claves);
        $result = sqlsrv_query($conn, $query, $params);

        if ($result === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al consultar rollos', 'details' => sqlsrv_errors()]);
            return;
        }

        $array = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $mmc     = (floatval($row['MetrosLineales']) * floatval($row['factor'])) / 1000;
            $array[] = [
                'NumeroRollo'    => intval($row['NumeroRollo']),
                'MetrosLineales' => floatval($row['MetrosLineales']),
                'Clave'          => $row['Clave'],
                'mmc'            => round($mmc, 3),
                'esMerma'        => intval($row['esMerma']),
            ];
        }

        http_response_code(200);
        echo json_encode($array);
    }

    /**
     * Guarda/revierte la selección de merma para un conjunto de rollos.
     * Body JSON: { folio, rollos: [{NumeroRollo, Clave, MetrosLineales, esMerma}] }
     */
    function guardarMermaHook()
    {
        $input  = json_decode(file_get_contents('php://input'), true);
        $folio  = $input['folio']  ?? null;
        $rollos = $input['rollos'] ?? [];

        if (!$folio || empty($rollos)) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros folio y rollos requeridos']);
            return;
        }

        $Conecta = new ClassConexion();
        $conn    = $Conecta->conexion("TLX004MXDB");

        $queryTurno = "SELECT Turno FROM tblEncabezadoBitacora WHERE IdEncabezadoBItacora = ?";
        $resTurno   = sqlsrv_query($conn, $queryTurno, array($folio));

        if ($resTurno === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener turno']);
            return;
        }
        $rowTurno = sqlsrv_fetch_array($resTurno, SQLSRV_FETCH_ASSOC);
        if (!$rowTurno) {
            http_response_code(404);
            echo json_encode(['error' => 'Folio no encontrado']);
            return;
        }
        $turno = $rowTurno['Turno'];

        $guardados  = 0;
        $regresados = 0;
        $errores    = [];

        foreach ($rollos as $r) {
            $noRollo = intval($r['NumeroRollo']);
            $clave   = $r['Clave'];
            $ml      = floatval($r['MetrosLineales']);
            $esMerma = intval($r['esMerma']);

            if ($esMerma === 1) {
                $qMerge = "IF EXISTS (
                               SELECT 1 FROM tblMXPR_Hook_Merma
                               WHERE folio = ? AND turno = ? AND NumeroRollo = ?
                           )
                           UPDATE tblMXPR_Hook_Merma
                              SET esMerma = 1, fechaGuardado = GETDATE()
                            WHERE folio = ? AND turno = ? AND NumeroRollo = ?
                           ELSE
                           INSERT INTO tblMXPR_Hook_Merma
                               (folio, turno, NumeroRollo, MetrosLineales, Clave, esMerma, fechaGuardado)
                           VALUES (?, ?, ?, ?, ?, 1, GETDATE())";

                $params = [$folio, $turno, $noRollo,
                           $folio, $turno, $noRollo,
                           $folio, $turno, $noRollo, $ml, $clave];

                $stmt = sqlsrv_query($conn, $qMerge, $params);
                if ($stmt === false) {
                    $errores[] = "Error rollo $noRollo: " . print_r(sqlsrv_errors(), true);
                } else {
                    $guardados++;
                }
            } else {
                $qDel = "DELETE FROM tblMXPR_Hook_Merma
                         WHERE folio = ? AND turno = ? AND NumeroRollo = ?";
                $stmt = sqlsrv_query($conn, $qDel, [$folio, $turno, $noRollo]);
                if ($stmt === false) {
                    $errores[] = "Error al regresar rollo $noRollo: " . print_r(sqlsrv_errors(), true);
                } else {
                    $regresados++;
                }
            }
        }

        if (!empty($errores)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'errores' => $errores]);
        } else {
            http_response_code(200);
            echo json_encode([
                'status'     => 'ok',
                'guardados'  => $guardados,
                'regresados' => $regresados,
            ]);
        }
    }
}

// ─────────────────────────────────────────────
// DISPATCHER
// ─────────────────────────────────────────────

$Hook  = new HookMesh();
$input = json_decode(file_get_contents('php://input'), true);

if (isset($_GET["DeletePresentacionHook"])) {
    $Hook->deletePresentacionHook();
} elseif (isset($_GET["obtenerEtiquetasHook"])) {
    $Hook->obtenerEtiquetasHook();
} elseif (isset($_GET["guardarEtiquetasHook"])) {
    $Hook->guardarEtiquetasHook();
} elseif (isset($_GET["cargarPresentacionesAutomatico"])) {
    $Hook->cargarPresentacionesAutomatico();
} elseif (isset($_GET["obtenerRollosMermaHook"])) {
    $Hook->obtenerRollosMermaHook();
} elseif (isset($_GET["guardarMermaHook"])) {
    $Hook->guardarMermaHook();
}