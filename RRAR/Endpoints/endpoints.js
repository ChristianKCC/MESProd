/* ============================================================================
   ENDPOINTS: mapa central de rutas + funciones de llamada
   Implementacion:
   import { API, llamarGET, llamarPOST } from "../Endpoints/endpoints.js";
   ============================================================================ */

const BASE = "."; // raíz del proyecto relativa a cada index.php

export const API = {
  /* ------- Vista 1: Reporte RARR (AnalisisRRS) ------- */
  getDepartamentos: `${BASE}/AnalisisRRS/php/getDepartamentos.php`,
  getResumen: `${BASE}/AnalisisRRS/php/getResumen.php`, // ?idDepartamento=
  getClasificacion: `${BASE}/AnalisisRRS/php/getClasificacion.php`, // ?idDepartamento=
  getTablaMaquinas: `${BASE}/AnalisisRRS/php/getTablaMaquinas.php`, // ?idDepartamento=
  getSeccionesMaquina: `${BASE}/AnalisisRRS/php/getSeccionesMaquina.php`, // ?idMaquina=
  getRARRxSeccion: `${BASE}/AnalisisRRS/php/getRARRxSeccion.php`, // ?idEquipo=

  // Descarga y generacion de excel y PDF
  exportarRARR: `${BASE}/AnalisisRRS/php/exportarRARR.php`, // ?idEquipo= (descarga directa)
  exportarPDF: `${BASE}/AnalisisRRS/php/exportarPDF.php`, // ?idEquipo= (descarga directa)

  /* ------- Vista 2: Registro con tabs (RegistroRARR) ------- */
  getMaquinas: `${BASE}/RegistroRARR/php/getMaquinas.php`, // ?idDepartamento= (opcional)
  getInfoMaquina: `${BASE}/RegistroRARR/php/getInfoMaquina.php`, // ?idMaquina=
  getSeccionesRARR: `${BASE}/RegistroRARR/php/getSeccionesRARR.php`, // ?idMaquina=
  getCatalogos: `${BASE}/RegistroRARR/php/getCatalogos.php`,
  getGenericos: `${BASE}/RegistroRARR/php/getGenericos.php`,
  getEmpleado: `${BASE}/RegistroRARR/php/getEmpleado.php`, // ?noEmp=

  // Tab 1: escenarios de riesgo
  insertarEscenario: `${BASE}/RegistroRARR/php/insertarEscenario.php`,
  getEscenarios: `${BASE}/RegistroRARR/php/getEscenarios.php`, // ?idMaquina=

  // Tab 2: evaluación
  guardarEvaluacion: `${BASE}/RegistroRARR/php/guardarEvaluacion.php`,
  getEvaluaciones: `${BASE}/RegistroRARR/php/getEvaluaciones.php`, // ?idMaquina=

  // Tab 3 bloque A: acciones de mejora
  insertarAccion: `${BASE}/RegistroRARR/php/insertarAccion.php`,
  getAcciones: `${BASE}/RegistroRARR/php/getAcciones.php`, // ?idMaquina=

  // Tab 3 bloque B: seguimiento de medidas de control
  insertarSeguimiento: `${BASE}/RegistroRARR/php/insertarSeguimiento.php`,
  getSeguimientos: `${BASE}/RegistroRARR/php/getSeguimientos.php`, // ?idMaquina=

  /* Guardado final (los 3 pasos de golpe, en transacción) */
  registrarRARR: `${BASE}/RegistroRARR/php/registrarRARR.php`, // POST payload + imágenes

  /* Tab 4: análisis de registros */
  getRARRxEquipo: `${BASE}/RegistroRARR/php/getRARRxEquipo.php`, // ?idEquipo=
  eliminarRARR: `${BASE}/RegistroRARR/php/eliminarRARR.php`, // POST idEquipo

  // Común: eliminar registro (soft delete) desde los iconos de bote
  eliminarRegistro: `${BASE}/RegistroRARR/php/eliminarRegistro.php`, // POST tipo, id

  /* Modal Personalizar */
  getConfig: `${BASE}/RegistroRARR/php/getConfig.php`, // ?tipo=
  guardarConfig: `${BASE}/RegistroRARR/php/guardarConfig.php`, // POST tipo, id, descripcion
  guardarSeccion: `${BASE}/RegistroRARR/php/guardarSeccion.php`, // POST id, idMaquina, nombreSeccion, abreviatura

  /* Tab 4 */
  concluirRARR: `${BASE}/RegistroRARR/php/concluirRARR.php`, // POST idEquipo

  /* Imagenes */
  getImagenRARR: `${BASE}/RegistroRARR/php/getImagenRARR.php`, // ?idImagen= o ?idEscenario=&paso=

  /* Obtencion de RRAR pendientes */
  getPendientesRARR: `${BASE}/RegistroRARR/php/getPendientesRARR.php`, // ?idEquipo=

  getFeedbackRARR: `${BASE}/RegistroRARR/php/getFeedbackRARR.php`, // ?idEquipo=
  responderFeedbackRARR: `${BASE}/RegistroRARR/php/responderFeedbackRARR.php`, // POST

  getContadorFeedbackRARR: `${BASE}/RegistroRARR/php/getContadorFeedbackRARR.php`, // ?idEquipo=

  guardarBorrador: `${BASE}/RegistroRARR/php/guardarBorrador.php`,
  getBorradores: `${BASE}/RegistroRARR/php/getBorradores.php`,
  getImagenBorrador: `${BASE}/RegistroRARR/php/getImagenBorrador.php`,
  eliminarBorrador: `${BASE}/RegistroRARR/php/eliminarBorrador.php`,
};

/* GET con query params: llamarGET(API.getResumen, { idDepartamento: 3 }) */
export async function llamarGET(url, params = {}) {
  const qs = new URLSearchParams(params).toString();
  const respuesta = await fetch(qs ? `${url}?${qs}` : url);
  return await procesar(respuesta);
}

/* POST con FormData (acepta File para las imágenes) */
export async function llamarPOST(url, datos = {}) {
  const fd = new FormData();
  Object.entries(datos).forEach(([k, v]) => {
    if (v instanceof File) fd.append(k, v, v.name);
    else fd.append(k, v ?? "");
  });
  const respuesta = await fetch(url, { method: "POST", body: fd });
  return await procesar(respuesta);
}

async function procesar(respuesta) {
  let json;
  try {
    json = await respuesta.json();
  } catch (e) {
    throw new Error("Respuesta no válida del servidor");
  }
  if (!json.ok) {
    throw new Error(json.error || "Error en el servidor");
  }
  return json;
}
