<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . "/../config.php");

// Obtener IBM de la sesión
$ibmSession = $_SESSION["ibm"];

// function obtenerSupervisoresIBM(): array {
//     if (!file_exists(CSV_FILE_SIND)) {
//         error_log("CSV de supervisores no encontrado en: " . CSV_FILE_SIND);
//         return [];
//     }

//     $handle = fopen(CSV_FILE_SIND, "r");
//     if (!$handle) {
//         error_log("No se pudo abrir el CSV de supervisores");
//         return [];
//     }

//     // Quitar BOM si existe
//     $bom = fread($handle, 3);
//     if ($bom !== "\xEF\xBB\xBF") rewind($handle);

//     $headers = fgetcsv($handle, 0, CSV_SEPARATOR);
//     if (!$headers) { fclose($handle); return []; }

//     // Lista de columnas de supervisores (ya definidas en guard.php)
//     $supervisorCols = [
//         COL_IBM_DEL_SUPERVISOR1_SIND,
//         COL_IBM_DEL_SUPERVISOR2_SIND,
//         COL_IBM_DEL_SUPERVISOR3_SIND,
//         COL_IBM_DEL_SUPERVISOR4_SIND,
//         COL_IBM_DEL_SUPERVISOR5_SIND,
//         COL_IBM_DEL_SUPERVISOR6_SIND,
//     ];

//     $supervisores = [];

//     while (($line = fgetcsv($handle, 0, CSV_SEPARATOR)) !== false) {
//         if (array_filter($line) === []) continue;

//         $row = array_combine($headers, array_pad($line, count($headers), ''));

//         foreach ($supervisorCols as $col) {
//             $valor = trim($row[$col] ?? '');
//             if ($valor !== '') {
//                 $supervisores[] = $valor;
//             }
//         }
//     }
//     fclose($handle);

//     // Devolver solo valores únicos
//     return array_values(array_unique($supervisores));
// }

function obtenerSupervisoresIBM(): array {
    if (!file_exists(CSV_FILE_SIND)) {
        error_log("CSV de supervisores no encontrado en: " . CSV_FILE_SIND);
        return [];
    }

    $handle = fopen(CSV_FILE_SIND, "r");
    if (!$handle) {
        error_log("No se pudo abrir el CSV de supervisores");
        return [];
    }

    // Quitar BOM si existe
    $bom = fread($handle, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($handle);

    $headers = fgetcsv($handle, 0, CSV_SEPARATOR);
    if (!$headers) { fclose($handle); return []; }

    // Columnas de supervisores
    $supervisorCols = [
        COL_IBM_DEL_SUPERVISOR1_SIND,
        COL_IBM_DEL_SUPERVISOR2_SIND,
        COL_IBM_DEL_SUPERVISOR3_SIND,
        COL_IBM_DEL_SUPERVISOR4_SIND,
        COL_IBM_DEL_SUPERVISOR5_SIND,
        COL_IBM_DEL_SUPERVISOR6_SIND,
    ];

    $supervisores = [];

    while (($line = fgetcsv($handle, 0, CSV_SEPARATOR)) !== false) {
        if (array_filter($line) === []) continue;

        $row = array_combine($headers, array_pad($line, count($headers), ''));

        foreach ($supervisorCols as $col) {
            $valor = trim($row[$col] ?? '');
            if ($valor !== '') {
                $supervisores[] = $valor;
            }
        }
    }
    fclose($handle);

    // Usuarios fijos
    $superusuarios = [60040, 51947, 55268, 53224, 58998];

    // Unir supervisores + usuarios
    $todos = array_merge($supervisores, $superusuarios);

    // Devolver únicos
    return array_values(array_unique($todos));
}

// Función para calcular antigüedad
// function calcularAntiguedad(string $fechaStr): string {
//     $fechaStr = trim($fechaStr);
//     if (!$fechaStr || $fechaStr === '-') return '-';

//     // Normalizar separadores
//     $fechaStr = str_replace('-', '/', $fechaStr);
//     $partes = explode('/', $fechaStr);
//     if (count($partes) !== 3) return $fechaStr;

//     // Detectar formato: si primer segmento tiene 4 dígitos es YYYY/MM/DD, si no es MM/DD/YYYY
//     if (strlen($partes[0]) === 4) {
//         [$anio, $mes, $dia] = $partes;
//     } else {
//         [$mes, $dia, $anio] = $partes;
//     }

//     // Usar DateTime::diff para cálculo exacto (respeta bisiestos y meses variables)
//     $ingreso = DateTime::createFromFormat('Y-m-d', "$anio-$mes-$dia");
//     if (!$ingreso) return $fechaStr;

//     $diff = $ingreso->diff(new DateTime());
//     $anios = $diff->y;
//     $meses = $diff->m;

//     if ($anios === 0) return "$meses mes(es)";
//     if ($meses === 0) return "$anios año(s)";
//     return "$anios año(s) y $meses mes(es)";
// }

// // Agrega esta función en vacacionesLogistica.php
// function normalizarFechaISO(string $fechaStr): string {
//     $fechaStr = trim($fechaStr);
//     if (!$fechaStr || $fechaStr === '-') return '';

//     $fechaStr = str_replace('-', '/', $fechaStr);
//     $partes = explode('/', $fechaStr);
//     if (count($partes) !== 3) return $fechaStr;

//     // Forzar siempre formato US: MM/DD/YYYY
//     [$mes, $dia, $anio] = $partes;

//     return sprintf('%04d-%02d-%02d', (int)$anio, (int)$mes, (int)$dia);
// }

// Normaliza cualquier fecha a ISO (YYYY-MM-DD)
// function normalizarFechaISO(string $fechaStr): string {
//     $fechaStr = trim($fechaStr);
//     if (!$fechaStr || $fechaStr === '-') return '';

//     error_log(" normalizarFechaISO: entrada = [$fechaStr]");

//     $formatos = ['m/d/Y', 'd/m/Y', 'Y-m-d'];
//     foreach ($formatos as $fmt) {
//         $fecha = DateTime::createFromFormat($fmt, $fechaStr);
//         if ($fecha && $fecha->format($fmt) === $fechaStr) {
//             $iso = $fecha->format('Y-m-d');
//             error_log("interpretado como $fmt = $iso");
//             return $iso;
//         }
//     }

//     error_log("normalizarFechaISO: no se pudo interpretar [$fechaStr]");
//     return '';
// }
function normalizarFechaISO(string $fechaStr): string {
    $fechaStr = trim($fechaStr);
    if (!$fechaStr || $fechaStr === '-') return '';

    error_log("normalizarFechaISO: entrada = [$fechaStr]");

    // Forzar siempre LATAM d/m/Y
    $fecha = DateTime::createFromFormat('d/m/Y', $fechaStr);
    if ($fecha) {
        $iso = $fecha->format('Y-m-d');
        error_log("forzado LATAM d/m/Y = $iso");
        return $iso;
    }

    // fallback ISO
    $fecha = DateTime::createFromFormat('Y-m-d', $fechaStr);
    if ($fecha) {
        $iso = $fecha->format('Y-m-d');
        error_log("interpretado como ISO Y-m-d = $iso");
        return $iso;
    }

    error_log("normalizarFechaISO: no se pudo interpretar [$fechaStr]");
    return '';
}

function calcularAntiguedad(string $fechaISO): string {
    if (!$fechaISO) {
        error_log("calcularAntiguedad: fecha vacía");
        return '-';
    }

    $ingreso = DateTime::createFromFormat('Y-m-d', $fechaISO);
    if (!$ingreso) {
        error_log("calcularAntiguedad: createFromFormat falló con [$fechaISO]");
        return '-';
    }

    $hoy = new DateTime();
    $diff = $ingreso->diff($hoy);

    $anios = $diff->y;
    $meses = $diff->m;
    $dias  = $diff->d;

    // Construir resultado exacto
    $resultado = [];
    if ($anios > 0) $resultado[] = "$anios año(s)";
    if ($meses > 0) $resultado[] = "$meses mes(es)";
    if ($dias > 0) $resultado[] = "$dias día(s)";

    $texto = implode(" ", $resultado);
    if ($texto === "") $texto = "0 días";

    error_log("calcularAntiguedad: fechaISO = [$fechaISO], resultado = [$texto]");

    return $texto;
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