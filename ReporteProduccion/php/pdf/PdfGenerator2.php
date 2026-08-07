<?php
require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';

class PdfGenerator
{
    private TCPDF $pdf;

    // ── Dimensiones página Legal horizontal (355.6 × 215.9 mm) ──
    private float $pageW    = 279.4;  // LETTER horizontal
    private float $pageH    = 215.9;
    private float $marginL  = 8.0;
    private float $marginR  = 8.0;
    private float $marginT  = 22.0;
    private float $marginB  = 8.0;

    // ── Ancho útil y proporciones columnas ──
    private float $usableW;
    private float $colLeftW;   // 66%
    private float $colRightW;  // 34%
    private float $colSepW = 2.0;

    // ── Colores ──
    private array $cHeader1  = [73,  100, 114]; // #496472
    private array $cHeader2  = [121, 154, 172]; // #799AAC
    private array $cMaq      = [184, 205, 214]; // #B8CDD6
    private array $cProd     = [237, 242, 244]; // #EDF2F4
    private array $cTotalMaq = [208, 228, 236]; // #D0E4EC
    private array $cTotalProd= [219, 234, 240]; // #dbeaf0
    private array $cWhite    = [255, 255, 255];
    private array $cAlt      = [247, 250, 251]; // #F7FAFB
    private array $cText     = [26,  26,  26];  // #1A1A1A
    private array $cAccent   = [73,  100, 114]; // #496472 texto énfasis
    private array $cBorder   = [159, 186, 197]; // #9FBAC5
    private array $cMorado   = [123, 45,  139]; // #7B2D8B
    private array $cVerde    = [28,  105, 58];  // #1C693A
    private array $cAmarillo = [184, 134, 11];  // #B8860B
    private array $cRojo     = [154, 28,  28];  // #9A1C1C

    // ── Fuentes y tamaños ──
    private string $font    = 'helvetica';
    private float  $fsBase  = 4.5;  // tamaño base celdas
    private float  $fsSmall = 4.0;  // claves/etapas
    private float  $fsTh    = 4.0;  // encabezados tabla
    private float  $fsTitle = 6.0; // título operación
    private float  $fsDepto = 6.0; // título departamento

    // ── Alturas de fila ──
    private float $rowH     = 2.7;  // fila normal (tablas de producción) - v2 compacta
    private float $rowHTh   = 2.5;  // fila encabezado
    private float $rowHProg = 2.0;  // fila del panel de Programa (compactada, como la tenías)
    private float $rowHThProg = 2.0;  // fila de encabezado del panel de Programa (compactada)

    // ── Datos del reporte para repetir en cada página ──
    private string $fechaReporte = '';
    private string $logoPathReporte = '';

    // Cursor de posición/página al terminar el último departamento dibujado —
    // permite que el siguiente departamento continúe en la misma hoja si hay
    // espacio, en vez de saltar de página siempre.
    private float $yLeftCursor  = 0;
    private float $yRightCursor = 0;
    private int   $paginaLeftCursor  = 0;
    private int   $paginaRightCursor = 0;

    public function __construct()
    {
        $this->pdf = new TCPDF('L', 'mm', 'A3', true, 'UTF-8', false);

        $this->pdf->SetCreator('Prosede');
        $this->pdf->SetAuthor('Reporte Diario');
        $this->pdf->SetTitle('Reporte Diario');

        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        $this->pdf->SetMargins($this->marginL, $this->marginT, $this->marginR);
        $this->pdf->SetAutoPageBreak(false, $this->marginB);

        $this->pdf->SetFont($this->font, '', $this->fsBase);

        $this->usableW  = $this->pageW - $this->marginL - $this->marginR;
        $this->colLeftW = round($this->usableW * 0.72, 2);
        $this->colRightW= round($this->usableW - $this->colLeftW - $this->colSepW, 2);
    }

    // ══════════════════════════════════════════════════════════════
    // API PÚBLICA
    // ══════════════════════════════════════════════════════════════

    public function iniciar(array $fechas, array $tablas, array $programa, string $fecha, string $logoPath = ''): void
    {
        // Guardar para que aparezcan en TODAS las páginas, no solo la primera
        $this->fechaReporte    = $fecha;
        $this->logoPathReporte = $logoPath;

        // Página horizontal — producción (izq) + programa (der) en la misma hoja
        $this->pdf->AddPage('L', 'LETTER');
        $this->dibujarHeader($fecha, $logoPath);
        $pagina = $this->pdf->getPage();

        [$yLeft, $yRight, $paginaLeft, $paginaRight] = $this->dibujarDepartamento(
            $fechas, $tablas, $programa, $this->marginT, $this->marginT, $pagina, true
        );

        $this->yLeftCursor        = $yLeft;
        $this->yRightCursor       = $yRight;
        $this->paginaLeftCursor   = $paginaLeft;
        $this->paginaRightCursor  = $paginaRight;
    }

    public function agregarSeccion(array $fechas, array $tablas, array $programa, string $nombreDepto): void
    {
        $programa['nombreDepto'] = $nombreDepto;

        // ── ¿Cabe este departamento en la hoja donde quedó el anterior? ──
        // Solo se puede continuar si ambas columnas (producción y programa)
        // del departamento anterior terminaron en la MISMA página, y queda
        // espacio suficiente en ambas para al menos el título + una tabla.
        $yMaxPage      = $this->pageH - $this->marginB;
        $espacioMinimo = 45; // mm

        $puedeContinuar = $this->paginaLeftCursor > 0
            && $this->paginaLeftCursor === $this->paginaRightCursor
            && ($yMaxPage - $this->yLeftCursor)  > $espacioMinimo
            && ($yMaxPage - $this->yRightCursor) > $espacioMinimo;

        if ($puedeContinuar) {
            $this->pdf->setPage($this->paginaLeftCursor);
            $pagina      = $this->paginaLeftCursor;
            $yLeftStart  = $this->yLeftCursor + 4;
            $yRightStart = $this->yRightCursor + 4;
            $nuevaPagina = false;
        } else {
            $this->pdf->AddPage('L', 'LETTER');
            $this->dibujarHeader($this->fechaReporte, $this->logoPathReporte);
            $pagina      = $this->pdf->getPage();
            $yLeftStart  = $this->marginT;
            $yRightStart = $this->marginT;
            $nuevaPagina = true;
        }

        [$yLeft, $yRight, $paginaLeft, $paginaRight] = $this->dibujarDepartamento(
            $fechas, $tablas, $programa, $yLeftStart, $yRightStart, $pagina, $nuevaPagina
        );

        $this->yLeftCursor        = $yLeft;
        $this->yRightCursor       = $yRight;
        $this->paginaLeftCursor   = $paginaLeft;
        $this->paginaRightCursor  = $paginaRight;
    }

    /**
     * iniciarPar()
     *
     * Layout especial de 4 columnas para 2 departamentos en 1 sola hoja:
     * - Izquierda: producción del depto 1, luego producción del depto 2
     *   apilada debajo (una sola columna ancha, como siempre).
     * - Derecha: panel de Programa del depto 1 y panel de Programa del
     *   depto 2, uno junto al otro (cada uno a la mitad del ancho de la
     *   columna derecha normal).
     *
     * Llamar UNA vez, en vez de iniciar() + agregarSeccion() para ese par
     * de departamentos. Después de esto, se puede seguir llamando
     * agregarSeccion() normalmente para el resto de departamentos.
     */
    public function iniciarPar(
        array $fechas1, array $tablas1, array $programa1, string $nombre1,
        array $fechas2, array $tablas2, array $programa2, string $nombre2,
        string $fecha, string $logoPath = ''
    ): void {
        $this->fechaReporte    = $fecha;
        $this->logoPathReporte = $logoPath;

        $this->pdf->AddPage('L', 'LETTER');
        $this->dibujarHeader($fecha, $logoPath);
        $pagina = $this->pdf->getPage();

        $xLeft  = $this->marginL;
        $xProg1 = $this->marginL + $this->colLeftW + $this->colSepW;
        $wHalf  = round(($this->colRightW - 1.0) / 2, 2);
        $xProg2 = $xProg1 + $wHalf + 1.0;

        $yMax   = $this->pageH - $this->marginB;
        $yInicio = $this->marginT - 4;

        // ── Programa depto 1 (mitad izquierda de la columna derecha) ──
        $programa1['nombreDepto'] = $nombre1;
        [$yR1, $pagR1] = $this->dibujarPrograma($programa1, $xProg1-50, $yInicio-5, $yMax, 0, $wHalf+20);

        // ── Programa depto 2 (mitad derecha de la columna derecha) ──
        $this->pdf->setPage($pagina);
        $programa2['nombreDepto'] = $nombre2;
        [$yR2, $pagR2] = $this->dibujarPrograma($programa2, $xProg2-25, $yInicio-5, $yMax, 0, $wHalf+20);

        // ── Producción: depto 1 y luego depto 2, apiladas en col. izquierda ──
        $this->pdf->setPage($pagina);
        $yLeft        = $yInicio;
        $paginaActual = $pagina;

        // Título depto 1
        $this->pdf->SetFont($this->font, 'B', $this->fsDepto);
        $this->setTextColor($this->cHeader1);
        $this->pdf->SetXY($xLeft, $yLeft - 10);
        $this->pdf->Cell($this->colLeftW-67, 6, $nombre1, 0, 0, 'C');
        $yLeft += -1;

        foreach ($tablas1 as $categoria => $maquinas) {
            $yLeft = $this->dibujarTablaUSTD($fechas1, $categoria, $maquinas, $xLeft, $yLeft, $yMax, $paginaActual);
            $paginaActual = $this->pdf->getPage();
            $yLeft += 1;
        }

        // Título depto 2
        $yLeft += 3;
        $this->pdf->setPage($paginaActual);
        $this->pdf->SetFont($this->font, 'B', $this->fsDepto);
        $this->setTextColor($this->cHeader1);
        $this->pdf->SetXY($xLeft, $yLeft - 9);
        $this->pdf->Cell($this->colLeftW-67, 6, $nombre2, 0, 0, 'C');
        $yLeft += -1;

        foreach ($tablas2 as $categoria => $maquinas) {
            $yLeft = $this->dibujarTablaUSTD($fechas2, $categoria, $maquinas, $xLeft, $yLeft, $yMax, $paginaActual);
            $paginaActual = $this->pdf->getPage();
            $yLeft += 1;
        }

        // ── Dejar el puntero en la última página usada ──
        $ultimaPagina = max($paginaActual, $pagR1, $pagR2);
        $this->pdf->setPage($ultimaPagina);

        // Cursores por si se agrega otro departamento después de este par
        $this->yLeftCursor       = $yLeft;
        $this->yRightCursor      = max($yR1, $yR2);
        $this->paginaLeftCursor  = $paginaActual;
        $this->paginaRightCursor = max($pagR1, $pagR2);
    }

    /**
     * agregarProteccionTabbiSoar()
     *
     * Página combinada de 3 departamentos en 1 sola hoja, igual estilo que
     * la hoja 1 (iniciarPar):
     * - Izquierda: producción de Protección Femenina, luego TABBI, luego
     *   SOAR/Hook, apiladas en una sola columna ancha.
     * - Derecha: panel de Programa de Protección Femenina y panel de
     *   Programa de TABBI, uno junto al otro (SOAR no lleva panel de
     *   programa, igual que en su hoja actual).
     *
     * Reemplaza las llamadas por separado a agregarSeccion() (Protección
     * Femenina), agregarTabbi() y agregarHook(): se llama UNA sola vez,
     * pasando los mismos datos que ya se le pasan a esas tres.
     */
    public function agregarProteccionTabbiSoar(
        array $fechasPF, array $tablasPF, array $programaPF, string $nombrePF,
        array $fechasTabbi, array $tablasTabbi, array $programaTabbi, string $nombreTabbi,
        array $fechasSoar, array $tablasSoar, string $nombreSoar = 'HOOK'
    ): void {
        $this->pdf->AddPage('L', 'LETTER');
        $this->dibujarHeader($this->fechaReporte, $this->logoPathReporte);
        $pagina = $this->pdf->getPage();

        $xLeft  = $this->marginL;
        $xProg1 = $this->marginL + $this->colLeftW + $this->colSepW;
        $wHalf  = round(($this->colRightW - 1.0) / 2, 2);
        $xProg2 = $xProg1 + $wHalf + 1.0;

        $yMax     = $this->pageH - $this->marginB;
        $yInicio  = $this->marginT - 4;

        // ── Programa Protección Femenina (mitad izquierda de la col. derecha) ──
        $programaPF['nombreDepto'] = $nombrePF;
        [$yR1, $pagR1] = $this->dibujarPrograma($programaPF, $xProg1 - 50, $yInicio - 5, $yMax, 0, $wHalf + 20);

        // ── Programa TABBI (mitad derecha de la col. derecha) ──
        $this->pdf->setPage($pagina);
        $programaTabbi['nombreDepto'] = $nombreTabbi;
        [$yR2, $pagR2] = $this->dibujarPrograma($programaTabbi, $xProg2 - 25, $yInicio - 5, $yMax, 3, $wHalf + 20);

        // ── Producción: PF, luego TABBI, luego SOAR — apiladas en col. izquierda ──
        $this->pdf->setPage($pagina);
        $yLeft        = $yInicio;
        $paginaActual = $pagina;

        // Título Protección Femenina
        $this->pdf->SetFont($this->font, 'B', $this->fsDepto);
        $this->setTextColor($this->cHeader1);
        $this->pdf->SetXY($xLeft, $yLeft - 8);
        $this->pdf->Cell($this->colLeftW-67, 6, $nombrePF, 0, 0, 'C');
        $yLeft += 4;

        foreach ($tablasPF as $categoria => $maquinas) {
            $yLeft = $this->dibujarTablaUSTD($fechasPF, $categoria, $maquinas, $xLeft, $yLeft, $yMax, $paginaActual);
            $paginaActual = $this->pdf->getPage();
            $yLeft += 1;
        }

        // ── De aquí para abajo: HOOK y TELAS lado a lado
        $wMitad = round(($this->colLeftW - 6.0) * 0.50, 2);
        $wMitadHook = round(($this->colLeftW - 6.0) - $wMitad, 2);
        $xHook  = $xLeft;
        $xTabbi = $xLeft + $wMitad + 6.0;
        
        // ── HOOK: posición relativa (depende de Protección Femenina) ──
        $yHookAbajo = $yLeft + 3;  // 3mm después de PF
        
        // ── TELAS NO TEJIDAS: posición FIJA (independiente de todo) ──
        $yTablasAbajo = 140;  // ← POSICIÓN FIJA: no depende de otras tablas
        
        $paginaAbajo = $paginaActual;

        // Títulos de las dos mitades
        $this->pdf->SetFont($this->font, 'B', $this->fsDepto);
        $this->setTextColor($this->cHeader1);
        $this->pdf->SetXY($xHook, $yHookAbajo);
        $this->pdf->Cell($wMitadHook, 6, $nombreSoar, 0, 0, 'C');
        $this->pdf->SetXY($xTabbi+50, $yTablasAbajo);
        $this->pdf->Cell($wMitad, 6, $nombreTabbi, 0, 0, 'C');

        // ── Mitad izquierda: Hook ──
        $yHook       = $yHookAbajo + 6;
        $paginaHook  = $paginaAbajo;
        foreach ($tablasSoar as $categoria => $maquinas) {
            $yHook = $this->dibujarTablaTabbi($fechasSoar, $categoria, $maquinas, $xHook, $yHook, $yMax, $paginaHook, $wMitadHook);
            $paginaHook = $this->pdf->getPage();
            $yHook += 1;
        }

        // ── Mitad derecha: Telas No Tejidas (TABBI + Spooler) - POSICIÓN FIJA ──
        $this->pdf->setPage($paginaAbajo);
        $yTabbi       = $yTablasAbajo + 6;  // ← Después del título, pero yTablasAbajo es FIJO
        $paginaTabbi  = $paginaAbajo;
        foreach ($tablasTabbi as $categoria => $maquinas) {
            $yTabbi = $this->dibujarTablaTabbi($fechasTabbi, $categoria, $maquinas, $xTabbi+40, $yTabbi, $yMax, $paginaTabbi, $wMitad);
            $paginaTabbi = $this->pdf->getPage();
            $yTabbi += 1;
        }

        $yLeft        = max($yTabbi, $yHook);
        $paginaActual = max($paginaTabbi, $paginaHook);

        // ── Dejar el puntero en la última página usada ──
        $ultimaPagina = max($paginaActual, $pagR1, $pagR2);
        $this->pdf->setPage($ultimaPagina);

        // Cursores por si se agrega otro departamento después de este bloque
        $this->yLeftCursor       = $yLeft;
        $this->yRightCursor      = max($yR1, $yR2);
        $this->paginaLeftCursor  = $paginaActual;
        $this->paginaRightCursor = max($pagR1, $pagR2);
    }

    public function finalizar(string $nombreArchivo = 'reporte.pdf'): void
    {
        // Insertar la fecha del reporte en el nombre del archivo,
        // ej. "reporte_diario_gerencia.pdf" -> "reporte_diario_gerencia_2026-06-29.pdf"
        if ($this->fechaReporte) {
            $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION) ?: 'pdf';
            $base      = pathinfo($nombreArchivo, PATHINFO_FILENAME);
            $nombreArchivo = $base . '_' . $this->fechaReporte . '.' . $extension;
        }

        $this->pdf->Output($nombreArchivo, 'I');
    }

    // ══════════════════════════════════════════════════════════════
    // HEADER
    // ══════════════════════════════════════════════════════════════

    private function dibujarHeader(string $fecha, string $logoPath): void
    {
        $y = $this->marginT - 14;
        $x = $this->marginL;
        $w = $this->usableW;

        // Logo
        if ($logoPath && file_exists($logoPath)) {
            $this->pdf->Image($logoPath, $x, $y-4, 0, 4, '', '', '', false, 300);
        }

        // Título central
        $this->pdf->SetFont($this->font, 'B', 6);
        $this->setTextColor($this->cHeader1);
        $this->pdf->SetXY($x, $y-7);
        $this->pdf->Cell(263, 8, 'REPORTE DIARIO GERENCIA', 0, 0, 'C');

        // Fecha derecha
        if ($fecha) {
            $this->pdf->SetFont($this->font, '', 5);
            $this->setTextColor([119, 119, 119]);
            $this->pdf->SetXY($x+190, $y-7);
            $this->pdf->Cell(70, 8, $this->formatFecha($fecha), 0, 0, 'R');
        }

        // Línea separadora
        $lineY = $y;
        $this->setDrawColor($this->cHeader1);
        $this->pdf->SetLineWidth(0.3);
        $this->pdf->Line($x, $lineY,268, $lineY);

        $this->pdf->SetLineWidth(0.2);
    }

    // ══════════════════════════════════════════════════════════════
    // HEADER PORTRAIT
    // ══════════════════════════════════════════════════════════════

    private function dibujarHeaderPortrait(string $fecha, string $logoPath): void
    {
        // En portrait A3: 297mm ancho, 420mm alto
        $y = $this->marginT - 14;
        // Letter portrait: ancho útil
        $w = 215.9 - $this->marginL - $this->marginR;

        if ($logoPath && file_exists($logoPath)) {
            $this->pdf->Image($logoPath, $this->marginL, $y, 0, 8, '', '', '', false, 300);
        }

        $this->pdf->SetFont($this->font, 'B', 11);
        $this->setTextColor($this->cHeader1);
        $this->pdf->SetXY($this->marginL, $y);
        $this->pdf->Cell($w, 8, 'Reporte Diario', 0, 0, 'C');

        if ($fecha) {
            $this->pdf->SetFont($this->font, '', 8);
            $this->setTextColor([119, 119, 119]);
            $this->pdf->SetXY($this->marginL, $y);
            $this->pdf->Cell($w, 8, $this->formatFecha($fecha), 0, 0, 'R');
        }

        $lineY = $y + 9;
        $this->setDrawColor($this->cHeader1);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line($this->marginL, $lineY, $this->marginL + $w, $lineY);
        $this->pdf->SetLineWidth(0.2);
    }

    private function dibujarDepartamento(
        array $fechas, array $tablas, array $programa,
        float $yLeftStart, float $yRightStart, int $paginaInicio, bool $nuevaPagina
    ): array {
        $xLeft  = $this->marginL;
        $xRight = $this->marginL + $this->colLeftW + $this->colSepW;
        $yMax   = $this->pageH - $this->marginB;

        $nombreDepto = $programa['nombreDepto'] ?? '';

        $this->pdf->setPage($paginaInicio);

        // ── Título departamento ──
        // Hoja nueva: título "flotando" arriba del margen (como siempre).
        // Continuando en la misma hoja: título en línea con el flujo normal.
        $this->pdf->SetFont($this->font, 'B', $this->fsDepto);
        $this->setTextColor($this->cHeader1);
        if ($nuevaPagina) {
            $this->pdf->SetXY($xLeft, $yLeftStart - 13);
            $yTituloBottom = $yLeftStart + 9;
        } else {
            $this->pdf->SetXY($xLeft, $yLeftStart);
            $yTituloBottom = $yLeftStart + 8;
        }
        $this->pdf->Cell(263, 6, $nombreDepto, 0, 0, 'C');

        $paginaInicioDepto = $this->pdf->getPage();

        // ── Dibujar programa primero (columna derecha) ──
        // Puede crear páginas nuevas si su contenido es largo
        $yProgInicio = $nuevaPagina ? ($yTituloBottom - 12) : ($yRightStart + 8);
        [$yRightFinal, $paginaProgFinal] = $this->dibujarPrograma($programa, $xRight, $yProgInicio, $yMax - 12);

        // ── Volver a la página inicial para dibujar producción ──
        $this->pdf->setPage($paginaInicioDepto);

        // ── Dibujar producción (columna izquierda) ──
        $yCursorLeft  = $nuevaPagina ? ($yTituloBottom - 12) : ($yLeftStart + 8);
        $paginaActual = $paginaInicioDepto;

        foreach ($tablas as $categoria => $maquinas) {
            $yCursorLeft = $this->dibujarTablaUSTD(
                $fechas,
                $categoria,
                $maquinas,
                $xLeft,
                $yCursorLeft,
                $yMax,
                $paginaActual
            );

            $paginaActual = $this->pdf->getPage();
            $yCursorLeft += 1;
        }

        // ── Dejar el puntero en la última página usada ──
        $ultimaPagina = max($paginaActual, $paginaProgFinal);
        $this->pdf->setPage($ultimaPagina);

        // Posiciones/páginas finales de cada columna, para que el siguiente
        // departamento decida si puede continuar en la misma hoja o no.
        return [$yCursorLeft, $yRightFinal, $paginaActual, $paginaProgFinal];
    }

    // ══════════════════════════════════════════════════════════════
    // TABLA USTD
    // ══════════════════════════════════════════════════════════════

    private function dibujarTablaUSTD(
        array $fechas, string $categoria, array $maquinas,
        float $x, float $y, float $yMax, int $pagina,
        float $anchoDisponible = 0
    ): float {
        if ($anchoDisponible <= 0) $anchoDisponible = $this->colLeftW;
        $this->pdf->setPage($pagina);

        // ── Título operación ──
        // Si la tabla completa no cabe en el espacio restante de esta página,
        // pero sí cabría en una página nueva completa, saltamos de una vez
        // para no dejar el título "huérfano" arriba y el resto en la siguiente.
        $alturaTabla     = 7 + $this->estimarAlturaTabla($maquinas); // 7 = título + línea
        $espacioRestante = $yMax - $y;
        $alturaPaginaUtil = $yMax - $this->marginT;

        if ($alturaTabla > $espacioRestante) {
            if ($alturaTabla <= $alturaPaginaUtil || $y > $this->marginT + 5) {
                // No cabe aquí: o cabe entera en una página nueva, o ya hay
                // contenido dibujado en esta página (no es la primera tabla).
                $pagina++;
                $this->pdf->AddPage('L', 'LETTER');
                $this->dibujarHeader($this->fechaReporte, $this->logoPathReporte);
                // No hay título de departamento en esta página nueva, así que
                // se sube el cursor para aprovechar ese espacio (mismo offset
                // que usa la primera tabla del departamento: yContent - 12).
                $y = $this->marginT - 3;
            }
        } elseif ($y + 5 > $yMax) {
            $pagina++;
            $this->pdf->AddPage('L', 'LETTER');
            $this->dibujarHeader($this->fechaReporte, $this->logoPathReporte);
            $y = $this->marginT - 3;
        }

        $this->pdf->SetFont($this->font, 'B', $this->fsTitle);
        $this->setTextColor($this->cHeader1);
        $this->pdf->SetXY($x, $y-7);
        $this->pdf->Cell($anchoDisponible, 5, $categoria, 0, 0, 'L');

        // Línea bajo título operación
        // $this->setDrawColor($this->cHeader1);
        // $this->pdf->SetLineWidth(0.4);
        // $tW = $this->pdf->GetStringWidth($categoria) + 2;
        // $this->pdf->Line($x, $y, $x + $tW, $y );
        // $this->pdf->SetLineWidth(0.2);

        $y += 3;

        // ── Definir anchos de columnas ──
        $nFechas  = count($fechas);
        $wMaq     = 9.0;
        $wPres    = 45.0;
        $wFecha   = round(($anchoDisponible * 0.40) / max($nFechas, 1), 2);
        $wAcum    = 8.0;
        $wProm    = 8.0;
        $wTP      = 8.0;
        $wMerma   = 8.0;

        // Ajustar wFecha si hay muchas fechas
        $totalFechasW = $wFecha * $nFechas;
        $totalFijo    = $wMaq + $wPres + $wAcum + $wProm + ($wTP * 2) + ($wMerma * 2);
        if ($totalFijo + $totalFechasW > $anchoDisponible) {
            $wFecha = round(($anchoDisponible - $totalFijo) / max($nFechas, 1), 2);
        }

        // ── Encabezados ──
        $y = $this->dibujarEncabezadoUSTD($fechas, $x, $y-1, $yMax, $pagina,
            $wMaq, $wPres, $wFecha, $wAcum, $wProm, $wTP, $wMerma);
        $pagina = $this->pdf->getPage();

        // ── Filas de máquinas ──
        $totalOp = $maquinas['_total_operacion'];
        unset($maquinas['_total_operacion']);

        foreach ($maquinas as $nombreMaq => $dataMaq) {
            [$y, $pagina] = $this->dibujarMaquina(
                $fechas, $nombreMaq, $dataMaq, $x, $y, $yMax, $pagina,
                $wMaq, $wPres, $wFecha, $wAcum, $wProm, $wTP, $wMerma
            );
        }

        // ── Total operación ──
        [$y, $pagina] = $this->verificarEspacio($y, $this->rowH, $yMax, $pagina, $x);
        $this->dibujarFilaTotalOp($fechas, $categoria, $totalOp, $x, $y,
            $wMaq, $wPres, $wFecha, $wAcum, $wProm, $wTP, $wMerma);
        $y += $this->rowH;

        return $y;
    }

    private function dibujarEncabezadoUSTD(
        array $fechas, float $x, float $y, float $yMax, int $pagina,
        float $wMaq, float $wPres, float $wFecha,
        float $wAcum, float $wProm, float $wTP, float $wMerma
    ): float {
        [$y, $pagina] = $this->verificarEspacio($y, $this->rowHTh * 2, $yMax, $pagina, $x);
        $this->pdf->setPage($pagina);

        $nF = count($fechas);

        // Fila 1 encabezados agrupados
        $this->pdf->SetFont($this->font, 'B', $this->fsTh);
        $this->setFillColor($this->cHeader1);
        $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
        $this->setTextColor($this->cWhite);

        $cx = $x;
        $this->pdf->SetXY($cx, $y-5);
        $this->pdf->Cell($wMaq-2,  $this->rowHTh, 'Máquina',     $this->border(), 0, 'C', true);
        $cx += $wMaq;
        $this->pdf->SetXY($cx-2, $y - 5);
        $this->pdf->Cell($wPres-26, $this->rowHTh, 'Presentación', $this->border(), 0, 'C', true);
        $cx += $wPres;

        $wFechasTotal = $wFecha * $nF;
        $this->pdf->SetXY($cx, $y-5);
        $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
        $this->pdf->SetXY($cx-28, $y-5);
        $this->pdf->Cell($wFechasTotal - 28, $this->rowHTh, 'USTD Días Anteriores', 1, 0, 'C', true);
        $cx += $wFechasTotal;

        $this->pdf->SetLineWidth(0.2);
        $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
        $this->pdf->SetXY($cx- 56, $y-5);
        $this->pdf->Cell($wAcum + $wProm-2, $this->rowHTh, 'USTD', $this->border(), 0, 'C', true);
        $cx += $wAcum + $wProm;

        $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
        $this->pdf->SetXY($cx-58, $y-5);
        $this->pdf->Cell($wAcum + $wProm + 5, $this->rowHTh, '% TP', $this->border(), 0, 'C', true);
        $cx += $wTP * 2 + $wAcum;

        $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
        $this->pdf->SetXY($cx-61, $y-5);
        $this->pdf->Cell($wAcum + $wProm - 2, $this->rowHTh, '% Merma', $this->border(), 0, 'C', true);

        $y += $this->rowHTh;

        // Fila 2: fechas individuales + Acum/Prom/Día/Acum
        $this->setFillColor($this->cHeader2);
        $cx = $x + $wMaq + $wPres;

        foreach ($fechas as $f) {
            $this->pdf->SetXY($cx-28, $y-5);
            $this->pdf->Cell($wFecha-4, $this->rowHTh, $this->formatFechaDia($f), $this->border(), 0, 'C', true);
            $cx += $wFecha-4;
        }

        $this->setFillColor($this->cHeader2);
        $this->pdf->SetXY(8, $y-5);
        $this->pdf->Cell(26, $this->rowHTh, '', $this->border(), 0, 'C', true);
        foreach (['Acum', 'Prom', 'HRS T', 'Día', 'Acum', 'Día', 'Acum'] as $lbl) {
            $w = in_array($lbl, ['Acum', 'Prom']) ? ($lbl === 'Acum' ? $wAcum : $wAcum) : ($lbl === 'Día' ? $wAcum : $wAcum);
            // alternar entre TP y Merma
            $this->pdf->SetXY($cx-28, $y-5);
            $this->pdf->Cell($wAcum-1, $this->rowHTh, $lbl, $this->border(), 0, 'C', true);
            $cx += $wAcum-1;
        }

        $y += $this->rowHTh;
        return $y;
    }

    private function dibujarMaquina(
        array $fechas, string $nombreMaq, array $dataMaq,
        float $x, float $y, float $yMax, int $pagina,
        float $wMaq, float $wPres, float $wFecha,
        float $wAcum, float $wProm, float $wTP, float $wMerma
    ): array {
        $sinDatos = empty($dataMaq['productos']);

        // ── Una máquina puede no tener detalle de los últimos 7 días (sin
        // movimiento reciente) pero sí tener acumulado del mes / %TP / %Merma.
        // En ese caso no debe pintarse en blanco: se dibuja la fila de
        // totales con sus indicadores reales.
        $tieneAcumulado = (
            (float)($dataMaq['total_maquina']['acum'] ?? 0) > 0
            || $this->fmtPct($dataMaq['tp_acum']    ?? '-') !== '-'
            || $this->fmtPct($dataMaq['merma_acum'] ?? '-') !== '-'
        );

        if ($sinDatos && !$tieneAcumulado) {
            [$y, $pagina] = $this->verificarEspacio($y, $this->rowH, $yMax, $pagina, $x);
            $this->dibujarFilaMaquinaSinDatos($fechas, $nombreMaq, $x, $y, $wMaq, $wPres, $wFecha, $wAcum, $wProm, $wTP, $wMerma);
            $y += $this->rowH;
        } elseif ($sinDatos && $tieneAcumulado) {
            // Sin movimiento en los últimos 7 días, pero con acumulado del mes
            [$y, $pagina] = $this->verificarEspacio($y, $this->rowH, $yMax, $pagina, $x);
            $this->dibujarFilaMaquinaSoloAcumulado($fechas, $nombreMaq, $dataMaq, $x, $y, $wMaq, $wPres, $wFecha, $wAcum, $wProm, $wTP, $wMerma);
            $y += $this->rowH;
        } else {
            // Contar filas totales para el rowspan simulado
            // v2: 1 fila por producto (ya no hay fila header + filas de clave)
            $totalFilas      = count($dataMaq['productos']);
            $alturaTotal     = $totalFilas * $this->rowH;
            $alturaNecesaria = $alturaTotal + $this->rowH; // +1 para la fila Total máquina

            // Si la máquina completa no cabe en el espacio restante, saltar a página nueva
            // antes de empezar a dibujarla (evita que yMaqStart quede en una página y las
            // filas terminen en otra, generando celdas con altura incorrecta).
            $espacioRestante = $yMax - $y;
            if ($alturaNecesaria > $espacioRestante && $y > $this->marginT + 20) {
                $this->pdf->AddPage('L', 'LETTER');
                $this->dibujarHeader($this->fechaReporte, $this->logoPathReporte);
                $pagina++;
                $y = $this->marginT;
                $this->pdf->setPage($pagina);
            }

            $yMaqStart  = $y;
            $primerFila = true;
            $filaIdx    = 0;

            // v2: una sola fila por producto (ya no hay fila header + filas
            // de clave; el producto trae directamente sus totales por día).
            foreach ($dataMaq['productos'] as $nombreProd => $producto) {
                $paginaAntesProd = $pagina;
                [$y, $pagina] = $this->verificarEspacio($y, $this->rowH, $yMax, $pagina, $x);

                if ($primerFila) {
                    $yMaqStart  = $y;
                    $primerFila = false;
                } elseif ($pagina !== $paginaAntesProd) {
                    // Hubo salto de página en medio de la máquina:
                    // cerrar la celda de máquina parcial en la página anterior
                    $alturaParcial = $yMax - $yMaqStart;
                    if ($alturaParcial > 0) {
                        $this->pdf->setPage($paginaAntesProd);
                        $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
                        $this->setFillColor($this->cMaq);
                        $this->setTextColor($this->cText);
                        $this->pdf->SetXY($x, $yMaqStart);
                        $this->pdf->Cell($wMaq, $alturaParcial, $nombreMaq, $this->border(), 0, 'C', true);
                        $this->pdf->setPage($pagina);
                    }
                    $yMaqStart = $y;
                }

                $bg = $filaIdx % 2 === 0 ? $this->cWhite : $this->cAlt;
                $filaIdx++;

                $cx = $x + $wMaq;
                $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
                $this->setFillColor($bg);
                $this->setTextColor($this->cAccent);
                $this->pdf->SetXY($cx-2, $y-5);
                $this->pdf->Cell($wPres - 26, $this->rowH, $nombreProd, $this->border(), 0, 'L', true);
                $cx += $wPres;

                foreach ($fechas as $f) {
                    $v = $producto['dias'][$f] ?? null;
                    $txt = ($v === null || $v == 0) ? '—' : $this->fmtNum($v);
                    $this->setTextColor(($v === null || $v == 0) ? [187, 187, 187] : $this->cText);
                    $this->pdf->SetFont($this->font, '', $this->fsSmall);
                    $this->pdf->SetXY($cx-28, $y-5);
                    $this->pdf->Cell($wFecha-4, $this->rowH, $txt, $this->border(), 0, 'C', true);
                    $cx += $wFecha-4;
                }

                $y += $this->rowH;
            }

            // Dibujar celda máquina (toda la altura acumulada)
            $alturaMaq = $y - $yMaqStart;
            $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
            $this->setFillColor($this->cMaq);
            $this->setTextColor($this->cText);
            $this->pdf->SetXY($x, $yMaqStart-5);
            $this->pdf->Cell($wMaq-2, $alturaMaq, $nombreMaq, $this->border(), 0, 'C', true);

            // ── Indicadores centrados verticalmente ──
            // cx2 calculado igual que el foreach de celdas de fecha (usando -5 por celda)
            $yCentrado = $yMaqStart + ($alturaMaq - $this->rowH) / 2;
            $cx2 = $x + $wMaq + ($wPres - 5) + (($wFecha - 5) * count($fechas));
            foreach ([
                [$wAcum + 3, $this->fmtNum($dataMaq['total_maquina']['acum'])],
                [$wAcum + 3, $this->fmtNum($dataMaq['total_maquina']['prom'])],
                [$wAcum + 3, $dataMaq['hrsT']      ?? '—'],
                [$wAcum + 3, $this->fmtPct($dataMaq['tp_dia'])],
                [$wAcum + 3, $this->fmtPct($dataMaq['tp_acum'])],
                [$wAcum + 3, $this->fmtPct($dataMaq['merma_dia'])],
                [$wAcum + 3, $this->fmtPct($dataMaq['merma_acum'])],
            ] as [$wC, $val]) {
                $this->setFillColor($this->cProd);
                $this->setTextColor($this->cText);
                $this->pdf->SetXY($cx2-16, $yMaqStart - 5);
                $this->pdf->Cell($wC - 4, $alturaMaq, '', $this->border(), 0, 'C', true);
                $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
                $this->pdf->SetXY($cx2-16, $yCentrado-5);
                $this->pdf->Cell($wC - 4, $this->rowH, $val, 0, 0, 'C');
                $cx2 += $wC - 4;
            }
        }

        // Total máquina
        [$y, $pagina] = $this->verificarEspacio($y, $this->rowH, $yMax, $pagina, $x);
        $this->dibujarFilaTotalMaq($fechas, $nombreMaq, $dataMaq, $x, $y, $wMaq, $wPres, $wFecha, $wAcum, $wProm, $wTP, $wMerma);
        $y += $this->rowH;

        return [$y, $pagina];
    }

    private function dibujarFilaMaquinaSinDatos(
        array $fechas, string $nombreMaq, float $x, float $y,
        float $wMaq, float $wPres, float $wFecha, float $wAcum, float $wProm, float $wTP, float $wMerma
    ): void {
        $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
        $this->setFillColor($this->cMaq);
        $this->setTextColor($this->cText);
        $this->pdf->SetXY($x, $y-5);
        $this->pdf->Cell($wMaq, $this->rowH, $nombreMaq, $this->border(), 0, 'C', true);

        $cx = $x + $wMaq;
        $this->setFillColor($this->cWhite);
        $this->setTextColor([187, 187, 187]);
        $this->pdf->SetFont($this->font, '', $this->fsSmall);
        $this->pdf->SetXY($cx, $y-5);
        $this->pdf->Cell($wPres - 5, $this->rowH, '—', $this->border(), 0, 'C', true);
        $cx += $wPres;

        foreach ($fechas as $f) {
            $this->pdf->SetXY($cx - 5, $y-5);
            $this->pdf->Cell($wFecha - 5, $this->rowH, '—', $this->border(), 0, 'C', true);
            $cx += $wFecha - 5;
        }

        foreach ([$wAcum, $wAcum, $wAcum, $wAcum, $wAcum, $wAcum, $wAcum] as $wC) {
            $this->pdf->SetXY($cx - 5, $y-5);
            $this->pdf->Cell($wC - 1, $this->rowH, '—', $this->border(), 0, 'C', true);
            $cx += $wC - 1;
        }
    }

    /**
     * Dibuja la fila de una máquina que NO tuvo movimiento en los últimos
     * 7 días (por eso no aparece detalle por clave/producto), pero que SÍ
     * tiene Acumulado/Promedio/%TP/%Merma del mes — esos valores se pintan
     * en vez de dejarlos en blanco con guiones.
     */
    private function dibujarFilaMaquinaSoloAcumulado(
        array $fechas, string $nombreMaq, array $dataMaq, float $x, float $y,
        float $wMaq, float $wPres, float $wFecha, float $wAcum, float $wProm, float $wTP, float $wMerma
    ): void {
        $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
        $this->setFillColor($this->cMaq);
        $this->setTextColor($this->cText);
        $this->pdf->SetXY($x, $y-5);
        $this->pdf->Cell($wMaq, $this->rowH, $nombreMaq, $this->border(), 0, 'C', true);

        $cx = $x + $wMaq;
        $this->setFillColor($this->cWhite);
        $this->setTextColor([187, 187, 187]);
        $this->pdf->SetFont($this->font, '', $this->fsSmall);
        $this->pdf->SetXY($cx, $y-5);
        $this->pdf->Cell($wPres - 5, $this->rowH, '—', $this->border(), 0, 'C', true);
        $cx += $wPres;

        // Sin movimiento diario reciente: los 7 días quedan en guion
        foreach ($fechas as $f) {
            $this->pdf->SetXY($cx - 5, $y-5);
            $this->pdf->Cell($wFecha - 5, $this->rowH, '—', $this->border(), 0, 'C', true);
            $cx += $wFecha - 5;
        }

        // Indicadores reales (Acum, Prom, HRS T, %TP Día/Acum, %Merma Día/Acum)
        $this->setFillColor($this->cProd);
        $this->setTextColor($this->cText);
        $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
        foreach ([
            $this->fmtNum($dataMaq['total_maquina']['acum'] ?? 0),
            $this->fmtNum($dataMaq['total_maquina']['prom'] ?? 0),
            $dataMaq['hrsT']      ?? '—',
            $this->fmtPct($dataMaq['tp_dia']     ?? '-'),
            $this->fmtPct($dataMaq['tp_acum']    ?? '-'),
            $this->fmtPct($dataMaq['merma_dia']  ?? '-'),
            $this->fmtPct($dataMaq['merma_acum'] ?? '-'),
        ] as $val) {
            $this->pdf->SetXY($cx - 5, $y-5);
            $this->pdf->Cell($wAcum - 1, $this->rowH, $val, $this->border(), 0, 'C', true);
            $cx += $wAcum - 1;
        }
    }

    private function dibujarFilaTotalMaq(
        array $fechas, string $nombreMaq, array $dataMaq, float $x, float $y,
        float $wMaq, float $wPres, float $wFecha, float $wAcum, float $wProm, float $wTP, float $wMerma
    ): void {
        $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
        $this->setFillColor($this->cTotalMaq);
        $this->setTextColor($this->cAccent);
        

        $this->pdf->SetXY($x, $y-5);
        $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
        $this->pdf->Cell($wMaq + $wPres - 28, $this->rowH, '  Total ' . $nombreMaq, $this->border(), 0, 'L', true);

        $cx = $x + $wMaq + $wPres;
        $this->setTextColor($this->cText);

        foreach ($fechas as $f) {
            $v = $dataMaq['total_maquina']['dias'][$f] ?? 0;
            $txt = $v > 0 ? $this->fmtNum($v) : '—';
            $this->setTextColor($v > 0 ? $this->cText : [187, 187, 187]);
            $this->pdf->SetXY($cx-28, $y-5);
            $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
            $this->pdf->Cell($wFecha - 4, $this->rowH, $txt, $this->border(), 0, 'C', true);
            $cx += $wFecha - 4;
        }

        $this->setTextColor($this->cText);
        $this->pdf->SetXY($cx-28, $y-5);
        $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
        $this->pdf->Cell($wAcum + 41, $this->rowH, '', $this->border(), 0, 'C', true);
        // foreach ([$wAcum, $wAcum, $wAcum, $wAcum, $wAcum, $wAcum] as $wC) {
            
            
        //     $cx += $wC;
        // }
    }

    private function dibujarFilaTotalOp(
        array $fechas, string $categoria, array $totalOp, float $x, float $y,
        float $wMaq, float $wPres, float $wFecha, float $wAcum, float $wProm, float $wTP, float $wMerma
    ): void {
        $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
        $this->setFillColor($this->cHeader1);
        $this->setTextColor($this->cWhite);
        $this->pdf->SetDrawColor(159, 186, 197); // Color RGB

        $this->pdf->SetXY($x, $y-5);
        $this->pdf->Cell($wMaq + $wPres - 28, $this->rowH, '  Total ' . $categoria, $this->border(), 0, 'L', true);

        $cx = $x + $wMaq + $wPres;

        foreach ($fechas as $f) {
            $v = $totalOp['dias'][$f] ?? 0;
            $txt = $v > 0 ? $this->fmtNum($v) : '—';
            $this->setTextColor($v > 0 ? $this->cWhite : [159, 186, 197]);
            $this->pdf->SetXY($cx -28, $y-5);
            $this->pdf->Cell($wFecha-4, $this->rowH, $txt, $this->border('T2'), 0, 'C', true);
            $cx += $wFecha-4;
        }

        $this->setTextColor($this->cWhite);
        $this->pdf->SetXY($cx-28, $y-5);
        $this->pdf->Cell($wAcum-1, $this->rowH, $this->fmtNum($totalOp['acum']), $this->border(), 0, 'C', true);
        $cx += $wAcum;
        $this->pdf->SetXY($cx - 29, $y-5);
        $this->pdf->Cell($wProm - 1, $this->rowH, $this->fmtNum($totalOp['prom']), $this->border(), 0, 'C', true);
        $cx += $wProm;

        foreach ([
            $totalOp['hrsT']                        ?? '—',
            $this->fmtPct($totalOp['tp_dia']     ?? '-'),
            $this->fmtPct($totalOp['tp_acum']    ?? '-'),
            $this->fmtPct($totalOp['merma_dia']  ?? '-'),
            $this->fmtPct($totalOp['merma_acum'] ?? '-'),
        ] as $val) {
            $this->pdf->SetXY($cx - 30   , $y-5);
            $this->pdf->Cell($wAcum - 1, $this->rowH, $val, $this->border(), 0, 'C', true);
            $cx += $wAcum - 1;
        }
    }

    // ══════════════════════════════════════════════════════════════
    // TABLA PROGRAMA
    // ══════════════════════════════════════════════════════════════

    private function dibujarPrograma(array $programa, float $x, float $y, float $yMax, int $decimales = 0, ?float $wOverride = null): array
    {
        $w       = $wOverride ?? $this->colRightW;
        $wTipo   = round($w * 0.50, 2);
        $wProd   = round($w * 0.16, 2);
        $wProg   = round($w * 0.16, 2);
        $wAva    = round($w - $wTipo - $wProd - $wProg, 2);

        // Página donde inició este departamento — el programa siempre dibuja en col derecha
        $paginaDepto = $this->pdf->getPage();

        // Helper: verificar espacio en col derecha y saltar si es necesario
        // Al saltar, vamos a una página NUEVA pero posicionamos en col derecha
        $saltarPaginaPrograma = function() use (&$y, &$x, $yMax, $paginaDepto) {
            // Insertar página nueva después de la página actual del programa
            $this->pdf->AddPage();
            $this->dibujarHeader($this->fechaReporte, $this->logoPathReporte);
            $y = $this->marginT;
            // x sigue siendo la columna derecha
        };

        // Nota día
        $this->pdf->SetFont($this->font, '', 5.0);
        $this->setTextColor([119, 119, 119]);
        $this->pdf->SetXY($x, $y);
        $this->pdf->Cell($w, 4, 'Día ' . $programa['diaActual'] . ' de ' . $programa['diasMes'] . '  ·  % esperado: ' . $programa['esperadoPct'] . '%', 0, 0, 'L');
        $y += 4.5;

        // Leyenda
        $y = $this->dibujarLeyenda($x, $y, $w);

        // Encabezado tabla programa
        $this->pdf->SetFont($this->font, 'B', $this->fsTh);
        $this->setFillColor($this->cHeader1);
        $this->setTextColor($this->cWhite);
        $cx = $x;
        foreach ([[$wTipo, 'TIPO'], [$wProd, 'ACUM'], [$wProg, 'PROG'], [$wAva, '% AVANCE']] as [$wC, $lbl]) {
            $this->pdf->SetXY($cx, $y);
            $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
            $this->pdf->Cell($wC, $this->rowHThProg, $lbl, $this->border(), 0, 'C', true);
            $cx += $wC;
        }
        $y += $this->rowHThProg;

        $esperadoPct = (float)$programa['esperadoPct'];
        $primeraCat  = true;

        foreach ($programa['grupos'] as $idCat => $cat) {
            if (!is_array($cat) || !isset($cat['_prods'])) continue;

            if (!$primeraCat) $y += 0.8;
            $primeraCat = false;

            $filaIdx = 0;

            foreach ($cat['_prods'] as $nombreProd => $etapas) {
                if (!is_array($etapas)) continue;

                $prodUstd = 0; $prodPlan = 0;
                foreach ($etapas as $info) {
                    if (!is_array($info)) continue;
                    $prodUstd += (float)($info['ustd'] ?? 0);
                    $prodPlan += (float)($info['plan'] ?? 0);
                }
                $prodAvance = $prodPlan > 0 ? round(($prodUstd / $prodPlan) * 100, 1) : 0;
                $prodColor  = $this->calcularColor($prodAvance, $esperadoPct);

                // Saltar si no cabe fila producto
                if ($y + $this->rowHProg > $yMax) $saltarPaginaPrograma();

                $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
                $this->setFillColor($this->cProd);
                $this->setTextColor($this->cAccent);
                $this->pdf->SetXY($x, $y);
                $this->pdf->Cell($w, $this->rowHProg, '  ' . $nombreProd, $this->border(), 0, 'L', true);
                $y += $this->rowHProg;

                foreach ($etapas as $etapa => $info) {
                    if (!is_array($info)) continue;

                    if ($y + $this->rowHProg > $yMax) $saltarPaginaPrograma();

                    $bg = $filaIdx % 2 === 0 ? $this->cWhite : $this->cAlt;
                    $filaIdx++;

                    $this->pdf->SetFont($this->font, '', $this->fsSmall);
                    $this->setFillColor($bg);
                    $this->setTextColor($this->cText);
                    $cx = $x;
                    $this->pdf->SetXY($cx, $y);
                    $this->pdf->Cell($wTipo, $this->rowHProg, '    ' . $info['etapa'], $this->border(), 0, 'L', true);
                    $cx += $wTipo;
                    $this->pdf->SetXY($cx, $y);
                    $this->pdf->Cell($wProd, $this->rowHProg, $this->fmtDec((float)$info['ustd'], $decimales), $this->border(), 0, 'C', true);
                    $cx += $wProd;
                    $this->pdf->SetXY($cx, $y);
                    $this->pdf->Cell($wProg, $this->rowHProg, $this->fmtDec((float)$info['plan'], $decimales), $this->border(), 0, 'C', true);
                    $cx += $wProg;
                    $this->dibujarCeldaAvance($cx, $y, $wAva, $info['avancePct'] . '%', $info['color']);
                    $y += $this->rowHProg;
                }

                // Total producto
                $countEtapas = count(array_filter($etapas, 'is_array'));
                if ($countEtapas > 1) {
                    if ($y + $this->rowHProg > $yMax) $saltarPaginaPrograma();
                    $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
                    $this->setFillColor($this->cTotalProd);
                    $this->setTextColor($this->cAccent);
                    $cx = $x;
                    $this->pdf->SetXY($cx, $y);
                    $this->pdf->Cell($wTipo, $this->rowHProg, 'Total ' . $nombreProd, $this->border(), 0, 'L', true);
                    $cx += $wTipo;
                    $this->pdf->SetXY($cx, $y);
                    $this->pdf->Cell($wProd, $this->rowHProg, $this->fmtDec($prodUstd, $decimales), $this->border(), 0, 'C', true);
                    $cx += $wProd;
                    $this->pdf->SetXY($cx, $y);
                    $this->pdf->Cell($wProg, $this->rowHProg, $this->fmtDec($prodPlan, $decimales), $this->border(), 0, 'C', true);
                    $cx += $wProg;
                    $this->dibujarCeldaAvance($cx, $y, $wAva, $prodAvance . '%', $prodColor, true);
                    $y += $this->rowHProg;
                }
            }

            // Total categoría
            $total = $cat['_total'];
            if ($y + $this->rowHProg > $yMax) $saltarPaginaPrograma();
            $nombreCat = strtoupper($cat['_nombre'] ?? 'GENERAL');
            $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
            $this->setFillColor($this->cTotalMaq);
            $this->setTextColor($this->cAccent);
            $cx = $x;
            $this->pdf->SetXY($cx, $y);
            $this->pdf->Cell($wTipo, $this->rowHProg, 'TOTAL ' . $nombreCat, $this->border(), 0, 'L', true);
            $cx += $wTipo;
            $this->setTextColor($this->cText);
            $this->pdf->SetXY($cx, $y);
            $this->pdf->Cell($wProd, $this->rowHProg, $this->fmtDec((float)$total['ustd'], $decimales), $this->border(), 0, 'C', true);
            $cx += $wProd;
            $this->pdf->SetXY($cx, $y);
            $this->pdf->Cell($wProg, $this->rowHProg, $this->fmtDec((float)$total['plan'], $decimales), $this->border(), 0, 'C', true);
            $cx += $wProg;
            $this->dibujarCeldaAvance($cx, $y, $wAva, $total['avancePct'] . '%', $total['color'], true);
            $y += $this->rowHProg;
        }

        // Total general
        $y += 0.8;
        $tg = $programa['totalGeneral'];
        $nombreDepto = strtoupper($programa['nombreDepto'] ?? 'GENERAL');
        if ($y + $this->rowHProg > $yMax) $saltarPaginaPrograma();
        $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
        $this->setFillColor($this->cHeader1);
        $this->setTextColor($this->cWhite);
        $cx = $x;
        $this->pdf->SetXY($cx, $y);
        $this->pdf->Cell($wTipo, $this->rowHProg, '  TOTAL ' . $nombreDepto, $this->border(), 0, 'L', true);
        $cx += $wTipo;
        $this->pdf->SetXY($cx, $y);
        $this->pdf->Cell($wProd, $this->rowHProg, $this->fmtDec((float)$tg['ustd'], $decimales), $this->border(), 0, 'C', true);
        $cx += $wProd;
        $this->pdf->SetXY($cx, $y);
        $this->pdf->Cell($wProg, $this->rowHProg, $this->fmtDec((float)$tg['plan'], $decimales), $this->border(), 0, 'C', true);
        $cx += $wProg;
        $this->dibujarCeldaAvance($cx, $y, $wAva, $tg['avancePct'] . '%', $tg['color'], true);

        return [$y + $this->rowHProg, $this->pdf->getPage()];
    }

    private function fmtDec(float $val, int $dec): string
    {
        if ($val === 0.0) return $dec > 0 ? number_format(0, $dec, '.', ',') : '—';
        return number_format($val, $dec, '.', ',');
    }

    private function fmtPct(string $valor): string
    {
        // fmtPct ya NO decide nada: cada función calcularTP.../calcularMerma...
        // es la que decide si el valor es '-' (sin producción/sin datos) o un
        // porcentaje real (incluyendo 0.00% cuando sí hubo producción).
        if ($valor === '-' || $valor === '—') return '-';
        return $valor;
    }

    private function dibujarLeyenda(float $x, float $y, float $w): float
    {
        $items = [
            [$this->cMorado,   'Por encima'],
            [$this->cVerde,    'En meta'],
            [$this->cAmarillo, 'Liger. bajo'],
            [$this->cRojo,     'Muy bajo'],
        ];

        $this->pdf->SetFont($this->font, '', 4.5);
        $xc = $x;
        $wItem = $w / 4;
        foreach ($items as [$color, $lbl]) {
            // cuadrito de color
            $this->setFillColor($color);
            $this->pdf->Rect($xc, $y, 2.5, 2.5, 'F');
            $xTxt = $xc + 3.2;
            $this->setTextColor([85, 85, 85]);
            $this->pdf->SetXY($xTxt, $y);
            $this->pdf->Cell($wItem - 3.2, 4, $lbl, 0, 0, 'L');
            $xc += $wItem;
        }
        return $y + 5;
    }

    private function dibujarCeldaAvance(float $x, float $y, float $w, string $txt, string $color, bool $bold = false): void
    {
        $bg = match($color) {
            'morado'   => $this->cMorado,
            'verde'    => $this->cVerde,
            'amarillo' => $this->cAmarillo,
            default    => $this->cRojo,
        };
        $this->setFillColor($bg);
        $this->setTextColor($this->cWhite);
        $this->pdf->SetFont($this->font, $bold ? 'B' : '', $this->fsSmall);
        $this->pdf->SetXY($x, $y);
        $this->pdf->Cell($w, $this->rowHProg, $txt, $this->border(), 0, 'C', true);
    }

    // ══════════════════════════════════════════════════════════════
    // TABBI
    // ══════════════════════════════════════════════════════════════

    public function agregarTabbi(array $fechas, array $tablas, array $programa = []): void
    {
        // Página horizontal — producción (izq) + programa (der) en la misma hoja
        $this->pdf->AddPage('L', 'LETTER');
        $this->dibujarHeader($this->fechaReporte, $this->logoPathReporte);

        $xLeft    = $this->marginL;
        $xRight   = $this->marginL + $this->colLeftW + $this->colSepW;
        $yStart   = $this->marginT;
        $yMax     = $this->pageH - $this->marginB;  // Letter landscape para el programa
        $yMaxProd = $this->pageH - $this->marginB;

        // Título departamento
        $this->pdf->SetFont($this->font, 'B', $this->fsDepto);
        $this->setTextColor($this->cHeader1);
        $this->pdf->SetXY($xLeft, $yStart-10);
        $this->pdf->Cell(263, 6, 'TELAS NO TEJIDAS', 0, 0, 'C');
        $this->setDrawColor($this->cHeader1);
        $this->pdf->SetLineWidth(0.5);
        $tW = $this->pdf->GetStringWidth('TELAS NO TEJIDAS') + 2;
        $this->pdf->SetLineWidth(0.2);

        $yContent = $yStart-5;

        // ── Dibujar programa primero (columna derecha) ──
        $paginaInicioDepto = $this->pdf->getPage();

        if (!empty($programa)) {
            $this->dibujarPrograma($programa, $xRight, $yContent, $yMax, 3);
        }
        $paginaTrasProg = $this->pdf->getPage();

        // ── Volver a la página inicial para dibujar producción (columna izquierda) ──
        $this->pdf->setPage($paginaInicioDepto);
        $pagina = $paginaInicioDepto;
        $y      = $yContent;

        foreach ($tablas as $categoria => $maquinas) {
            $y = $this->dibujarTablaTabbi($fechas, $categoria, $maquinas, $xLeft, $y, $yMaxProd, $pagina);
            $pagina = $this->pdf->getPage();
            $y += 1;
        }

        // ── Dejar puntero en la última página usada ──
        $ultimaPagina = max($this->pdf->getPage(), $paginaTrasProg);
        $this->pdf->setPage($ultimaPagina);
    }

    /**
     * agregarHook()
     *
     * Dibuja Hook en su propia hoja (a ancho completo, sin columna de
     * programa al lado), porque hoy solo tiene una clave pero puede crecer
     * a 2 o más — así no compite por espacio con TABBI/Spooler.
     */
    // public function agregarHook(array $fechas, array $tablas): void
    // {
    //     $this->pdf->AddPage('L', 'LETTER');
    //     $this->dibujarHeader($this->fechaReporte, $this->logoPathReporte);

    //     $xLeft    = $this->marginL;
    //     $yStart   = $this->marginT;
    //     $yMaxProd = $this->pageH - $this->marginB;
    //     $wTotal   = $this->usableW;

    //     // Título de la hoja
    //     $this->pdf->SetFont($this->font, 'B', $this->fsDepto);
    //     $this->setTextColor($this->cHeader1);
    //     $this->pdf->SetXY($xLeft, $yStart-10);
    //     $this->pdf->Cell(263, 6, 'HOOK', 0, 0, 'C');

    //     $yContent = $yStart + 9;
    //     $pagina   = $this->pdf->getPage();
    //     $y        = $yContent - 15;

    //     foreach ($tablas as $categoria => $maquinas) {
    //         $y = $this->dibujarTablaTabbi($fechas, $categoria, $maquinas, $xLeft+200, $y, $yMaxProd, $pagina, $wTotal);
    //         $pagina = $this->pdf->getPage();
    //         $y += 1;
    //     }
    // }

    private function dibujarTablaTabbi(
        array $fechas, string $categoria, array $maquinas,
        float $x, float $y, float $yMax, int $pagina,
        ?float $wTotal = null
    ): float {
        // Por defecto ocupa la columna izquierda (66%), como en TABBI/Spooler.
        // Se puede pasar un ancho distinto (ej. usableW completo) para hojas
        // que no llevan columna de programa al lado, como la de Hook.
        $wTotal = $wTotal ?? $this->colLeftW;

        $nFechas  = count($fechas);
        $wMaq     = round($wTotal * 0.06, 2);
        $wPres    = round($wTotal * 0.22, 2);
        $wFecha   = round(($wTotal * 0.40) / max($nFechas, 1), 2);
        $wAcum    = round($wTotal * 0.06, 2);
        $wProm    = $wAcum;
        $wTP      = $wAcum;
        $wMerma   = $wAcum;

        // Ajustar wFecha si es necesario
        $totalFijo = $wMaq + $wPres + $wAcum + $wProm + ($wTP * 2) + ($wMerma * 2);
        $disponible = $wTotal - $totalFijo;
        if ($disponible < $wFecha * $nFechas) {
            $wFecha = round($disponible / max($nFechas, 1), 2);
        }

        // Título operación
        // Mismo criterio que dibujarTablaUSTD: si la tabla completa no cabe
        // en el espacio restante, saltar de página entera para no dejar el
        // título huérfano arriba y el resto en la página siguiente.
        $alturaTablaTabbi  = 7 + $this->estimarAlturaTabla($maquinas);
        $espacioRestanteTb = $yMax - $y;
        $alturaPaginaUtilTb = $yMax - $this->marginT;

        if ($alturaTablaTabbi > $espacioRestanteTb) {
            if ($alturaTablaTabbi <= $alturaPaginaUtilTb || $y > $this->marginT + 5) {
                $this->pdf->AddPage('L', 'LETTER'); $pagina++;
                $this->dibujarHeader($this->fechaReporte, $this->logoPathReporte);
                // No hay título de departamento en esta página nueva: subir
                // el cursor para aprovechar ese espacio.
                $y = $this->marginT - 3;
            }
        } elseif ($y + 5 > $yMax) {
            $this->pdf->AddPage('L', 'LETTER'); $pagina++;
            $this->dibujarHeader($this->fechaReporte, $this->logoPathReporte);
            $y = $this->marginT - 3;
        }
        $this->pdf->SetFont($this->font, 'B', $this->fsTitle);
        $this->setTextColor($this->cHeader1);
        $this->pdf->SetXY($x, $y);
        $this->pdf->Cell($wTotal, 5, $categoria, 0, 0, 'L');
        $this->setDrawColor($this->cHeader1);
        $this->pdf->SetLineWidth(0.4);
        $tW = $this->pdf->GetStringWidth($categoria) + 2;
        // $this->pdf->Line($x, $y + 5.5, $x + $tW, $y + 5.5);
        $this->pdf->SetLineWidth(0.2);
        $y += 5;

        // Encabezados
        [$y, $pagina] = $this->verificarEspacio($y, $this->rowHTh * 2, $yMax, $pagina, $x);
        $this->pdf->SetFont($this->font, 'B', $this->fsTh);

        // Fila 1
        $this->setFillColor($this->cHeader1);
        $this->setTextColor($this->cWhite);
        $cx = $x;
        $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
        $this->pdf->SetXY($cx, $y);
        $this->pdf->Cell($wMaq+3, $this->rowHTh, 'Máquina', $this->border(), 0, 'C', true); $cx += $wMaq;
        $this->pdf->SetXY($cx+3, $y);
        $this->pdf->Cell($wPres-2.5, $this->rowHTh, 'Presentación', $this->border(), 0, 'C', true); $cx += $wPres;
        $wFechasTotal = $wFecha * $nFechas;
        $this->pdf->SetXY($cx+0.5, $y);
        $this->pdf->Cell($wFechasTotal+15, $this->rowHTh, 'MM² Días Anteriores', $this->border(), 0, 'C', true); $cx += $wFechasTotal;
        $this->pdf->SetXY($cx+15.5, $y);
        $this->pdf->Cell($wAcum + $wProm+3.1, $this->rowHTh, 'MM²', $this->border(), 0, 'C', true); $cx += $wAcum + $wProm;
        $this->pdf->SetXY($cx+18.6, $y);
        $this->pdf->Cell($wAcum + $wProm + 10.1, $this->rowHTh, '% TP', $this->border(), 0, 'C', true); $cx += $wTP * 3;
        $this->pdf->SetXY($cx+23.2, $y);
        $this->pdf->Cell($wMerma + 8.65, $this->rowHTh, '% Merma', $this->border(), 0, 'C', true);
        $y += $this->rowHTh;

        // Fila 2
        $this->setFillColor($this->cHeader2);
        $this->pdf->SetXY($x, $y);
        $this->pdf->Cell($wMaq + $wPres+0.5, $this->rowHTh, '', $this->border(), 0, 'C', true);

        $cx = $x + $wMaq + $wPres;
        foreach ($fechas as $f) {
            $this->pdf->SetXY($cx+0.5, $y);
            $this->pdf->Cell($wFecha+2.14, $this->rowHTh, $this->formatFechaDia($f), $this->border(), 0, 'C', true);
            $cx += $wFecha+2.14;
        }

        foreach ([[$wAcum,'Acum'],[$wProm,'Prom'],[$wTP,'HRS T'],[$wTP,'Día'],[$wTP,'Acum'],[$wMerma,'Día'],[$wMerma,'Acum']] as [$wC,$lbl]) {
            $this->pdf->SetXY($cx+0.5, $y);
            $this->pdf->Cell($wC+1.55, $this->rowHTh, $lbl, $this->border(), 0, 'C', true);
            $cx += $wC+1.55;
        }
        $y += $this->rowHTh;

        // Filas de máquinas
        $totalOp = $maquinas['_total_operacion'] ?? null;
        unset($maquinas['_total_operacion']);

        foreach ($maquinas as $nombreMaq => $dataMaq) {
            // Ignorar entradas espurias de la BD (SPOOLER3 sin espacio = duplicado de SPOOLER 3)
            if ($nombreMaq === 'SPOOLER3') continue;

            $sinDatos = empty($dataMaq['productos']);
            $tieneAcumuladoTabbi = (
                (float)($dataMaq['total_maquina']['acum'] ?? 0) > 0
                || $this->fmtPct($dataMaq['tp_acum']    ?? '-') !== '-'
                || $this->fmtPct($dataMaq['merma_acum'] ?? '-') !== '-'
            );

            if ($sinDatos && !$tieneAcumuladoTabbi) {
                [$y, $pagina] = $this->verificarEspacio($y, $this->rowH, $yMax, $pagina, $x);
                $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
                $this->setFillColor($this->cMaq);
                $this->setTextColor($this->cText);
                $this->pdf->SetXY($x, $y);
                $this->pdf->Cell($wMaq, $this->rowH, $nombreMaq, $this->border(), 0, 'C', true);
                $cx = $x + $wMaq;
                $this->setFillColor($this->cWhite);
                $this->setTextColor([187,187,187]);
                $this->pdf->SetXY($cx, $y);
                $this->pdf->Cell($wPres, $this->rowH, '—', $this->border(), 0, 'C', true);
                $cx += $wPres;
                foreach ($fechas as $f) {
                    $this->pdf->SetXY($cx+0.5, $y);
                    $this->pdf->Cell($wFecha+2.14, $this->rowH, '—', $this->border(), 0, 'C', true);
                    $cx += $wFecha+2.14;
                }
                foreach ([$wAcum,$wProm,$wTP,$wTP,$wTP,$wMerma,$wMerma] as $wC) {
                    $this->pdf->SetXY($cx, $y);
                    $this->pdf->Cell($wC, $this->rowH, '', $this->border(), 0, 'C', true);
                    $cx += $wC;
                }
                $y += $this->rowH;
                continue;
            } elseif ($sinDatos && $tieneAcumuladoTabbi) {
                // Sin movimiento en los últimos 7 días, pero con acumulado/indicadores del mes
                [$y, $pagina] = $this->verificarEspacio($y, $this->rowH, $yMax, $pagina, $x);
                $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
                $this->setFillColor($this->cMaq);
                $this->setTextColor($this->cText);
                $this->pdf->SetXY($x, $y);
                $this->pdf->Cell($wMaq, $this->rowH, $nombreMaq, $this->border(), 0, 'C', true);
                $cx = $x + $wMaq;
                $this->setFillColor($this->cWhite);
                $this->setTextColor([187,187,187]);
                $this->pdf->SetXY($cx, $y);
                $this->pdf->Cell($wPres, $this->rowH, '—', $this->border(), 0, 'C', true);
                $cx += $wPres;
                foreach ($fechas as $f) {
                    $this->pdf->SetXY($cx+0.5, $y);
                    $this->pdf->Cell($wFecha+2.14, $this->rowH, '—', $this->border(), 0, 'C', true);
                    $cx += $wFecha+2.14;
                }
                $this->setFillColor($this->cProd);
                $this->setTextColor($this->cText);
                $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
                foreach ([
                    [$wAcum, number_format((float)($dataMaq['total_maquina']['acum'] ?? 0), 3, '.', ',')],
                    [$wProm, number_format((float)($dataMaq['total_maquina']['prom'] ?? 0), 3, '.', ',')],
                    [$wTP,   $dataMaq['tp_day_hrs'] ?? '—'],
                    [$wTP,   $this->fmtPct($dataMaq['tp_dia']     ?? '-')],
                    [$wTP,   $this->fmtPct($dataMaq['tp_acum']    ?? '-')],
                    [$wMerma, $this->fmtPct($dataMaq['merma_dia']  ?? '-')],
                    [$wMerma, $this->fmtPct($dataMaq['merma_acum'] ?? '-')],
                ] as [$wC, $val]) {
                    $this->pdf->SetXY($cx, $y);
                    $this->pdf->Cell($wC, $this->rowH, $val, $this->border(), 0, 'C', true);
                    $cx += $wC;
                }
                $y += $this->rowH;
                continue;
            }

            // Calcular altura total de esta máquina para decidir si cabe
            // v2: 1 fila por producto (ya no hay fila header + filas de clave)
            $totalFilasTabbi      = count($dataMaq['productos']);
            $alturaNecesariaTabbi = ($totalFilasTabbi + 1) * $this->rowH; // +1 para Total máquina
            $espacioRestanteTabbi = $yMax - $y;
            if ($alturaNecesariaTabbi > $espacioRestanteTabbi && $y > $this->marginT + 20) {
                $this->pdf->AddPage('L', 'LETTER');
                $this->dibujarHeader($this->fechaReporte, $this->logoPathReporte);
                $pagina++;
                $y = $this->marginT;
                $this->pdf->setPage($pagina);
            }

            $yMaqStart  = $y;
            $primerFila = true;
            $filaIdx    = 0;

            // v2: una sola fila por producto (ya no hay fila header + filas
            // de clave; el producto trae directamente sus totales por día).
            foreach ($dataMaq['productos'] as $nombreProd => $producto) {
                $paginaAntesProdTabbi = $pagina;
                [$y, $pagina] = $this->verificarEspacio($y, $this->rowH, $yMax, $pagina, $x);

                if ($primerFila) {
                    $yMaqStart  = $y;
                    $primerFila = false;
                } elseif ($pagina !== $paginaAntesProdTabbi) {
                    $alturaParcialTabbi = $yMax - $yMaqStart;
                    if ($alturaParcialTabbi > 0) {
                        $this->pdf->setPage($paginaAntesProdTabbi);
                        $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
                        $this->setFillColor($this->cMaq);
                        $this->setTextColor($this->cText);
                        $this->pdf->SetXY($x, $yMaqStart);
                        $this->pdf->Cell($wMaq, $alturaParcialTabbi, $nombreMaq, $this->border(), 0, 'C', true);
                        $this->pdf->setPage($pagina);
                    }
                    $yMaqStart = $y;
                }

                $bg = $filaIdx % 2 === 0 ? $this->cWhite : $this->cAlt;
                $filaIdx++;

                $cx = $x + $wMaq;
                $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
                $this->setFillColor($bg);
                $this->setTextColor($this->cAccent);
                $this->pdf->SetXY($cx+2, $y);
                $this->pdf->Cell($wPres, $this->rowH, '  ' . $nombreProd, $this->border(), 0, 'L', true);
                $cx += $wPres;

                foreach ($fechas as $f) {
                    $v   = $producto['dias'][$f] ?? 0;
                    $txt = $v > 0 ? number_format((float)$v, 3, '.', ',') : '—';
                    $this->setTextColor($v > 0 ? $this->cText : [187,187,187]);
                    $this->pdf->SetFont($this->font, '', $this->fsSmall);
                    $this->pdf->SetXY($cx+0.5, $y);
                    $this->pdf->Cell($wFecha+2.14, $this->rowH, $txt, $this->border(), 0, 'C', true);
                    $cx += $wFecha + 2.14;
                }
                $y += $this->rowH;
            }

            // Celda máquina con altura total + indicadores centrados
            $alturaMaq = $y - $yMaqStart;
            $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
            $this->setFillColor($this->cMaq);
            $this->setTextColor($this->cText);
            $this->pdf->SetXY($x, $yMaqStart);
            $this->pdf->Cell($wMaq + 3, $alturaMaq, $nombreMaq, $this->border(), 0, 'C', true);

            // Indicadores centrados verticalmente
            $yCentrado = $yMaqStart + ($alturaMaq - $this->rowH) / 2;
            $cx2 = $x + $wMaq + $wPres + ($wFecha * $nFechas);
            foreach ([
                [$wAcum, number_format((float)($dataMaq['total_maquina']['acum'] ?? 0), 3, '.', ',')],
                [$wProm, number_format((float)($dataMaq['total_maquina']['prom'] ?? 0), 3, '.', ',')],
                [$wTP,   $dataMaq['tp_day_hrs'] ?? '—'],
                [$wTP,   $this->fmtPct($dataMaq['tp_dia'])],
                [$wTP,   $this->fmtPct($dataMaq['tp_acum'])],
                [$wMerma, $this->fmtPct($dataMaq['merma_dia'])],
                [$wMerma, $this->fmtPct($dataMaq['merma_acum'])],
            ] as [$wC, $val]) {
                $this->setFillColor($this->cProd);
                $this->setTextColor($this->cText);
                $this->pdf->SetXY($cx2+15.5, $yMaqStart);
                $this->pdf->Cell($wC+1.55, $alturaMaq, '', $this->border(), 0, 'C', true);
                $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
                $this->pdf->SetXY($cx2+15.5, $yCentrado);
                $this->pdf->Cell($wC+1.55, $this->rowH, $val, 0, 0, 'C');
                $cx2 += $wC+1.55;
            }

            // Total máquina
            [$y, $pagina] = $this->verificarEspacio($y, $this->rowH, $yMax, $pagina, $x);
            $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
            $this->setFillColor($this->cTotalMaq);
            $this->setTextColor($this->cAccent);
            $this->pdf->SetXY($x, $y);
            $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
            $this->pdf->Cell($wMaq + $wPres+0.5, $this->rowH, '  Total ' . $nombreMaq, $this->border(''), 0, 'L', true);
            $cx = $x + $wMaq + $wPres;
            $this->setTextColor($this->cText);
            foreach ($fechas as $f) {
                $v   = $dataMaq['total_maquina']['dias'][$f] ?? 0;
                $txt = $v > 0 ? number_format((float)$v, 3, '.', ',') : '—';
                $this->setTextColor($v > 0 ? $this->cText : [187,187,187]);
                $this->pdf->SetXY($cx+0.5, $y);
                $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
                $this->pdf->Cell($wFecha+2.14, $this->rowH, $txt, $this->border(''), 0, 'C', true);
                $cx += $wFecha+2.14;
            }

            $this->setTextColor($this->cText);
            $this->pdf->SetXY($cx+0.5, $y);
            $this->pdf->SetDrawColor(159, 186, 197); // Color RGB
            $this->pdf->Cell($wAcum + $wProm + 38.4, $this->rowH, '', $this->border(), 0, 'C', true);

            $y += $this->rowH;
        }

        // Total operación
        if ($totalOp) {
            [$y, $pagina] = $this->verificarEspacio($y, $this->rowH, $yMax, $pagina, $x);
            $this->pdf->SetFont($this->font, 'B', $this->fsSmall);
            $this->setFillColor($this->cHeader1);
            $this->setTextColor($this->cWhite);
            $this->pdf->SetXY($x, $y);
            $this->pdf->Cell($wMaq + $wPres+0.5, $this->rowH, '  Total ' . $categoria, $this->border(''), 0, 'L', true);
            $cx = $x + $wMaq + $wPres;
            foreach ($fechas as $f) {
                $v   = $totalOp['dias'][$f] ?? 0;
                $txt = $v > 0 ? number_format((float)$v, 3, '.', ',') : '—';
                $this->setTextColor($v > 0 ? $this->cWhite : [159,186,197]);
                $this->pdf->SetXY($cx+0.5, $y);
                $this->pdf->Cell($wFecha+2.14, $this->rowH, $txt, $this->border(''), 0, 'C', true);
                $cx += $wFecha+2.14;
            }
            $this->setTextColor($this->cWhite);
            $this->pdf->SetXY($cx+0.5, $y);
            $this->pdf->Cell($wAcum+1.7, $this->rowH, number_format((float)$totalOp['acum'], 3, '.', ','), $this->border(''), 0, 'C', true);
            $cx += $wAcum;
            $this->pdf->SetXY($cx+2.2, $y);
            $this->pdf->Cell($wProm+1.5, $this->rowH, number_format((float)$totalOp['prom'], 3, '.', ','), $this->border(''), 0, 'C', true);
            $cx += $wProm;
            foreach ([
                [$wTP, $totalOp['tp_day_hrs']                        ?? '-'],
                [$wTP, $this->fmtPct($totalOp['tp_dia']     ?? '-')],
                [$wTP, $this->fmtPct($totalOp['tp_acum']    ?? '-')],
                [$wMerma, $this->fmtPct($totalOp['merma_dia']  ?? '-')],
                [$wMerma, $this->fmtPct($totalOp['merma_acum'] ?? '-')],
            ] as [$wC, $val]) {
                $this->pdf->SetXY($cx+3.6, $y);
                $this->pdf->Cell($wC+1.55, $this->rowH, $val, $this->border(''), 0, 'C', true);
                $cx += $wC+1.55;
            }
            $y += $this->rowH;
        }

        return $y;
    }

    // ══════════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════════

    
    private function estimarAlturaTabla(array $maquinas): float
    {
        $filas = 2; // encabezados

        foreach ($maquinas as $maq) {
            if ($maq === null || $maq === [] || !is_array($maq)) continue;

            if (isset($maq['productos'])) {
                // v2: 1 fila por producto (ya no hay filas de clave separadas)
                $filas += count($maq['productos']);
                $filas += 1; // total máquina
            }
        }

        $filas += 1; // total operación

        return $filas * $this->rowH;
    }


    private function verificarEspacio(float $y, float $h, float $yMax, int $pagina, float $xLeft): array
    {
        if ($y + $h > $yMax) {
            // Detectar formato por el valor de yMax:
            //   Portrait Letter : yMax ≈ 279.4 - 8 = 271.4
            //   Legal landscape : yMax ≈ 215.9 - 8 = 207.9  (Producción y TNT)
            if (abs($yMax - (279.4 - $this->marginB)) < 2) {
                $this->pdf->AddPage('P', 'LETTER');
                $this->dibujarHeaderPortrait('', '');
            } else {
                // Letter landscape (producción, TNT, etc.)
                $this->pdf->AddPage('L', 'LETTER');
                $this->dibujarHeader($this->fechaReporte, $this->logoPathReporte);
            }
            $pagina++;
            $y = $this->marginT;
            $this->pdf->setPage($pagina);
        }
        return [$y, $pagina];
    }

    private function border(string $tipo = ''): string
    {
        return match($tipo) {
            'T'  => 'T',
            'T2' => 'T',
            ''   => '1',
            default => '1',
        };
    }

    private function setFillColor(array $c): void
    {
        $this->pdf->SetFillColor($c[0], $c[1], $c[2]);
    }

    private function setTextColor(array $c): void
    {
        $this->pdf->SetTextColor($c[0], $c[1], $c[2]);
    }

    private function setDrawColor(array $c): void
    {
        $this->pdf->SetDrawColor($c[0], $c[1], $c[2]);
    }

    private function fmtNum($val): string
    {
        if ($val === null || $val === 0 || $val === '') return '—';
        return number_format((float)$val, 0, '.', ',');
    }

    private function formatFecha(string $fecha): string
    {
        $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',
                  6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',
                  10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
        $ts = strtotime($fecha);
        return date('d', $ts) . ' - ' . $meses[(int)date('n', $ts)] . ' - ' . date('Y', $ts);
    }

    private function formatFechaDia(string $fecha): string
    {
        $p = explode('-', $fecha);
        return $p[2] . '/' . $p[1];
    }

    private function calcularColor(float $avancePct, float $esperadoPct): string
    {
        if ($esperadoPct <= 0) return 'rojo';
        $ratio = ($avancePct / $esperadoPct) * 100;
        if ($ratio > 100)  return 'morado';
        if ($ratio >= 90)  return 'verde';
        if ($ratio >= 70)  return 'amarillo';
        return 'rojo';
    }
}