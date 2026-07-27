<?php
// Instancia de cabeceras para conexion a la BD
require_once "./CatalogosF.php";
// Instancia de archivos para validaciones de seguridad
require_once "../Session/seguridad.php";
// Crear session para ibm en activo
$noemp = $_SESSION['ibm'];
// Si GetSlcMaquinas o su resultado no es null entonces se ejecuta una consulta
if (isset($_GET["GetSlcMaquinas"])) {
        // Catalogos::getDataSlcDB("TLX009MXDB", "SELECT NoMaquina, NombreMaquina
        //                                                     FROM tblMaquinas
        //                                                     WHERE MaquinaObsoleta = 0
        //                                                     AND NoMaquina IN (82, 84, 64, 97, 77, 60, 61, 63, 62, 65, 67, 68, 69, 70, 71, 72, 83, 74, 75, 81, 85, 86, 73, 76)
        //                                                     ORDER BY NombreMaquina ASC;");

        // Se hace una conexion a la 9
        // Se manda como parametro toda la query de consulta como
        // Numero de departamento / Numero de maquina / Nombre de maquina
        // Se hace un INNER de la 9 en maquinas con la tblMaquinasCombo en donde el numero de maquina coincida
        // Se hace un INNER a la 32 en empleados en donde el numero d departamento coincida con tblMaquinasCombo
        Catalogos::getDataSlcDB("TLX009MXDB", "SELECT tblMC.NoDepto, tblMC.NoMaquina, tblM.NombreMaquina
                                                        FROM tblMaquinasCombo tblMC
                                                        INNER JOIN TLX009MXDB.dbo.tblMaquinas tblM ON tblM.NoMaquina = tblMC.NoMaquina
                                                        INNER JOIN TLX032MXDB.dbo.tblEmpleados tblEMP ON tblEMP.NoDeptoReal = tblMC.NoDepto
                                                        WHERE (
                                                                -- Si es empleado especial (34374 o 58998), muestra todas las máquinas de la lista
                                                                (tblEMP.NoEmp IN (34374, 58998) AND tblMC.NoMaquina IN (82, 84, 64, 97, 77, 60, 61, 63, 62, 65, 67, 68, 69, 70, 71, 72, 83, 74, 75, 81, 85, 86, 73, 76))
                                                                OR
                                                                -- Si es cualquier otro empleado, muestra solo las máquinas de su departamento
                                                                (tblEMP.NoEmp NOT IN (34374, 58998) AND tblMC.NoMaquina IN (82, 84, 64, 97, 77, 60, 61, 63, 62, 65, 67, 68, 69, 70, 71, 72, 83, 74, 75, 81, 85, 86, 73, 76))
                                                                )
                                                        ORDER BY NombreMaquina ASC;");
}