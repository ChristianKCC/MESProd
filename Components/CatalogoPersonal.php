<?php
// Instancia de conexion a la BD
require_once "./CatalogosF.php";

// Si los parametros dentro de los isset no son null entonces se usa el metodo de conexion segun su BD y su query
if (isset($_GET["GetSlcDeps"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT NoDepto,NombreDepto FROM tblDepartamentos WHERE Filtro=1 ORDER BY NombreDepto ASC");
} else if (isset($_GET["GetSlcDepsall"])) {
    // Se modifico aqui para no mostrar el departamento de Administrativo en los departamentos reales
    Catalogos::getDataSlcDB("TLX009MXDB",   
        "SELECT NoDepto,NombreDepto 
            FROM tblDepartamentos 
            WHERE NombreDepto <> 'Administrativo'
            ORDER BY NombreDepto ASC");
} else if (isset($_GET["GetSlcMaquinas"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT NoMaquina,NombreMaquina FROM tblMaquinas WHERE MaquinaObsoleta=0 ORDER BY NombreMaquina ASC");
// } else if (isset($_GET["GetSlcMaquinasxdep"])) {
//     $departamento = $_GET['departamento'];
//     Catalogos::getDataSlcDB("TLX009MXDB", "SELECT tblMaquinas.NoMaquina,tblMaquinas.NombreMaquina FROM tblMaquinas
//     INNER JOIN tblMaquinasCombo ON tblMaquinasCombo.NoMaquina=tblMaquinas.NoMaquina WHERE tblMaquinasCombo.NoDepto='$departamento' AND MaquinaObsoleta=0 ORDER BY NombreMaquina ASC");
// }
} else if (isset($_GET["GetSlcMaquinasxdep"])) {
    $departamento = $_GET['departamento'];
    Catalogos::getDataSlcDB("TLX009MXDB", 
        "SELECT tblMaquinas.NoMaquina, tblMaquinas.NombreMaquina 
         FROM tblMaquinas
         INNER JOIN tblMaquinasCombo ON tblMaquinasCombo.NoMaquina = tblMaquinas.NoMaquina 
         WHERE tblMaquinasCombo.NoDepto = '$departamento'   
         AND MaquinaObsoleta = 0 
         ORDER BY NombreMaquina ASC");
} else if (isset($_GET["GetSlcCentroCostos"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT IdCentroCosto,CentroDeCosto FROM tblCentrosDeCosto ORDER BY CentroDeCosto ASC");
} else if (isset($_GET["GetSlcPuestos"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT id,nombre FROM tblPuestos ORDER BY nombre ASC");
} else if (isset($_GET["GetSlcTipoTrabajador"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT strTipoTrabajador as id,strTipoTrabajador FROM tblTipoTrabajador ORDER BY strTipoTrabajador ASC");
} else if (isset($_GET["GetSlcEstadoCivil"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT IdClvEstadoCivil,DescripcionEstadoCivil FROM tblRIEstadoCivil ORDER BY DescripcionEstadoCivil ASC");
} else if (isset($_GET["GetSlcEntidad"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT IdClvEntidad,DescEntidadFederativa FROM tblRIEntidadFederativa ORDER BY DescEntidadFederativa ASC");
} else if (isset($_GET["GetSlcMunicipio"])) {
    $entidad = $_GET['entidad'];
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT ClvMunicipioYDelegacion,DescMunicipioYDelegacion FROM tblRIMunicipiosYDelegaciones 
    WHERE IdClvEntidad=$entidad ORDER BY DescMunicipioYDelegacion ASC");
} else if (isset($_GET["GetSlcNvlEstudios"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT * FROM tblRINivelMaxEstudios2016");
} else if (isset($_GET["GetSlcOcupacion"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT IdClvOcupaciones,DescOcupacion FROM tblRIOcupaciones where Obsoleto=0 ORDER BY DescOcupacion ASC");
} else if (isset($_GET["GetSlcDiscapacidad"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT * FROM tblRIDiscapacidad");
} else if (isset($_GET["GetSlcDocAprobatorio"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT * FROM tblRIDocumentoProbatorio");
} else if (isset($_GET["GetSlcClaveInst"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT * FROM tblRITipoInstitucionEducativa");
} else if (isset($_GET["GetSlcMotivoBaja"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT * FROM tblCausasDeBaja ORDER BY CausaDeBaja ASC");
} else if (isset($_GET["GetSlcJefeInm"])) {
    Catalogos::getDataSlcDB("TLX032MXDB", "SELECT NoEmp,Nombre FROM tblEmpleados WHERE EsJefeDepto=1 ORDER BY Nombre ASC");
} else if (isset($_GET["GetSlcGeneralSiNoNa"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblGeneralSiNoNa");
}


// Datos de labotratorio
else if (isset($_GET["GetSlcEstadosLaboratorio"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblLaboratorioFoliosEstados ORDER BY nombreestado ASC");
}

// Datos de enfermeria
else if (isset($_GET["GetSlcTipoSangre"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblEnfermeriaTipoSangre ORDER BY tiposangredesc ASC");
} else if (isset($_GET["GetSlcReligion"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblEnfermeriaReligion ORDER BY religiondesc ASC");
}
?>