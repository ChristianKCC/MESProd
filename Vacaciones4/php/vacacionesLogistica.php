<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . "/../config.php");

// Obtener IBM de la sesión
$ibmSession = $_SESSION["ibm"];

// Función para calcular antigüedad
function calcularAntiguedad(string $fechaStr): string {
    $fechaStr = trim($fechaStr);
    if (!$fechaStr || $fechaStr === '-') return '-';
    $fechaStr = str_replace('-', '/', $fechaStr);
    $partes = explode('/', $fechaStr);
    if (count($partes) !== 3) return $fechaStr;
    
    [$mes, $dia, $anio] = $partes;
    $ingreso = mktime(0, 0, 0, (int)$mes, (int)$dia, (int)$anio);
    if (!$ingreso) return $fechaStr;

    $hoy = time();
    $anios = (int) floor(($hoy - $ingreso) / (365.25 * 24 *3600));
    $meses = (int) floor((($hoy - $ingreso) / (30.44 * 24 *3600)) % 12);

    if ($anios === 0) return "$meses mes(es)";
    if ($meses === 0) return "$anios año(s)";
    return "$anios año(s) y $meses mes(es)";
}

// Función para buscar empleado en CSV
function buscarEmpleado(string $ibm): ?array {
    if (!file_exists(CSV_FILE)) return null;
    $handle = fopen(CSV_FILE, "r");
    if (!$handle) return null;

    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);

    $headers = fgetcsv($handle, 0, CSV_SEPARATOR);
    if (!$headers) { fclose($handle); return null; }

    while (($line = fgetcsv($handle, 0, CSV_SEPARATOR)) !== false) {
        if (array_filter($line) === []) continue;
        $row = array_combine($headers, array_pad($line, count($headers), ''));
        if (strtolower(trim($row[COL_IBM] ?? '')) === strtolower(trim($ibm))) {
            fclose($handle);
            return $row;
        }
    }
    fclose($handle);
    return null;
}

// Funcion de calculo de siguiente aniversario
function calcularAniversario(string $fechaIngreso): string {
    try {
        $fecha = new DateTime($fechaIngreso);
        $anioActual = (int)date("Y");
        $proximo = DateTime::createFromFormat("Y-m-d", $anioActual . "-" . $fecha->format("m") . "-" . $fecha->format("d"));
        return $proximo->format("d/m/Y");
    } catch (Exception $e) {
        return "";
    }
}

// Funcion de busqueda de supervisor en csv
function buscarSupervisor(string $ibm): ?array {
    if (!file_exists(CSV_FILE_SIND)) return null;
    $handle = fopen(CSV_FILE_SIND, "r");
    if (!$handle) return null;

    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);

    $headers = fgetcsv($handle, 0, CSV_SEPARATOR);
    if (!$headers) { fclose($handle); return null; }

    // Lista de columnas de supervisores
    $supervisorCols = [
        COL_IBM_DEL_SUPERVISOR1_SIND,
        COL_IBM_DEL_SUPERVISOR2_SIND,
        COL_IBM_DEL_SUPERVISOR3_SIND,
        COL_IBM_DEL_SUPERVISOR4_SIND,
        COL_IBM_DEL_SUPERVISOR5_SIND,
        COL_IBM_DEL_SUPERVISOR6_SIND,
    ];

    while (($line = fgetcsv($handle, 0, CSV_SEPARATOR)) !== false) {
        if (array_filter($line) === []) continue;
        $row = array_combine($headers, array_pad($line, count($headers), ''));

        foreach ($supervisorCols as $col) {
            if (strtolower(trim($row[$col] ?? '')) === strtolower(trim($ibm))) {
                fclose($handle);
                return $row;
            }
        }
    }
    fclose($handle);
    return null;
}

// Normalizacion de datos para el nombre
function normalizarNombre(string $nombre): array {
    $nombre = strtoupper(str_replace(',', ' ', trim($nombre)));
    $nombre = preg_replace('/\s+/', ' ', $nombre);
    return explode(' ', $nombre);
}

// Funcion de busqueda entre nombres
function nombresCoinciden(string $buscado, string $csv): bool {
    $palabrasBuscadas = normalizarNombre($buscado);
    $palabrasCsv = normalizarNombre($csv);

    return empty(array_diff($palabrasBuscadas, $palabrasCsv));
}

// Validacion de supervisor
function validarSupervisor(string $ibmEmpleado = '', string $nombreEmpleado = '', string $ibmSupervisorSesion): bool {
    if (!file_exists(CSV_FILE_SIND)) return false;
    $handle = fopen(CSV_FILE_SIND, "r");
    if (!$handle) return false;

    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);

    $headers = fgetcsv($handle, 0, CSV_SEPARATOR);
    if (!$headers) { fclose($handle); return false; }

    // Lista de columnas de supervisores
    $supervisorCols = [
        COL_IBM_DEL_SUPERVISOR1_SIND,
        COL_IBM_DEL_SUPERVISOR2_SIND,
        COL_IBM_DEL_SUPERVISOR3_SIND,
        COL_IBM_DEL_SUPERVISOR4_SIND,
        COL_IBM_DEL_SUPERVISOR5_SIND,
        COL_IBM_DEL_SUPERVISOR6_SIND,
    ];

    while (($line = fgetcsv($handle, 0, CSV_SEPARATOR)) !== false) {
        if (array_filter($line) === []) continue;
        $row = array_combine($headers, array_pad($line, count($headers), ''));

        $coincide = false;

        // Comparar por IBM
        if ($ibmEmpleado !== '' && trim($row[COL_IBM_SIND] ?? '') === trim($ibmEmpleado)) {
            $coincide = true;
        }

        // Comparar por Nombre
        if (!$coincide && $nombreEmpleado !== '') {
            $palabrasBuscadas = normalizarNombre($nombreEmpleado);
            $palabrasCsv = normalizarNombre($row[COL_NOMBRE_SIND] ?? '');
            $coincide = !array_diff($palabrasBuscadas, $palabrasCsv);
        }

        if ($coincide) {
            // Validar contra todas las columnas de supervisores
            foreach ($supervisorCols as $col) {
                $supervisorAsignado = trim($row[$col] ?? '');
                if ($supervisorAsignado !== '' && $supervisorAsignado === trim($ibmSupervisorSesion)) {
                    fclose($handle);
                    return true;
                }
            }
            fclose($handle);
            return false;
        }
    }
    fclose($handle);
    return false;
}

// Busqueda de empleados por medio del supervisor
function busquedaEmpledoxSupervisor(string $ibm = '', string $nombre = ''): ?array {
    if (!file_exists(CSV_FILE)) return null;
    $handle = fopen(CSV_FILE, "r");
    if (!$handle) return null;

    // Quitar BOM de encabezados
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);

    $headers = fgetcsv($handle, 0, CSV_SEPARATOR);
    if (!$headers) { fclose($handle); return null; }
    $headers = array_map(function($h) {
        return preg_replace('/^\xEF\xBB\xBF/', '', trim($h));
    }, $headers);

    while (($line = fgetcsv($handle, 0, CSV_SEPARATOR)) !== false) {
        if (array_filter($line) === []) continue;
        $row = array_combine($headers, array_pad($line, count($headers), ''));

        // Comparar por IBM
        if ($ibm !== '' && trim($row[COL_IBM] ?? '') === trim($ibm)) {
            fclose($handle);
            return $row;
        }

        // Comparar por Nombre
        if ($nombre !== '') {
            if (nombresCoinciden($nombre, $row[COL_NOMBRE] ?? '')) {
                fclose($handle);
                return $row;
            }
        }
        
    }
    fclose($handle);
    return null;
}

// Ejecutar búsqueda
$csvExiste = file_exists(CSV_FILE);
$empleado = $csvExiste ? buscarEmpleado($ibmSession) : null;

$csvSupervisorExiste = file_exists(CSV_FILE_SIND);
$supervisor = $csvSupervisorExiste ? buscarSupervisor($ibmSession) : null;

// Helper para mostrar datos
function col(?array $row, string $key): string {
    if (!$row) return 'Empleado no encontrado';
    $valor = trim($row[$key] ?? '');
    if ($key === COL_NOMBRE) $valor = str_replace(',', ' ', $valor);
    return htmlspecialchars($valor);
}


// Ruta del historial
define('HISTORIAL_FILE', UPLOAD_DIR . "Historial_Solicitudes_Vacaciones.csv");

/*
// Calcular días disponibles considerando historial
$diasDisponibles = (int)($empleado[COL_VAC] ?? 0);

$historial = [];
if (file_exists(HISTORIAL_FILE)) {
    $handle = fopen(HISTORIAL_FILE, "r");
    $headers = fgetcsv($handle);
    while (($line = fgetcsv($handle)) !== false) {
        if (trim($line[0]) === (string)$ibmSession) {
            $historial[] = $line;
            $estatus = trim($line[7]);
            if (!in_array($estatus, ["Rechazado", "Pendiente"])) {
                $diasDisponibles -= (int)$line[6];
            }
        }
    }
    fclose($handle);
}

-------------------------------------------------------------------
*/

// Recuperar IBM actual: si viene de POST úsalo, si no usa la sesión
$ibmActual = $_POST["ibm"] ?? $ibmSession;

// Buscar empleado con ese IBM
$empleado = $csvExiste ? buscarEmpleado($ibmActual) : null;

// Calcular días disponibles con ese IBM
$diasDisponibles = (int)($empleado[COL_VAC] ?? 0);

$historial = [];
if (file_exists(HISTORIAL_FILE)) {
    $handle = fopen(HISTORIAL_FILE, "r");
    $headers = fgetcsv($handle);
    while (($line = fgetcsv($handle)) !== false) {
        if (trim($line[0]) === (string)$ibmActual) {
            $historial[] = $line;
            $estatus = trim($line[7]);
            if (!in_array($estatus, ["Rechazado", "Pendiente"])) {
                $diasDisponibles -= (int)$line[6];
            }
        }
    }
    fclose($handle);
}


/*
-------------------------------------------------------------------
*/

// Filtrar dias que ya han sido solicitados
$eventosBloqueados = [];
foreach ($historial as $solicitud) {
    $estatus = trim($solicitud[7]);

    if ($estatus === "Pendiente") {
        $eventosBloqueados[] = [
            "title" => "SOLICITUD PENDIENTE",
            "start" => date("Y-m-d", strtotime($solicitud[4])),
            "end"   => date("Y-m-d", strtotime($solicitud[5] . ' +1 day')),
            "display" => "background",
            "color" => "#cdd9ff"
        ];
    } elseif ($estatus === "Aprobado") {
        $eventosBloqueados[] = [
            "title" => "SOLICITUD APROBADA",
            "start" => date("Y-m-d", strtotime($solicitud[4])),
            "end"   => date("Y-m-d", strtotime($solicitud[5] . ' +1 day')),
            "display" => "background",
            "color" => "#d4edda"
        ];
    } elseif ($estatus === "Rechazado") {
        continue;
    }    
}