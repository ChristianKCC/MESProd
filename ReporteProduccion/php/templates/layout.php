<?php
/**
 * Template: layout.php
 * Página 1: Primera operación USTD (60%) + Programa (40%)
 * Página 2+: Operaciones restantes a ancho completo
 */

// Separar primera operación del resto
$todasCategorias = [];
foreach ($tablas as $cat => $maquinas) {
    $todasCategorias[$cat] = $maquinas;
}

$primeraCategoria = array_key_first($todasCategorias);
$restoCategorias  = array_slice($todasCategorias, 1, null, true);

// Primera operación para el layout 60/40
$tablasPrimera = [$primeraCategoria => $todasCategorias[$primeraCategoria]];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 9px;
    color: #1A1A1A;
    padding: 8px;
  }

  /* ── Layout contenedor página 1 ── */
  table.layout-container {
    width: 100%;
    border-collapse: collapse;
    border: none;
  }
  table.layout-container td {
    border: none;
    vertical-align: top;
    padding: 0;
  }
  td.col-ustd  { width: 58%; padding-right: 4px; border-right: 1px solid #B8CDD6; }
  td.col-sep   { width: 2%; border: none; }
  td.col-prog  { width: 40%; padding-left: 4px; }

  /* ── Layout ancho completo páginas siguientes ── */
  .full-width { width: 100%; }

  /* ══════════════════════════════════════
     ESTILOS TABLA USTD
  ══════════════════════════════════════ */
  .op-titulo {
    font-size: 11px;
    font-weight: bold;
    color: #496472;
    margin: 8px 0 4px 0;
    padding-bottom: 2px;
    border-bottom: 1.5px solid #496472;
  }
  .ustd-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-bottom: 5px;
  }
  .ustd-table th {
    padding: 3px 2px;
    border: 0.5px solid #9FBAC5;
    text-align: center;
    font-weight: bold;
    font-size: 8.5px;
  }
  .ustd-table td {
    padding: 2px 3px;
    border: 0.5px solid #9FBAC5;
    text-align: center;
    vertical-align: middle;
    font-size: 8px;
  }
  .th-g1 { background-color: #496472; color: #FFFFFF; }
  .th-g2 { background-color: #799AAC; color: #FFFFFF; }
  .ustd-table td.maq {
    background-color: #B8CDD6;
    font-weight: bold;
    text-align: center;
    color: #1A1A1A;
  }
  .ustd-table td.producto {
    background-color: #EDF2F4;
    text-align: left;
    font-weight: bold;
    color: #496472;
    padding-left: 5px;
    border-left: 1.5px solid #799AAC;
  }
  .ustd-table td.etapa {
    background-color: #FFFFFF;
    text-align: left;
    padding-left: 12px;
    color: #1A1A1A;
  }
  .ustd-table td.num { background-color: #FFFFFF; }
  .ustd-table tr.par td.num,
  .ustd-table tr.par td.etapa { background-color: #F7FAFB; }
  .ustd-table td.indicador {
    background-color: #EDF2F4;
    font-weight: bold;
  }
  .vacio { color: #BBBBBB; }
  .ustd-table tr.total-maq td {
    background-color: #D0E4EC;
    font-weight: bold;
    color: #1A1A1A;
    border-top: 1px solid #496472;
  }
  .ustd-table tr.total-maq td.lbl { text-align: left; padding-left: 5px; color: #496472; }
  .ustd-table tr.total-op td {
    background-color: #496472;
    font-weight: bold;
    color: #FFFFFF;
    border-top: 1.5px solid #2C3E50;
  }
  .ustd-table tr.total-op td.lbl { text-align: left; padding-left: 5px; }

  /* ══════════════════════════════════════
     ESTILOS TABLA PROGRAMA
  ══════════════════════════════════════ */
  .prog-nota {
    font-size: 8px;
    color: #777777;
    margin: 4px 0 4px 0;
  }

  table.prog-leyenda {
    border: none;
    margin-bottom: 5px;
    border-collapse: collapse;
  }
  table.prog-leyenda td {
    border: none;
    padding: 1px 4px 1px 0;
    font-size: 7.5px;
    vertical-align: middle;
  }

  .prog-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: 8px;
    margin-bottom: 5px;
  }
  .prog-table th {
    padding: 3px 2px;
    border: 0.5px solid #9FBAC5;
    text-align: center;
    font-weight: bold;
    font-size: 8.5px;
  }
  .prog-table td {
    padding: 2px 3px;
    border: 0.5px solid #9FBAC5;
    text-align: center;
    vertical-align: middle;
    font-size: 8px;
  }
  .prog-th-g1 { background-color: #496472; color: #FFFFFF; }
  td.prog-tipo { text-align: left; padding-left: 5px; background-color: #FFFFFF; }
  td.prog-num  { background-color: #FFFFFF; }
  tr.prog-par td.prog-tipo,
  tr.prog-par td.prog-num { background-color: #F7FAFB; }

  td.prog-avance { font-weight: bold; color: #FFFFFF; }
  td.av-morado   { background-color: #7B2D8B; color: #FFFFFF; }
  td.av-verde    { background-color: #1C693A; color: #FFFFFF; }
  td.av-amarillo { background-color: #B8860B; color: #FFFFFF; }
  td.av-rojo     { background-color: #9A1C1C; color: #FFFFFF; }

  tr.prog-total-cat td {
    background-color: #D0E4EC;
    font-weight: bold;
    color: #1A1A1A;
    border-top: 1px solid #496472;
  }
  tr.prog-total-cat td.av-morado   { background-color: #7B2D8B; color: #FFFFFF; }
  tr.prog-total-cat td.av-verde    { background-color: #1C693A; color: #FFFFFF; }
  tr.prog-total-cat td.av-amarillo { background-color: #B8860B; color: #FFFFFF; }
  tr.prog-total-cat td.av-rojo     { background-color: #9A1C1C; color: #FFFFFF; }

  tr.prog-total-gen td {
    background-color: #496472;
    font-weight: bold;
    color: #FFFFFF;
    border-top: 1.5px solid #2C3E50;
  }
  tr.prog-total-gen td.av-morado   { background-color: #7B2D8B; color: #FFFFFF; }
  tr.prog-total-gen td.av-verde    { background-color: #1C693A; color: #FFFFFF; }
  tr.prog-total-gen td.av-amarillo { background-color: #B8860B; color: #FFFFFF; }
  tr.prog-total-gen td.av-rojo     { background-color: #9A1C1C; color: #FFFFFF; }

  tr.prog-sep td { height: 4px; border: none; background-color: #FFFFFF; }
</style>
</head>
<body>

<!-- ══ PÁGINA 1: Primera operación (60%) + Programa (40%) ══ -->
<table class="layout-container">
  <tr>
    <td class="col-ustd">
      <?php $tablas = $tablasPrimera; include __DIR__ . '/tabla_produccion.php'; ?>
    </td>
    <td class="col-sep"></td>
    <td class="col-prog">
      <?php include __DIR__ . '/tabla_programa.php'; ?>
    </td>
  </tr>
</table>

<?php if (!empty($restoCategorias)): ?>

  <!-- ══ PÁGINAS SIGUIENTES: Operaciones restantes a ancho completo ══ -->
  <?php foreach ($restoCategorias as $cat => $maquinas): ?>
    <pagebreak />
    <div class="full-width">
      <?php $tablas = [$cat => $maquinas]; include __DIR__ . '/tabla_produccion.php'; ?>
    </div>
  <?php endforeach; ?>

<?php endif; ?>

</body>
</html>