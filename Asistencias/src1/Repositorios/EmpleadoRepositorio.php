<?php
namespace src\Repositorios;

class EmpleadoRepositorio {
    private $conn;
    public function __construct($conn) { $this->conn = $conn; }

    // Creacion de la conexion a a BD adjuntando una cadena vacia para parametros en la siguiente consulta
    private function query(string $sql, array $params = [], int $fetchMode = SQLSRV_FETCH_ASSOC): array {
        $stmt = sqlsrv_query($this->conn, $sql, $params);
        if ($stmt === false) throw new \RuntimeException(print_r(sqlsrv_errors(), true));
        $rows = [];
        while ($r = sqlsrv_fetch_array($stmt, $fetchMode)) $rows[] = $r;
        return $rows;
    }

     // Funcion principal para la obtencion de emleados con busqueda de NoEmp / Nombre / Puesto / DepartamentoClave
    public function getEmpleados(string $filtros = ''): array {
        $sql = "
            SELECT DISTINCT
                tblEmpleados.NoEmp,
                tblEmpleados.Nombre,
                tblPuestos.nombre AS Puesto,
                tblDepartamentos.clave AS DepartamentoClave
            FROM TLX032MXDB.dbo.tblEmpleados
            INNER JOIN TLX001MXDB.dbo.acc_transaction ON tblEmpleados.NoEmp = TLX001MXDB.dbo.acc_transaction.pin
            INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEmpleados.Puesto
            INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
            WHERE tblEmpleados.Bajas = 0
            $filtros
            ORDER BY tblEmpleados.NoEmp ASC;
        ";
        return $this->query($sql);
    }
}
