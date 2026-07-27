<?php
namespace src\Pdf;

require_once('../../fpdf/fpdf.php');
use FPDF;

class TarjetaPdf
{
    private $pdf;
    private $cont = 0;
    // Controlador de posicion en vertical
    private $y = 2;

    /* -----------------------------------------------------------------
       GEOMETRIA DEL BLOQUE DE TIEMPOS (offsets relativos a $x / $y)
       ----------------------------------------------------------------- */

    // Las 3 columnas NO son turnos: son las 3 horas de cambio de turno.
    // Cada columna aloja los dos movimientos que ocurren a esa misma hora.
    //   col 0 -> 7:00    (I ENTRADA  / III SALIDA)
    //   col 1 -> 15:00   (I SALIDA   / II ENTRADA)
    //   col 2 -> 22:30   (III ENTRADA / II SALIDA)
    private const COL_X = [10, 25, 40];   // inicio de cada columna
    private const COL_W = 15;             // ancho de cada columna
    private const FILA_Y = 70;             // primer renglon (lunes)
    private const FILA_H = 10;             // alto de cada renglon

    /* -----------------------------------------------------------------
       GEOMETRIA DEL BLOQUE DE CONCEPTOS
       ----------------------------------------------------------------- */

    // [x, ancho, etiqueta] de cada columna, relativo a $x
    private const CON_COLS = [
        ['x' => 90, 'w' => 20, 'label' => 'CONCEPTO'],
        ['x' => 110, 'w' => 20, 'label' => '# DIAS'],
        ['x' => 130, 'w' => 20, 'label' => '# HRS'],
        ['x' => 150, 'w' => 20, 'label' => 'CAMBIO PROV'],
        ['x' => 170, 'w' => 30, 'label' => 'OBSERVACIONES'],
    ];
    private const CON_FILA_Y = 30;   // primer renglon de datos
    private const CON_FILA_H = 10;
    private const CON_MAX = 11;   // renglones disponibles

    public function __construct()
    {
        $this->pdf = new FPDF('P');
        $this->pdf->SetAutoPageBreak(false);
    }

    public function addPage()
    {
        $this->pdf->AddPage();
    }

    /* =================================================================
       BLOQUE DE TIEMPOS
       ================================================================= */

    /**
     * Decide en que columna cae una checada segun su hora.
     * Las fronteras son los puntos medios entre las horas de cambio de turno:
     * 11:00 (entre 7:00 y 15:00), 19:00 (entre 15:00 y 22:30) y 03:00 (entre 22:30 y 7:00).
     */
    private static function columnaPorHora(int $minutos): int
    {
        if ($minutos >= 180 && $minutos < 660)
            return 0;   // 03:00 - 10:59  -> 7:00
        if ($minutos >= 660 && $minutos < 1140)
            return 1;   // 11:00 - 18:59  -> 15:00
        return 2;                                           // 19:00 - 02:59  -> 22:30
    }

    /**
     * Agrupa las checadas del empleado en la malla [fila][columna] => [horas].
     * La fila sale de dia_semana (1 = lunes ... 7 = domingo) y la columna de la hora.
     * El acomodo depende SOLO de la hora: cada una de las 3 columnas es una hora
     * de cambio de turno, por lo que no hace falta desambiguar el turno.
     */
    private function acomodarTransacciones(array $txs): array
    {
        $celdas = [];

        foreach ($txs as $t) {
            $eventTime = $t['event_time'];
            $dt = ($eventTime instanceof \DateTime) ? $eventTime : new \DateTime($eventTime);

            $fila = (int) $t['dia_semana'] - 1;   // 1 = lunes -> fila 0
            if ($fila < 0 || $fila > 6)
                continue;

            $minutos = ((int) $dt->format('H') * 60) + (int) $dt->format('i');
            $col = self::columnaPorHora($minutos);

            $celdas[$fila][$col][] = $dt->format('H:i:s');
        }

        return $celdas;
    }

    /**
     * Dibuja la malla del bloque de tiempos: columna de dias, banda de turnos
     * de dia, banda de turnos de noche, letras de los dias y separadores.
     */
    private function dibujarEncabezadoTurnos(float $x, float $y): void
    {
        $pdf = $this->pdf;

        // Rellenos solo del area de turnos, sin invadir la columna de dias
        $pdf->SetFillColor(228, 233, 245);
        $pdf->Rect($x + 10, $y + 50, 45, 10, 'F');
        $pdf->SetFillColor(210, 217, 236);
        $pdf->Rect($x + 10, $y + 60, 45, 10, 'F');

        // Marco general del bloque
        $pdf->Rect($x + 5, $y + 50, 80, 90);

        $pdf->SetTextColor(0, 0, 0);

        // "DIA" apilado en vertical, una letra por renglon de 5mm,
        // para que no se desborde ni la cruce el separador de bandas
        $pdf->SetFont('Arial', 'B', 6);
        foreach (['D', 'I', 'A'] as $i => $letra) {
            $pdf->SetXY($x + 5, $y + 50 + ($i * 5));
            $pdf->Cell(5, 5, $letra, 0, 0, 'C');
        }

        $pdf->SetXY($x + 55, $y + 50);
        $pdf->Cell(30, 20, 'TIEMPOS', 0, 0, 'C');

        // [columna, banda, turno, movimiento, hora de referencia]
        // banda 0 = turnos de dia (arriba), banda 1 = turnos de noche (abajo)
        $etiquetas = [
            [0, 0, 'I TURNO', 'ENTRADA', '7:00'],
            [1, 0, 'I TURNO', 'SALIDA', '15:00'],
            [2, 0, 'III TURNO', 'ENTRADA', '22:30'],
            [0, 1, 'III TURNO', 'SALIDA', '7:00'],
            [1, 1, 'II TURNO', 'ENTRADA', '15:00'],
            [2, 1, 'II TURNO', 'SALIDA', '22:30'],
        ];

        foreach ($etiquetas as $e) {
            list($col, $banda, $turno, $mov, $hora) = $e;
            $cx = $x + self::COL_X[$col];
            $cy = $y + 50 + ($banda * 10);

            $pdf->SetFont('Arial', 'B', 6);
            $pdf->SetXY($cx, $cy + 0.3);
            $pdf->Cell(self::COL_W, 3.4, $turno, 0, 0, 'C');

            $pdf->SetXY($cx, $cy + 3.4);
            $pdf->Cell(self::COL_W, 3.4, $mov, 0, 0, 'C');

            // Hora de referencia de cada columna, mas discreta
            $pdf->SetFont('Arial', '', 5);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY($cx, $cy + 6.4);
            $pdf->Cell(self::COL_W, 3.2, $hora, 0, 0, 'C');
        }

        // Separador entre bandas: arranca en x+10 para no cruzar la columna de dias
        $pdf->Line($x + 10, $y + 60, $x + 55, $y + 60);

        // Renglones de los 7 dias
        for ($i = 0; $i <= 7; $i++) {
            $yy = $y + self::FILA_Y + ($i * self::FILA_H);
            $pdf->Line($x + 5, $yy, $x + 85, $yy);
        }

        // Separadores verticales de las columnas
        $pdf->Line($x + 10, $y + 50, $x + 10, $y + 140);
        foreach (self::COL_X as $cx) {
            $pdf->Line($x + $cx + self::COL_W, $y + 50, $x + $cx + self::COL_W, $y + 140);
        }

        // Letra de cada dia, alineada arriba del renglon
        $pdf->SetFont('Arial', 'B', 7);
        $dias = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
        foreach ($dias as $i => $letra) {
            $pdf->SetXY($x + 5, $y + self::FILA_Y + ($i * self::FILA_H));
            $pdf->Cell(5, 5, $letra, 0, 0, 'C');
        }
    }

    /* =================================================================
       BLOQUE DE CONCEPTOS
       ================================================================= */

    /**
     * Dibuja la rejilla del bloque de conceptos (encabezados y lineas).
     *
     * EL LLENADO DE DATOS ESTA OCULTO TEMPORALMENTE (return; abajo).
     * La rejilla se sigue pintando siempre; solo se omite el volcado de
     * tiempo extra / vacaciones / cambio de puesto mientras se analiza el
     * modulo. Toda la tuberia de datos sigue viva y sin usar:
     *   - ConceptosRepositorio->getConceptosAgrupados()  (las 3 consultas)
     *   - resumirConceptos()  en CrearTarjetasClas.php    (arma los renglones)
     *   - el parametro $conceptos que llega hasta aqui
     * Para REACTIVAR: borrar el 'return;' y quitar las lineas de apertura
     * y cierre del comentario que envuelve el foreach de contenido.
     */
    private function dibujarConceptos(float $x, float $y, array $conceptos): void
    {
        $pdf = $this->pdf;

        // Encabezado sombreado
        $pdf->SetFillColor(228, 233, 245);
        $pdf->Rect($x + 90, $y + 20, 110, 10, 'F');

        $pdf->Rect($x + 90, $y + 20, 110, 120);
        $pdf->SetTextColor(0, 0, 0);

        // Titulos alineados a las columnas reales
        $pdf->SetFont('Arial', 'B', 6);
        foreach (self::CON_COLS as $c) {
            $pdf->SetXY($x + $c['x'], $y + 20 + ($c['label'] === 'CAMBIO PROV' ? 1.5 : 3));
            $pdf->Cell($c['w'], 4, $c['label'], 0, 0, 'C');
        }
        // Segunda linea del encabezado de cambio provisional
        $pdf->SetXY($x + 150, $y + 25);
        $pdf->Cell(20, 4, 'AL PUESTO', 0, 0, 'C');

        // Verticales
        foreach ([110, 130, 150, 170] as $vx) {
            $pdf->Line($x + $vx, $y + 20, $x + $vx, $y + 140);
        }
        // Horizontales
        for ($sub = 30; $sub <= 130; $sub += 10) {
            $pdf->Line($x + 90, $y + $sub, $x + 200, $y + $sub);
        }

        // ===== CONCEPTOS: llenado de datos OCULTO temporalmente =====
        // Ver el docblock del metodo para reactivarlo.
        return;
        /*
        // Contenido
        $pdf->SetFont('Arial', '', 6);
        $fila = 0;
        foreach ($conceptos as $c) {
            if ($fila >= self::CON_MAX)
                break;
            $cy = $y + self::CON_FILA_Y + ($fila * self::CON_FILA_H) + 3;

            $valores = [
                $c['concepto'],
                $c['dias'],
                $c['hrs'],
                $c['puesto'],
                $c['obs']
            ];

            foreach (self::CON_COLS as $i => $col) {
                if ($valores[$i] === '')
                    continue;
                $pdf->SetXY($x + $col['x'] + 1, $cy);
                $pdf->Cell($col['w'] - 2, 4, utf8_decode($valores[$i]), 0, 0, 'C');
            }
            $fila++;
        }
        */
    }

    /* =================================================================
       RENDER PRINCIPAL
       ================================================================= */

    /**
     * Renderiza una tarjeta completa de un empleado para una semana.
     *
     * @param array  $conceptos  Renglones ya resumidos (tiempo extra, vacaciones,
     *                           cambio de puesto). ACTUALMENTE NO SE PINTAN: el
     *                           llenado esta oculto en dibujarConceptos(). El
     *                           parametro se conserva para reactivarlo sin tocar
     *                           la firma ni la llamada.
     */
    public function renderizarEmpleado(array $emp, array $txs, array $descs, array $conceptos, string $fechai, string $fechaf, string $nominaActual = '')
    {
        // Nueva hoja cada 2 tarjetas, solo cuando realmente se va a dibujar.
        // Va al INICIO (no al final) para no generar una hoja en blanco al cerrar.
        if ($this->cont % 2 === 0) {
            $this->pdf->AddPage();
            $this->y = 2;
        }

        // Datos del empleado
        $noemp = $emp['NoEmp'];
        $nombre = $emp['Nombre'];
        $puesto = $emp['Puesto'];
        $depto = $emp['DepartamentoClave'];

        $this->pdf->Ln(20);
        $this->pdf->SetFont('Arial', 'B', 10);
        $x = 2;
        $y = $this->y;

        // Dibujo del marco y datos básicos
        $this->pdf->Rect($x, $y, 205, 142);
        $this->pdf->Image('../../img/logo.jpg', $x + 5, $y + 5, 60);
        $this->pdf->SetXY($x + 5, $y + 10);

        $this->pdf->SetTextColor(0, 0, 0);

        $this->pdf->Cell(15, 10, "DEPTO:");
        $this->pdf->Cell(20, 10, $depto);
        $this->pdf->Cell(15, 10, "NUM:");
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(110, 10, "0" . $noemp);
        $this->pdf->Cell(20, 10, "NUM:");
        $this->pdf->Cell(20, 10, $noemp);
        $this->pdf->SetFont('Arial', '', 8);
        $this->pdf->Ln(5);
        $this->pdf->SetX($x + 5);
        $this->pdf->Cell(20, 10, utf8_decode($nombre));
        $this->pdf->Ln(5);
        $this->pdf->SetXY($x + 180, $y + 5);
        $this->pdf->Cell(20, 10, "NOMINA NO." . $nominaActual);
        $this->pdf->Ln(15);
        $this->pdf->SetX($x);
        $this->pdf->Cell(20, 10, "DEL   $fechai   AL   $fechaf");
        $this->pdf->Ln(5);
        $this->pdf->SetX($x + 5);
        $this->pdf->Cell(20, 10, "14");
        $this->pdf->SetX($x + 30);
        $this->pdf->Cell(20, 10, "020006");
        $this->pdf->SetX($x + 60);
        $this->pdf->Cell(20, 10, "87");
        $this->pdf->Ln(5);
        $this->pdf->SetX($x + 5);
        $this->pdf->Cell(20, 10, utf8_decode($puesto));

        /* ----- Bloque de tiempos ----- */
        $this->dibujarEncabezadoTurnos($x, $y);

        /* ----- Bloque de conceptos (solo rejilla; datos ocultos) ----- */
        $this->dibujarConceptos($x, $y, $conceptos);

        /* ----- Checadas acomodadas por hora ----- */
        $celdas = $this->acomodarTransacciones($txs);

        foreach ($celdas as $fila => $columnas) {
            foreach ($columnas as $col => $horas) {
                sort($horas);
                $cx = $x + self::COL_X[$col];
                $cy = $y + self::FILA_Y + ($fila * self::FILA_H);

                $this->pdf->SetFont('Arial', '', 7);
                $this->pdf->SetTextColor(0, 0, 0);
                $this->pdf->SetXY($cx, $cy + 0.5);
                $this->pdf->Cell(self::COL_W, 4.5, $horas[0], 0, 0, 'C');

                // Si hubo mas de una checada en la misma ventana se muestra la ultima abajo
                if (count($horas) > 1) {
                    $this->pdf->SetFont('Arial', '', 5.5);
                    $this->pdf->SetTextColor(0, 0, 0);
                    $this->pdf->SetXY($cx, $cy + 5);
                    $this->pdf->Cell(self::COL_W, 4.5, end($horas), 0, 0, 'C');
                }
            }
        }
        $this->pdf->SetTextColor(0, 0, 0);

        /* ----- Descansos ----- */
        $this->pdf->SetFont('Arial', 'B', 10);
        foreach ($descs as $d) {
            if (!empty($d['lunes'])) {
                $this->pdf->SetXY(60, $y + 70);
                $this->pdf->Cell(15, 5, $d['lunes']);
            }
            if (!empty($d['martes'])) {
                $this->pdf->SetXY(60, $y + 80);
                $this->pdf->Cell(15, 5, $d['martes']);
            }
            if (!empty($d['miercoles'])) {
                $this->pdf->SetXY(60, $y + 90);
                $this->pdf->Cell(15, 5, $d['miercoles']);
            }
            if (!empty($d['jueves'])) {
                $this->pdf->SetXY(60, $y + 100);
                $this->pdf->Cell(15, 5, $d['jueves']);
            }
            if (!empty($d['viernes'])) {
                $this->pdf->SetXY(60, $y + 110);
                $this->pdf->Cell(15, 5, $d['viernes']);
            }
            if (!empty($d['sabado'])) {
                $this->pdf->SetXY(60, $y + 120);
                $this->pdf->Cell(15, 5, $d['sabado']);
            }
            if (!empty($d['domingo'])) {
                $this->pdf->SetXY(60, $y + 130);
                $this->pdf->Cell(15, 5, $d['domingo']);
            }
        }

        // Avanzar posición (la paginacion se maneja al inicio del metodo)
        $this->cont++;
        $this->y += 150;
    }

    public function output(string $mode = 'I', string $filename = 'tarjetas.pdf')
    {
        $this->pdf->Output($mode, $filename);
    }
}