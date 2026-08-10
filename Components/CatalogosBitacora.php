<?php
// Instancia de conexion a la BD
require_once "./CatalogosF.php";
// Instancia de validaciones de seguridad en inicio de sesion
require_once "../Session/seguridad.php";

// Si los parametros en el isset no son null entonces se ejecuta una query basadas en la BD y la cadena
if (isset($_GET["clavesTraz"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT DISTINCT tblTrazabilidadClaves.id, tblTrazabilidadClaves.Nombre FROM tblTrazabilidadClaves
    LEFT JOIN tblTrazabilidadComboClaves ON tblTrazabilidadComboClaves.Clave = tblTrazabilidadClaves.id WHERE tblTrazabilidadComboClaves.Maquina=" . $_SESSION['idmaquina']);
} else if (isset($_GET["modulosTraz"])) {
    $clave = $_GET['clave'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT DISTINCT tblTrazabilidadModulos.ID,tblTrazabilidadModulos.Nombre FROM tblTrazabilidadModulos LEFT JOIN tblTrazabilidadComboClaves ON
    tblTrazabilidadComboClaves.Modulo=tblTrazabilidadModulos.ID WHERE tblTrazabilidadComboClaves.Maquina=" . $_SESSION['idmaquina'] . " AND 
    tblTrazabilidadComboClaves.Clave=$clave");
} else if (isset($_GET["materialesTraz"])) {
    $clave = $_GET['clave'];
    $modulo = $_GET['modulo'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT DISTINCT tblTrazabilidadMateriales.ID,tblTrazabilidadMateriales.Nombre FROM tblTrazabilidadMateriales LEFT JOIN tblTrazabilidadComboClaves ON
    tblTrazabilidadComboClaves.Material=tblTrazabilidadMateriales.ID WHERE tblTrazabilidadComboClaves.Maquina= " . $_SESSION['idmaquina'] . " AND tblTrazabilidadComboClaves.Clave= $clave
	AND tblTrazabilidadComboClaves.Modulo= $modulo");
} else if (isset($_GET["especificacionTraz"])) {
    $clave = $_GET['clave'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT tblTrazabilidadEspecificaciones.ID,tblTrazabilidadEspecificaciones.Nombre FROM tblTrazabilidadClaves 
    INNER JOIN tblTrazabilidadEspecificaciones ON tblTrazabilidadEspecificaciones.ID = tblTrazabilidadClaves.Especificacion 
    WHERE tblTrazabilidadClaves.id=$clave");
} else if (isset($_GET["empleadosallTraz"])) {
    Catalogos::getDataSlcDB("TLX032MXDB", "SELECT tblEmpleados.NoEmp,tblEmpleados.Nombre FROM tblEmpleados WHERE Bajas=0 ORDER BY Nombre");
} else if (isset($_GET["ClaveCorrugado"])) {
    Catalogos::getDataSlcDB("TLX004MXDB", "SELECT id,nombre FROM tblBitPresentaciones WHERE maquina=" . $_SESSION['idmaquina'] . " ORDER BY nombre");
} else if (isset($_GET["GetSeccionesParos"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT tblSecciones.NoSeccion,tblSecciones.NombreSeccion FROM tblSecciones 
    INNER JOIN tblSeccionesCombo ON tblSeccionesCombo.NoSeccion = tblSecciones.NoSeccion WHERE tblSeccionesCombo.NoMaquina=" . $_SESSION["idmaquina"] . " ORDER BY NombreSeccion");
} else if (isset($_GET["GetModulosParos"])) {
    $seccion = $_GET['seccion'];
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT tblModulos.NoModulo,tblModulos.NombreModulo FROM tblModulos 
    INNER JOIN tblModulosCombo On tblModulosCombo.NoModulo = tblModulos.NoModulo WHERE tblModulosCombo.NoSeccion=$seccion");
} else if (isset($_GET["GetFallasParos"])) {
    $seccion = $_GET['seccion'];
    $modulo = $_GET['modulo'];
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT DISTINCT tblFallas.IdFalla, tblFallas.DescripcionFalla FROM tblFallas LEFT JOIN tblCombinacionesBitacora ON
    tblCombinacionesBitacora.IdFalla=tblFallas.IdFalla WHERE tblCombinacionesBitacora.NoSeccion = $seccion AND tblCombinacionesBitacora.NoModulo=$modulo
	AND tblCombinacionesBitacora.NoMaquina = " . $_SESSION['idmaquina']);
} else if (isset($_GET["platicas5Min"])) {
    Catalogos::getDataSlcDB("TLX035MXDB", "SELECT TOP 60 id,nombre FROM tblPlaticas5min ORDER BY id DESC");
} else if (isset($_GET["TurnoHoras"])) {
    $idbitacora = $_GET['idbitacora'];
    Catalogos::getDataSlcDB("TLX004MXDB", "SELECT id,horastr FROM tblBitTurnoHoras WHERE turno=(SELECT Turno FROM tblEncabezadoBitacora WHERE IdEncabezadoBItacora=$idbitacora) ORDER BY hora ASC");
} else if (isset($_GET["GetTurnos"])) {
    Catalogos::getDataSlcDB("TLX036MXDB", "SELECT * FROM Trazabilidadturno");
} else if (isset($_GET["GetDefectosxdep"])) {
    $deps = $_GET["deps"];
    Catalogos::getDataSlcDB("TLX036MXDB", "SELECT Trazabilidaddefectos.* FROM Trazabilidaddefectos
    INNER JOIN Trazabilidaddefectoscombo ON Trazabilidaddefectoscombo.id_defecto=trazabilidaddefectos.id
    INNER JOIN TLX009MXDB.dbo.tblDepartamentos ON tblDepartamentos.NoDepto = Trazabilidaddefectoscombo.id_maquina WHERE Trazabilidaddefectoscombo.id_maquina=$deps");
} else if (isset($_GET["GetClavesallConf"])) {
    Catalogos::getDataSlcDB("TLX036MXDB", "SELECT * FROM Trazabilidadclavesdeproducto ORDER BY nombre ASC");
} else if (isset($_GET["GetMaquinasAll"])) {
    Catalogos::getDataSlcDB("TLX009MXDB", "SELECT NoMaquina,NombreMaquina FROM tblMaquinas  WHERE MaquinaObsoleta=0 ORDER BY NombreMaquina ASC");
} else if (isset($_GET["GetSeccionesTiempos"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT DISTINCT tblProduccionSecciones.IdSeccion, tblProduccionSecciones.Seccion FROM tblProduccionSecciones LEFT JOIN tblProduccionConSeccModFall ON
    tblProduccionConSeccModFall.idSecciones=tblProduccionSecciones.IdSeccion WHERE tblProduccionConSeccModFall.NoMaquina =  " . $_SESSION['idmaquina'] . " ORDER BY Seccion");
} else if (isset($_GET["GetModulosTiempos"])) {
    $seccion = $_GET['seccion'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT DISTINCT tblProduccionModulos.idModulos, tblProduccionModulos.Modulos FROM tblProduccionModulos LEFT JOIN tblProduccionConSeccModFall ON
    tblProduccionConSeccModFall.idModulos=tblProduccionModulos.idModulos WHERE tblProduccionConSeccModFall.idSecciones = $seccion AND tblProduccionConSeccModFall.NoMaquina = " . $_SESSION['idmaquina']. "ORDER BY Modulos");
} else if (isset($_GET["GetSlcPresentaciones"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT clave,CONCAT(clave, ' - ', nombre) FROM tblBitPresentaciones WHERE maquina=" . $_SESSION["idmaquina"] . " ORDER BY nombre ASC");
} else if (isset($_GET["GetClavesValesE"])) {
    Catalogos::getDataSlcDB("TLX004MXDB", "SELECT DISTINCT NoClave, CONCAT(NoClave , ' - ' ,Descripcion_Articulo) as texto FROM vwMXPRClaveMaquina WHERE maquina=" . $_SESSION["idmaquina"] . " ORDER BY NoCLave ASC");
} else if (isset($_GET["GetValesEEstados"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblValeEEstados");
} else if (isset($_GET["GetClasexClave"])) {
    $clave = $_GET['clave'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT DISTINCT tblValeEClases.NoClase, tblValeEClases.Descripcion_Clase FROM tblValeEClases
    INNER JOIN tblValeConClavClasMat ON tblValeConClavClasMat.NoClase = tblValeEClases.NoClase WHERE tblValeConClavClasMat.NoMaquina=" . $_SESSION['idmaquina'] . "
    AND tblValeConClavClasMat.NoClave = '" . $clave . "'");
} else if (isset($_GET["GetMaterialesxclase"])) {
    $idclave = $_GET['clave'];
    $idclase = $_GET['clase'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT DISTINCT tblValeEMateriales.NoMaterial, tblValeEMateriales.NombreMaterial,tblValeEMateriales.CentroCosto,tblValeEMateriales.TiempoMaterial,tblValeEMateriales.TipoMontacargas FROM tblValeEMateriales
    INNER JOIN tblValeConClavClasMat ON tblValeConClavClasMat.NoMaterial = tblValeEMateriales.NoMaterial WHERE tblValeConClavClasMat.NoMaquina=" . $_SESSION['idmaquina'] . " 
    AND tblValeConClavClasMat.NoClave='$idclave' AND tblValeConClavClasMat.NoClase=$idclase");
} else if (isset($_GET["GetFoliosValesRil"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT TOP 2 id, CONCAT('" . $_SESSION["usuario"] . " - ', foliocons,(CASE 
        WHEN CAST(fechaenviado AS DATE) = CAST(GETDATE() AS DATE) THEN ' / Hoy'
        ELSE '' END),' Turno', turno ) AS folio FROM tblValeEEnc
    WHERE maquina = " . $_SESSION['idmaquina'] . " ORDER BY id DESC");
}
// Paros de maquina
else if (isset($_GET["GetTiemposSecciones"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT DISTINCT tblBitCatSecciones.idSeccion,tblBitCatSecciones.nombreSeccion FROM tblBitCatSecciones 
    INNER JOIN tblBitCatCombinacionesMaquina ON tblBitCatCombinacionesMaquina.idSeccion = tblBitCatSecciones.idSeccion WHERE tblBitCatCombinacionesMaquina.idMaquina=" . $_SESSION["idmaquina"] . " ORDER BY tblBitCatSecciones.nombreSeccion");
} else if (isset($_GET["GetTiemposModulos"])) {
    $seccion = $_GET['seccion'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT DISTINCT tblBitCatModulos.idModulo,tblBitCatModulos.nombreModulo FROM tblBitCatModulos 
    INNER JOIN tblBitCatCombinacionesMaquina ON tblBitCatCombinacionesMaquina.idModulo = tblBitCatModulos.idModulo WHERE tblBitCatCombinacionesMaquina.idMaquina=" . $_SESSION["idmaquina"] . "
    AND tblBitCatCombinacionesMaquina.idSeccion=$seccion ORDER BY tblBitCatModulos.nombreModulo");
} else if (isset($_GET["GetTiemposFallas"])) {
    $seccion = $_GET['seccion'];
    $modulo = $_GET['modulo'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT DISTINCT tblBitCatFallas.idFalla,tblBitCatFallas.nombreFalla FROM tblBitCatFallas 
    INNER JOIN tblBitCatCombinacionesMaquina ON tblBitCatCombinacionesMaquina.idFalla = tblBitCatFallas.IdFalla WHERE tblBitCatCombinacionesMaquina.idMaquina=" . $_SESSION["idmaquina"] . "
    AND tblBitCatCombinacionesMaquina.idSeccion=$seccion AND tblBitCatCombinacionesMaquina.idModulo=$modulo ORDER BY tblBitCatFallas.nombreFalla ASC");
}
// No conformidad
else if (isset($_GET["GetComponentesNoconformidad"])) {
    $departamento = $_GET['departamento'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT tblNoConformidadComponentes.* FROM tblNoConformidadComponentes
    INNER JOIN tblNoConformidadComboComponente ON tblNoConformidadComboComponente.componente = tblNoConformidadComponentes.id
    WHERE tblNoConformidadComboComponente.departamento=$departamento ORDER BY tblNoConformidadComponentes.Componente");
}

// producciones
else if (isset($_GET["GetClavesxmaquina"])) {
    $maquina = $_GET['maquina'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT DISTINCT tblValeConClavClasMat.NoClave, CONCAT(tblValeConClavClasMat.NoClave , ' - ' ,tblValeEClaves.Descripcion_Articulo) as texto FROM tblValeConClavClasMat
INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave = tblValeConClavClasMat.NoClave WHERE tblValeConClavClasMat.NoMaquina=" . $maquina . " ORDER BY NoCLave ASC");
} 
else if (isset($_GET["GetClavesxmaquinaSession"])) {
    $maquina = $_SESSION['idmaquina'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT DISTINCT tblValeConClavClasMat.NoClave, CONCAT(tblValeConClavClasMat.NoClave , ' - ' ,tblValeEClaves.Descripcion_Articulo) as texto FROM tblValeConClavClasMat
INNER JOIN tblValeEClaves ON tblValeEClaves.NoClave = tblValeConClavClasMat.NoClave WHERE tblValeConClavClasMat.NoMaquina=" . $maquina . " ORDER BY NoCLave ASC");
} 

else if (isset($_GET["getClaveClase"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblProduccionesClaveClase ORDER BY idClase ASC");
}else if (isset($_GET["getClaveTipo"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblProduccionesClaveTipo ORDER BY idTipo ASC");
}else if (isset($_GET["GetTipoInspeccion"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblInspeccionTipo ORDER BY id ASC");
}else if (isset($_GET["GetDescInspeccion"])) {
    $id = $_GET['id'];
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblInspeccionDesc WHERE tipo = $id ORDER BY id ASC");
} else if (isset($_GET["GetDescSecpreusos"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblBitPreusosSecciones ORDER BY seccionpre ASC");
} else if(isset($_GET["GetSeccionesCombinaciones"])){
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblProduccionSecciones");
} else if (isset($_GET["getClaveProducto"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblProduccionProductos ORDER BY idProducto ASC");
} else if (isset($_GET["getClaveTamaño"])) {
    Catalogos::getDataSlcDB("TLX002MXDB", "SELECT * FROM tblProduccionEtapas ORDER BY idEtapa ASC");
} else if (isset($_GET["getClaveCategoria"])) {
    Catalogos::getDataSlcDB("TLX004MXDB", "SELECT * FROM tblProduccionOperaciones ORDER BY idOperacion ASC");
}