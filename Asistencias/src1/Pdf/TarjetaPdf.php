<?php
namespace src\Pdf;

require_once('../../fpdf/fpdf.php');
use FPDF;

class TarjetaPdf {
    private $pdf;
    private $cont = 0;
    // Controlador de posicion en vertical
    private $y = 2;

    public function __construct() {
        $this->pdf = new FPDF('P');
        $this->pdf->SetAutoPageBreak(false);
    }

    public function addPage() {
        $this->pdf->AddPage();
    }

    public function renderizarEmpleado(array $emp, array $txs, array $descs, string $fechai, string $fechaf, string $nominaActual = '')
    {
        // Datos del empleado
        $noemp  = $emp['NoEmp'];
        $nombre = $emp['Nombre'];
        $puesto = $emp['Puesto'];
        $depto  = $emp['DepartamentoClave'];

        // Generación del PDF usando array (transaccion y descansos)
        $this->pdf->Ln(20);
        $this->pdf->SetFont('Arial', 'B', 10);
        $x = 2;
        $y = $this->y;

        // Dibujo del marco y datos básicos
        $this->pdf->rect($x, $y, 205, 142);
        $this->pdf->Image('../../img/logo.jpg', $x + 5, $y + 5, 60);
        $this->pdf->SetXY($x + 5, $y + 10);
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
        $this->pdf->rect($x + 5, $y + 50, 80, 90);
        $this->pdf->SetXY($x + 5, $y + 50);

        $this->pdf->SetFont('Arial', 'B', 7);
        $this->pdf->multiCell(2, 5, "DIA L M M J V S D");
        $this->pdf->SetXY($x + 10, $y + 50);
        $this->pdf->Cell(15, 5, "I TURNO");
        $this->pdf->Cell(15, 5, "I TURNO");
        $this->pdf->Cell(24, 5, "III TURNO");
        $yi = $this->pdf->GETY();
        $xi = $this->pdf->GETX();
        $this->pdf->multiCell(20, 20, "TIEMPOS");
        $this->pdf->SETY($yi);
        $this->pdf->SETX($xi + 15);
        $this->pdf->SetXY($x + 10, $y + 55);
        $this->pdf->Cell(15, 5, "ENTRADA");
        $this->pdf->Cell(15, 5, "SALIDA");
        $this->pdf->Cell(15, 5, "ENTRADA");
        $this->pdf->SetXY($x + 10, $y + 60);
        $this->pdf->Cell(15, 5, "III TURNO");
        $this->pdf->Cell(15, 5, "II TURNO");
        $this->pdf->Cell(15, 5, "II TURNO");
        $this->pdf->SetXY($x + 10, $y + 65);
        $this->pdf->Cell(15, 5, "SALIDA");
        $this->pdf->Cell(15, 5, "ENTRADA");
        $this->pdf->Cell(15, 5, "SALIDA");
        $this->pdf->line($x + 10, $y + 55, $x + 55, $y + 55);
        $this->pdf->line($x + 10, $y + 60, $x + 55, $y + 60);
        $this->pdf->line($x + 10, $y + 65, $x + 55, $y + 65);
        $this->pdf->line($x + 5, $y + 70, $x + 85, $y + 70);
        $this->pdf->line($x + 5, $y + 80, $x + 85, $y + 80);
        $this->pdf->line($x + 5, $y + 90, $x + 85, $y + 90);
        $this->pdf->line($x + 5, $y + 100, $x + 85, $y + 100);
        $this->pdf->line($x + 5, $y + 110, $x + 85, $y + 110);
        $this->pdf->line($x + 5, $y + 120, $x + 85, $y + 120);
        $this->pdf->line($x + 5, $y + 130, $x + 85, $y + 130);
        $this->pdf->line($x + 10, $y + 50, $x + 10, $y + 140);
        $this->pdf->line($x + 25, $y + 50, $x + 25, $y + 140);
        $this->pdf->line($x + 40, $y + 50, $x + 40, $y + 140);
        $this->pdf->line($x + 55, $y + 50, $x + 55, $y + 140);

        $this->pdf->rect($x + 90, $y + 20, 110, 120);
        $this->pdf->SetXY($x + 90, $y + 20);
        $this->pdf->Cell(2, 5, "");
        $this->pdf->Cell(22, 5, "CONCEPTO");
        $this->pdf->Cell(20, 5, "# DIAS");
        $this->pdf->Cell(16, 5, "# HRS");
        $this->pdf->Cell(22, 5, "CAMBIO PROV");
        $this->pdf->Cell(20, 5, "OBSERVACIONES");
        $this->pdf->SetXY($x + 152, $y + 25);
        $this->pdf->Cell(20, 5, "AL PUESTO");
        // Vertical
        $this->pdf->line($x + 110, $y + 20, $x + 110, $y + 140);
        $this->pdf->line($x + 130, $y + 20, $x + 130, $y + 140);
        $this->pdf->line($x + 150, $y + 20, $x + 150, $y + 140);
        $this->pdf->line($x + 170, $y + 20, $x + 170, $y + 140);
        // Horizontal
        for ($sub = 30; $sub <= 130; $sub += 10) {
            $this->pdf->line($x + 90, $y + $sub, $x + 200, $y + $sub);
        }
        $this->pdf->SetFont('Arial', '', 7);

        $horasx = 12;
        $horasy = $y;
        $antrow = 0;

        foreach ($txs as $t) {
            $eventTime = $t['event_time'];
            $hora = ($eventTime instanceof \DateTime) ? $eventTime->format('H:i:s') : date('H:i:s', strtotime($eventTime));
            $dia = (int)$t['dia_semana'];

            if ($dia === $antrow) {
                $horasx += 15;
            } else {
                $horasx = 12;
            }

            switch ($dia) {
                case 2: $this->pdf->SetXY($x + $horasx, $horasy + 70); break;
                case 3: $this->pdf->SetXY($x + $horasx, $horasy + 80); break;
                case 4: $this->pdf->SetXY($x + $horasx, $horasy + 90); break;
                case 5: $this->pdf->SetXY($x + $horasx, $horasy + 100); break;
                case 6: $this->pdf->SetXY($x + $horasx, $horasy + 110); break;
                case 7: $this->pdf->SetXY($x + $horasx, $horasy + 120); break;
                case 1: $this->pdf->SetXY($x + $horasx, $horasy + 130); break;
            }
            // Agregacion de datos
            $this->pdf->Cell(15, 5, $hora);
            $antrow = $dia;
        }

        // Cabecera y generacion de contenido para validacion de descansos
	    $this->pdf->SetFont('Arial', 'B', 10);
        foreach ($descs as $d) {
            if (!empty($d['lunes'])) {
                $this->pdf->SetXY(60, $horasy + 70);
                $this->pdf->Cell(15, 5, $d['lunes']);
            }
            if (!empty($d['martes'])) {
                $this->pdf->SetXY(60, $horasy + 80);
                $this->pdf->Cell(15, 5, $d['martes']);
            }
            if (!empty($d['miercoles'])) {
                $this->pdf->SetXY(60, $horasy + 90);
                $this->pdf->Cell(15, 5, $d['miercoles']);
            }
            if (!empty($d['jueves'])) {
                $this->pdf->SetXY(60, $horasy + 100);
                $this->pdf->Cell(15, 5, $d['jueves']);
            }
            if (!empty($d['viernes'])) {
                $this->pdf->SetXY(60, $horasy + 110);
                $this->pdf->Cell(15, 5, $d['viernes']);
            }
            if (!empty($d['sabado'])) {
                $this->pdf->SetXY(60, $horasy + 120);
                $this->pdf->Cell(15, 5, $d['sabado']);
            }
            if (!empty($d['domingo'])) {
                $this->pdf->SetXY(60, $horasy + 130);
                $this->pdf->Cell(15, 5, $d['domingo']);
            }
        }

        // Avanzar posición y paginar
        $this->cont++;
        $this->y += 150;

        if ($this->cont % 2 === 0) {
            $this->pdf->AddPage();
            // Reinicio de posicion para nueva hoja
            $this->y = 2;
        }
    }

    public function output(string $mode = 'I', string $filename = 'tarjetas.pdf') {
        $this->pdf->Output($mode, $filename);
    }
    
}