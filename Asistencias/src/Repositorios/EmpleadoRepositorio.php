<?php
namespace src\Repositorios;

class EmpleadoRepositorio
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Creacion de la conexion a la BD adjuntando una cadena vacia para parametros en la siguiente consulta
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
     * Empleados activos que tienen checadas en cualquiera de las dos fuentes:
     * acc_transaction (control de acceso) o att_transaction en las areas 16 y 17.
     * Se usa EXISTS en lugar del INNER JOIN para no excluir a quien SOLO checa
     * en 16/17, y de paso evitar la multiplicacion de filas que obligaba al DISTINCT.
     */
    public function getEmpleados(string $filtros = ''): array
    {
        $sql = "
            SELECT
                tblEmpleados.NoEmp,
                tblEmpleados.Nombre,
                tblPuestos.nombre AS Puesto,
                tblDepartamentos.clave AS DepartamentoClave
            FROM TLX032MXDB.dbo.tblEmpleados
            INNER JOIN TLX009MXDB.dbo.tblPuestos ON tblPuestos.id = tblEmpleados.Puesto
            INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = tblEmpleados.NombreDepartamento
            WHERE tblEmpleados.Bajas = 0
              AND (
                    EXISTS (SELECT 1 FROM TLX001MXDB.dbo.acc_transaction a
                            WHERE a.pin = tblEmpleados.NoEmp)
                 OR EXISTS (SELECT 1 FROM TLX001MXDB.dbo.att_transaction b
                            WHERE b.pers_person_pin = tblEmpleados.NoEmp
                              AND b.auth_area_no IN (16,17))
              )
            $filtros
            ORDER BY tblEmpleados.NoEmp ASC;
        ";
        return $this->query($sql);
    }
}