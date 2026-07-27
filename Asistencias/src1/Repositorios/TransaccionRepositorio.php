<?php
namespace src\Repositorios;

class TransaccionRepositorio {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    // Creacion de la conexion  la BD adjuntando una cadena vacia de parametros para la siguiente consulta
    private function query(string $sql, array $params = [], int $fetchMode = SQLSRV_FETCH_ASSOC): array {
        $stmt = sqlsrv_query($this->conn, $sql, $params);
        if ($stmt === false) throw new \RuntimeException(print_r(sqlsrv_errors(), true));
        $rows = [];
        while ($r = sqlsrv_fetch_array($stmt, $fetchMode)) $rows[] = $r;
        return $rows;
    }

     // Funcion para la obtencion de transacciones de horas x empleado
    public function getTransaccionesAgrupadas(string $fechai, string $fechaf): array {
        $sql = "
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

        // Se usa el primer metodo de conexion y se adjuntan los parametros de fecha de inicio y fin
        $rows = $this->query($sql, [$fechai, $fechaf]);
        
        // Se agrupan los resultados en un array para su posterior manejo
        $grupo = [];
        foreach ($rows as $r) {
            $grupo[$r['NoEmp']][] = $r;
        }
        return $grupo;
    }
}
