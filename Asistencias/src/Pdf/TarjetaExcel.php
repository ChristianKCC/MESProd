<?php
namespace src\Pdf;

class TarjetaExcel {
    private $data = [];
    private $cont = 0;

    public function renderizarEmpleado(array $emp, array $txs, array $descs, string $fechai, string $fechaf, string $nominaActual = '')
    {
        $noemp  = $emp['NoEmp'];
        $nombre = $emp['Nombre'];
        $puesto = $emp['Puesto'];
        $depto  = $emp['DepartamentoClave'];

        // Cabecera principal de datos
        $this->data[] = ['DEPTO: ', $depto, '', '', 'NUM: ', $noemp , '', 'Nomina No. ', $nominaActual];
        $this->data[] = ['NOMBRE: ', $nombre, '', '', '', '',''];
        $this->data[] = ['PUESTO: ', $puesto, '', '', 'DEL: ', $fechai, 'AL: ', $fechaf];
        $this->data[] = ['','','','','','',''];

        // Encabezados de la tabla
        $this->data[] = [
            'DIA',
            'T1 ENTRADA', 'T1 SALIDA',
            'T2 ENTRADA', 'T2 SALIDA',
            'T3 ENTRADA', 'T3 SALIDA',
        ];

        // Array de dias para facil acceso
        $diasSemana = [
            2 => 'Lunes',
            3 => 'Martes',
            4 => 'Miércoles',
            5 => 'Jueves',
            6 => 'Viernes',
            7 => 'Sábado',
            1 => 'Domingo'
        ];

        // Agrupación de transacciones(horas) por día
        $horariosPorDia = [];
        foreach ($txs as $t) {
            $eventTime = $t['event_time'];
            $hora = ($eventTime instanceof \DateTime) ? $eventTime->format('H:i:s') : date('H:i:s', strtotime($eventTime));
            $dia = (int)$t['dia_semana'];

            if (!isset($horariosPorDia[$dia])) {
                $horariosPorDia[$dia] = [];
            }
            $horariosPorDia[$dia][] = $hora;
        }

        // Iteración de días y escritura de las filas por cada uno
        foreach ($diasSemana as $numDia => $nombreDia) {
            $fila = [$nombreDia, '', '', '', '', '', '', ''];
            // Gestion de dias por fila 1&2 (Turno 1 - Entrada y Salida)
            // Gestion de dias por fila 3&4 (Turno 2 - Entrada y Salida)
            // Gestion de dias por fila 5&6 (Turno 3 - Entrada y Salida)
            if (isset($horariosPorDia[$numDia])) {
                $horas = $horariosPorDia[$numDia];
                if (isset($horas[0])) $fila[1] = $horas[0];
                if (isset($horas[1])) $fila[2] = $horas[1];
                if (isset($horas[2])) $fila[3] = $horas[2];
                if (isset($horas[3])) $fila[4] = $horas[3];
                if (isset($horas[4])) $fila[5] = $horas[4];
                if (isset($horas[5])) $fila[6] = $horas[5];
                if (isset($horas[6])) $fila[7] = $horas[6];
            }
            // Agregacion de datos a data[] para csv
            $this->data[] = $fila;
        }
        
        // Agregacion de descansos en data
        $this->data[] = ['','','','','','',''];
        $this->data[] = ['=================================','', '' ,'Tiempos de: ', $nombre,'','================================='];
        $this->data[] = ['','','','','','',''];
        // Validaciones para campos vacios antes de meter descansos y tiempos

        $d = $descs[0] ?? [];
            if (!empty($d['lunes']))     $this->data[] = ['Lunes', $d['lunes']];
            if (!empty($d['martes']))    $this->data[] = ['Martes', $d['martes']];
            if (!empty($d['miercoles']))    $this->data[] = ['Miercoles', $d['miercoles']];
            if (!empty($d['jueves']))    $this->data[] = ['Jueves', $d['jueves']];
            if (!empty($d['viernes']))    $this->data[] = ['Viernes', $d['viernes']];
            if (!empty($d['sabado']))    $this->data[] = ['Sabado', $d['sabado']];
            if (!empty($d['domingo']))    $this->data[] = ['Domingo', $d['domingo']];

        // Separador entre consultas x empleado
        $this->data[] = ['','','','','','',''];
        $this->data[] = ['','','','','','',''];
        $this->cont++;
    }

    // Creacion y salida de archivo csv
    public function output(string $nombre_archivo = null) {
        if ($nombre_archivo === null) {
            $nombre_archivo = 'control_asistencia_' . date('Ymd_His') . '.csv';
        }

        // Headers para lanzar el archivo
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
        header('Cache-Control: max-age=0');

        // Escritura del arcvivo
        $output = fopen('php://output', 'w');
        // BOM para UTF-8(compatibilidad)
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        foreach($this->data as $row){
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}