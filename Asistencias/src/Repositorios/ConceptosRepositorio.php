<?php
namespace src\Repositorios;

class ConceptosRepositorio
{
    private $conn;

    // Condicion que define un cambio de puesto autorizado.    
    private const CP_AUTORIZADO = " AND e.terminado = 1 ";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    private function query(string $sql, array $params = [], int $fetchMode = SQLSRV_FETCH_ASSOC): array
    {
        $stmt = sqlsrv_query($this->conn, $sql, $params);
        if ($stmt === false)
            throw new \RuntimeException(print_r(sqlsrv_errors(), true));
        $rows = [];
        while ($r = sqlsrv_fetch_array($stmt, $fetchMode))
            $rows[] = $r;
        return $rows;
    }

    /**
     * Devuelve todos los conceptos del periodo agrupados por numero de empleado.
     * Cada evento lleva una clave 'fecha' para poder filtrarlo despues por semana.
     */
    public function getConceptosAgrupados(string $fechai, string $fechaf): array
    {
        $grupo = [];

        foreach ($this->tiemposExtra($fechai, $fechaf) as $ev) {
            $grupo[$ev['noemp']][] = $ev;
        }
        foreach ($this->vacaciones($fechai, $fechaf) as $ev) {
            $grupo[$ev['noemp']][] = $ev;
        }
        foreach ($this->cambiosPuesto($fechai, $fechaf) as $ev) {
            $grupo[$ev['noemp']][] = $ev;
        }

        return $grupo;
    }

    /**
     * Tiempo extra validado. La cantidad no se almacena: se calcula del lapso
     * horai -> horaf. Si el lapso cruza medianoche (turno 3) el DATEDIFF sale
     * negativo y se le suman 1440 minutos.
     */
    private function tiemposExtra(string $fechai, string $fechaf): array
    {
        $sql = "
        SELECT s.noemp,
               s.fecha,
               CASE WHEN DATEDIFF(minute, CAST(s.horai AS TIME), CAST(s.horaf AS TIME)) < 0
                    THEN DATEDIFF(minute, CAST(s.horai AS TIME), CAST(s.horaf AS TIME)) + 1440
                    ELSE DATEDIFF(minute, CAST(s.horai AS TIME), CAST(s.horaf AS TIME))
               END AS minutos
        FROM TLX003MXDB.dbo.TiempoextraSubEnc s
        INNER JOIN TLX003MXDB.dbo.TiempoextraEnc e ON e.id = s.folio
        WHERE s.validado = 1
          AND s.fecha BETWEEN ? AND ?
        ";

        $out = [];
        foreach ($this->query($sql, [$fechai, $fechaf]) as $r) {
            $fecha = ($r['fecha'] instanceof \DateTime) ? $r['fecha']->format('Y-m-d') : $r['fecha'];
            $out[] = [
                'tipo' => 'TIEMPO EXTRA',
                'noemp' => $r['noemp'],
                'fecha' => $fecha,
                'minutos' => (int) $r['minutos'],
                'dias' => 0,
                'puesto' => '',
                'ini' => $fecha,
                'fin' => $fecha
            ];
        }
        return $out;
    }

    /**
     * Vacaciones revisadas por nominas. Un evento por cada dia marcado como V.
     * ini/fin traen el rango completo de la solicitud, no solo el tramo semanal.
     */
    private function vacaciones(string $fechai, string $fechaf): array
    {
        $sql = "
        SELECT e.Vc_ibm AS noemp,
               c.Cav_fecha,
               r.ini,
               r.fin
        FROM TLX002MXDB.dbo.tblMXPRCalendarioVacaciones c
        INNER JOIN TLX002MXDB.dbo.tblMXPRVacacionesEnc e ON e.Vc_id = c.Cav_folio
        INNER JOIN (
            SELECT Cav_folio,
                   MIN(Cav_fecha) AS ini,
                   MAX(Cav_fecha) AS fin
            FROM TLX002MXDB.dbo.tblMXPRCalendarioVacaciones
            WHERE Cav_seleccionado = 1 AND Cav_tipoDia = 'V'
            GROUP BY Cav_folio
        ) r ON r.Cav_folio = c.Cav_folio
        WHERE e.Vc_revisado = 1
          AND c.Cav_seleccionado = 1
          AND c.Cav_tipoDia = 'V'
          AND c.Cav_fecha BETWEEN ? AND ?
        ";

        $out = [];
        foreach ($this->query($sql, [$fechai, $fechaf]) as $r) {
            $out[] = [
                'tipo' => 'VACACIONES',
                'noemp' => $r['noemp'],
                'fecha' => $this->fmt($r['Cav_fecha']),
                'minutos' => 0,
                'dias' => 1,
                'puesto' => '',
                'ini' => $this->fmt($r['ini']),
                'fin' => $this->fmt($r['fin'])
            ];
        }
        return $out;
    }

    /**
     * Cambios de puesto autorizados. Los dias vienen como banderas lunes..domingo
     * colgadas del lunes que guarda CambiopuestoEnc.fecha.
     */
    private function cambiosPuesto(string $fechai, string $fechaf): array
    {
        $sql = "
        SELECT s.noemp,
               e.fecha AS inicioSemana,
               s.lunes, s.martes, s.miercoles, s.jueves,
               s.viernes, s.sabado, s.domingo,
               p.nombre AS puestoTemporal
        FROM TLX003MXDB.dbo.CambiopuestoSubEnc s
        INNER JOIN TLX003MXDB.dbo.CambiopuestoEnc e ON e.id = s.folio
        LEFT JOIN TLX009MXDB.dbo.tblPuestos p ON p.id = s.puestotemporal
        WHERE e.fecha BETWEEN ? AND ?
        " . self::CP_AUTORIZADO;

        $nombresDias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        $out = [];

        foreach ($this->query($sql, [$fechai, $fechaf]) as $r) {
            $lunes = ($r['inicioSemana'] instanceof \DateTime)
                ? clone $r['inicioSemana']
                : new \DateTime($r['inicioSemana']);

            $dias = 0;
            $primero = null;
            $ultimo = null;

            foreach ($nombresDias as $i => $nombre) {
                if ((int) $r[$nombre] === 1) {
                    $dias++;
                    $fechaDia = (clone $lunes)->modify("+$i days")->format('Y-m-d');
                    if ($primero === null)
                        $primero = $fechaDia;
                    $ultimo = $fechaDia;
                }
            }

            if ($dias === 0)
                continue;

            $out[] = [
                'tipo' => 'CAMBIO DE PUESTO',
                'noemp' => $r['noemp'],
                'fecha' => $lunes->format('Y-m-d'),
                'minutos' => 0,
                'dias' => $dias,
                'puesto' => $r['puestoTemporal'] ?? '',
                'ini' => $primero,
                'fin' => $ultimo
            ];
        }
        return $out;
    }

    private function fmt($valor): string
    {
        return ($valor instanceof \DateTime) ? $valor->format('Y-m-d') : (string) $valor;
    }
}