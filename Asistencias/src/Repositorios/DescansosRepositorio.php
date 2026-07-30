<?php
namespace src\Repositorios;

class DescansosRepositorio {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    // Creacion de la conexion a la BD adjuntando una cadena vacia de parametros para la siguiente consulta
    private function query(string $sql, array $params = [], int $fetchMode = SQLSRV_FETCH_ASSOC): array {
        $stmt = sqlsrv_query($this->conn, $sql, $params);
        if ($stmt === false) throw new \RuntimeException(print_r(sqlsrv_errors(), true));
        $rows = [];
        while ($r = sqlsrv_fetch_array($stmt, $fetchMode)) $rows[] = $r;
        return $rows;
    }

     // Funcion principal para la busqueda de noemp / fecha / Dias entre rango de fechas para descansos
    public function getDescansosAgrupados(string $fechai, string $fechaf): array {
        $sql = "
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

        // Se usa el primer metodo de conexion y adjuntamos los parametros que son las fechas de inicio y de fin
        $rows = $this->query($sql, [$fechai, $fechaf]);
        
        // se agrupan los datos en array que se retorna para su posterior uso
        $grupo = [];
        foreach ($rows as $r) {
            $grupo[$r['noemp']][] = $r;
        }
        return $grupo;
    }
}
