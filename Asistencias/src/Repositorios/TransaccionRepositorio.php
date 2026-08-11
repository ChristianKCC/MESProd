<?php
namespace src\Repositorios;

class TransaccionRepositorio
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Creacion de la conexion a la BD adjuntando una cadena vacia de parametros para la siguiente consulta
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
     * Transacciones de horas por empleado, unificando dos fuentes:
     *   - acc_transaction  (control de acceso, columna event_time ya es datetime)
     *   - att_transaction  (asistencia, areas 16 y 17; fecha y hora van separadas
     *                       en att_date + att_time, hay que combinarlas)
     * La deduplicacion por hora reloj se hace despues sobre la fuente unificada.
     */
    public function getTransaccionesAgrupadas(string $fechai, string $fechaf): array
    {
        $sql = "
        WITH fuente AS (
            SELECT CAST(pin AS VARCHAR(50)) AS pin,
                   event_time
            FROM TLX001MXDB.dbo.acc_transaction
            WHERE CONVERT(date, event_time) BETWEEN ? AND ?

            UNION ALL

            SELECT CAST(pers_person_pin AS VARCHAR(50)),
                   CAST(att_date AS DATETIME) + CAST(att_time AS DATETIME)
            FROM TLX001MXDB.dbo.att_transaction
            WHERE auth_area_no IN (16,17)
              AND att_date BETWEEN ? AND ?
        ),
        cte AS (
            SELECT pin,
                   event_time,
                   ROW_NUMBER() OVER (
                       PARTITION BY pin, DATEADD(hour, DATEDIFF(hour, 0, event_time), 0)
                       ORDER BY event_time
                   ) AS rn
            FROM fuente
        )
        SELECT pin AS NoEmp,
               event_time,
               DATEPART(WEEKDAY, event_time) AS dia_semana
        FROM cte
        WHERE rn = 1
        ORDER BY pin, event_time ASC;
        ";

        $rows = $this->query($sql, [$fechai, $fechaf, $fechai, $fechaf]);

        // Se agrupan los resultados por numero de empleado
        $grupo = [];
        foreach ($rows as $r) {
            $grupo[$r['NoEmp']][] = $r;
        }
        return $grupo;
    }
}