import { Toolsjs } from "../../Tools/Tools.js";
import { BitCalidad } from "./sectioncalidad.js";
import { BitAsistencias } from "./sectionasistencias.js";
import { BitCorrugados } from "./sectioncorrugados.js";
import { ReporteBitacora } from "./sectionreporte.js";
import { Tiempos } from "./sectionctrolTiemposOld.js";
import { BitPresentaciones } from "./sectionpresentaciones.js";
import { Comentarios } from "./sectioncomentarios.js";
import { BitTiempos } from "./sectionCtrolTiempos.js";
import { BitInspeccion } from "./sectionInspeccion.js";
import { PlanProducc } from "../modules/Bitacora.js";
import { PresentacionSpooler } from "./sectionSpooler.js";

const Calidad = new BitCalidad();
const Asistencias = new BitAsistencias();
const Corrugados = new BitCorrugados();
const reporteObj = new ReporteBitacora();
const TiemposObj = new Tiempos();
const Presentaciones = new BitPresentaciones();
const ComentariosObj = new Comentarios();
const BitTiemposObj = new BitTiempos();
const BitInspeccionObj = new BitInspeccion();
const Tools = new Toolsjs();
const PlanProduccObj = new PlanProducc();
let intervaloSesion; // para TurnoVale y checkSession
let intervaloParos; // para tblParos cada 10 segundos
let horasTurno = 0;
let horasTurnoTrabajadas = 0;

const horasPorTurno = {
  1: 8,
  2: 7.5,
  3: 8.5,
};

// Limpiar intervalo de Hook cuando salgas de la página
window.addEventListener("beforeunload", () => {
  if (window.hookRefreshInterval) {
    clearInterval(window.hookRefreshInterval);
    window.hookRefreshInterval = null;
  }
});

class Bitacorastart {
  constructor() {
    this.api = new PresentacionSpooler();
    this.currentTab = 1;
    this.catalogoClaves = [];
    this.debounceTimers = {};

    this.state = {
      1: this._crearEstado(1),
      2: this._crearEstado(2),
      3: this._crearEstado(3),
    };
  }

  _crearEstado(noTabla) {
    return {
      noTabla,
      iniciado: false, // true después de Play
      idPT: null, // viene de BD al hacer Play
      folio: null,
      clave: null,
      claveData: null, // { pesoBase, anchoCorte }
      rollos: [],
      historial: [],
    };
  }

  get s() {
    return this.state[this.currentTab];
  }

  async init() {
    await this._cargarClaves();
    await this._cargarSesionPorFolio();
    this._exponerAlDOM();
  }

  async _cargarSesionPorFolio() {
    const folio = document.getElementById("folio").value;
    if (!folio) return;

    try {
      const presentaciones = await this.api.getSesionPorFolio(folio);
      if (!presentaciones || presentaciones.length === 0) return;
      console.log(presentaciones);

      presentaciones.forEach((p) => {
        const s = this.state[p.NoTabla];
        if (!s) return;

        s.iniciado = true;
        s.idPT = p.idPT;
        s.folio = folio;
        s.clave = p.Clave;
        s.claveData = this._getClaveData(p.Clave) || null;
        s.historial = (p.historial || []).map((h) => ({
          noBajada: h.NoBajada,
          bobinas: h.bobinas,
          kgTotal: h.KgTotales,
          mlBajada: h.MLBajada,
          mm2Bajada: h.MMCBajada,
          kgMerma: h.KgMBajada,
        }));

        //Insertar fila vacia lista para seguir trabajando
        s.rollos = [{ noRollo: "", kg: 0, ml: 0 }];
      });

      // Renderizar el tab activo con los datos restaurados
      this._sincronizarClaveActual();
      this._sincronizarCamposClaveActual();
      this._render();
    } catch (e) {
      console.error("Error al cargar sesión por folio:", e);
    }
  }

  async abrirturno() {
    const respuestaraw = await fetch("./php/bitacora.php?abreturno");
    const respuesta = await respuestaraw.json();
    let ses = 0;
    if (respuesta && respuesta[0]) {
      ses = Number(respuesta[0].sesion);

      const hideOldSessions = [
        60, 61, 63, 64, 65, 73, 74, 76, 77, 81, 82, 83, 84, 97,
      ];
      // Si el arreglo contiene algún valor de ses (NoMaquina)
      if (hideOldSessions.includes(ses)) {
        const el = document.getElementById("tiemposOldSection");
        const elRechazos = document.getElementById("rechazosSeccion");
        if (el) el.hidden = true; // Si el existe, lo oculta
        if (elRechazos) elRechazos.hidden = true; //Si elRechazos existe, lo oculta
      } else {
        const elNew = document.getElementById("tiemposNuevaSeccion");
        const liMenu = document.getElementById("sectionctrolTiemposMenu");
        // Si el valor de ses (NoMaquina) es igual a 85
        // if (ses === 85) {
        //   const headers85 = ["Hora", "Bajadas", "MML", "Kg", "MM2"]; // Declarar nuevos encabezados
        //   for (let i = 1; i <= 4; i++) {
        //     const tbl = document.getElementById(`tablapresentacion${i}`);
        //     if (!tbl) continue; // Si no existe la tabla, se continua con la siguiente
        //     const thead = tbl.querySelector("thead");
        //     // Obtener la fila de encabezado, ya sea del thead o de la primera fila de la tabla
        //     const headerRow = thead
        //       ? thead.querySelector("tr")
        //       : tbl.querySelector("tr");
        //     if (!headerRow) continue; // Si no existe la fila de encabezado, se continua con la siguiente
        //     Array.from(headerRow.children).forEach((cell, idx) => {
        //       // Si existe un nuevo encabezado para esta posición, se actualiza el texto
        //       if (headers85[idx]) cell.textContent = headers85[idx];
        //     });
        //   }
        // }
        if (elNew) elNew.hidden = true;
        if (liMenu) liMenu.hidden = true;
      }

      // Mostrar/ocultar campo con id="conOsinWR" solo para máquinas 60-65
      const conOsin = document.getElementById("conOsinWR");
      const mostrarEn = [60, 61, 62, 63, 64, 65];
      if (conOsin) conOsin.hidden = !mostrarEn.includes(ses);

      document.getElementById("folio").value = respuesta[0].id;
      document.getElementById("folioenctext").textContent = respuesta[0].id;
      document.getElementById("turnoenctext").textContent = respuesta[0].turno;
      obtenerTurnoYHoras(respuesta[0].turno, respuesta[0].horasTrabajadas);
      horasTurno = obtenerTurnoYHoras(
        respuesta[0].turno,
        respuesta[0].horasTrabajadas,
      );
      document.getElementById("rechazosTurno").value =
        respuesta[0].RechazosTurno || 0;
    }
    return ses;
  }
  // Volver al turno anterior con credenciales
  // async turnoanterior(fecha, turno, usuario, password) {
  //   const data = new FormData();
  //   data.append("fecha", fecha);
  //   data.append("turno", turno);
  //   data.append("usuario", usuario);
  //   data.append("password", password);

  //   const respuestaraw = await fetch("./php/bitacora.php?turnoanterior", {
  //     method: "POST",
  //     body: data,
  //   });

  //   const respuesta = await respuestaraw.json();

  //   if (respuesta.error) {
  //     Swal.fire({
  //       icon: "error",
  //       title: "Credenciales inválidas",
  //       text: "El usuario o la contraseña no son correctos.",
  //     });
  //     return null;
  //   } else {
  //     document.getElementById("folio").value = respuesta[0].id;
  //     const folioTurnoAnterior = respuesta[0].id;
  //     document.getElementById("folioenctext").textContent = respuesta[0].id;
  //     document.getElementById("turnoenctext").textContent = respuesta[0].turno;
  //     obtenerTurnoYHoras(respuesta[0].turno, respuesta[0].horasTrabajadas);
  //     BitTiemposObj.tblParos(folioTurnoAnterior);
  //     return folioTurnoAnterior;
  //   }
  // }

  // Solo para volver al turno anterior sin credenciales
  async turnoanterior(fecha, turno) {
    if (window.hookRefreshInterval) {
      clearInterval(window.hookRefreshInterval);
      window.hookRefreshInterval = null;
    }
    const data = new FormData();
    data.append("fecha", fecha);
    data.append("turno", turno);
    const respuestaraw = await fetch("./php/bitacora.php?turnoanterior", {
      method: "POST",
      body: data,
    });
    const respuesta = await respuestaraw.json();
    document.getElementById("folio").value = respuesta[0].id;
    const folioTurnoAnterior = respuesta[0].id;
    document.getElementById("folioenctext").textContent = respuesta[0].id;
    document.getElementById("turnoenctext").textContent = respuesta[0].turno;
    obtenerTurnoYHoras(respuesta[0].turno, respuesta[0].horasTrabajadas);
    BitTiemposObj.tblParos(folioTurnoAnterior);

    // 👇 Agregar esto: resetear estado y recargar sesión del turno anterior
    this.state = {
      1: this._crearEstado(1),
      2: this._crearEstado(2),
      3: this._crearEstado(3),
    };
    await this._cargarSesionPorFolio();

    return folioTurnoAnterior;
  }

  async checkSession() {
    let response = await fetch("../Session/sessioncheck.php");
    let data = await response.json();
    if (data.status === "expired") window.location.href = "../login.php";
  }
  async main(folio) {
    await Promise.all([
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion1",
        0,
      ).then(() =>
        Presentaciones.tblPresentacionSub(folio, 1, "tblpresentacionsub1"),
      ),
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion2",
        0,
      ).then(() =>
        Presentaciones.tblPresentacionSub(folio, 2, "tblpresentacionsub2"),
      ),
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion3",
        0,
      ).then(() =>
        Presentaciones.tblPresentacionSub(folio, 3, "tblpresentacionsub3"),
      ),
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion4",
        0,
      ).then(() =>
        Presentaciones.tblPresentacionSub(folio, 4, "tblpresentacionsub4"),
      ),
    ]);
  }

  async maintelas(folio) {
    await Promise.all([
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion1telas",
        0,
      ).then(() =>
        Presentaciones.tblPresentacionSubtelas(
          folio,
          1,
          "tblpresentacionsub1telas",
        ),
      ),
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion2telas",
        0,
      ).then(() =>
        Presentaciones.tblPresentacionSubtelas(
          folio,
          2,
          "tblpresentacionsub2telas",
        ),
      ),
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion3telas",
        0,
      ).then(() =>
        Presentaciones.tblPresentacionSubtelas(
          folio,
          3,
          "tblpresentacionsub3telas",
        ),
      ),
    ]);
  }

  async mainHook(folio){
    await Promise.all([
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion1Hook",
        0,
      ).then(() =>
        Presentaciones.cargarPresentacionesAutomatico(
          folio
        ),
      ),
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion2Hook",
        0,
      ).then(() =>
        Presentaciones.cargarPresentacionesAutomatico(
          folio,
        ),
      ),
      Tools.llnarslc(
        "CatalogosBitacora",
        "GetClavesValesE",
        "presentacion3Hook",
        0,
      ).then(() =>
        Presentaciones.cargarPresentacionesAutomatico(
          folio,
        ),
      ),
    ]);
    // Refrescar tablas cada 10 segundos
    window.hookRefreshInterval = setInterval(() => {
      Presentaciones.cargarPresentacionesAutomatico(folio);
    }, 10000); // 10 segundos
  }

  // CATALOGO DE CLAVES

  async _cargarClaves() {
    this.catalogoClaves = await this.api.getClaves();
    const select = document.getElementById("selectClave");
    select.innerHTML = "";

    this.catalogoClaves.forEach((clave) => {
      const option = document.createElement("option");
      option.value = clave.clave;
      option.textContent = clave.ClaveDescripcion;
      select.appendChild(option);
    });

    this._sincronizarClaveActual();
  }

  _getClaveData(clave) {
    return this.catalogoClaves.find((c) => c.clave === clave) || null;
  }

  // TAB - Cambio de presentación
  setPresentacion(n) {
    this._sincronizarDomAlEstado(); // Guarda cambios antes de cambiar de tab
    this.currentTab = n;

    document.querySelectorAll(".nav-link").forEach((b, i) => {
      b.classList.toggle("active", i === n - 1);
    });

    this._sincronizarClaveActual();
    this._render();
  }

  _sincronizarClaveActual() {
    const s = this.s;
    const select = document.getElementById("selectClave");

    if (!s.clave && this.catalogoClaves.length > 0) {
      s.clave = this.catalogoClaves[0].clave;
      s.claveData = this.catalogoClaves[0];
    }

    select.value = s.clave || "";
    select.disabled = s.iniciado;

    const btnPlay = document.getElementById("btnPlay");
    btnPlay.disabled = s.iniciado;
    btnPlay.classList.toggle("btn-secondary", s.iniciado);
    btnPlay.classList.toggle("bg-target", !s.iniciado);
  }

  _sincronizarCamposClaveActual() {
    const s = this.s;
    const ancho = s.iniciado && s.claveData ? s.claveData.Ancho : "";
  }

  // ----------------------------------------------------------
  // CLAVE — cambio manual en el select
  // ----------------------------------------------------------
  cambiarClave(val) {
    if (this.s.iniciado) return;
    this.s.clave = val;
    this.s.claveData = this._getClaveData(val);
  }

  // Sección Spooler

  async play() {
    const s = this.s;
    const folioBitacora = document.getElementById("folio").value;

    if (s.iniciado) return;

    const { isConfirmed: confirmado } = await Swal.fire({
      title: "Confirmación",
      text: `¿Deseas empezar a generar la Clave ${s.clave} en la Presentación ${s.noTabla}?`,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí",
      cancelButtonText: "No",
    });
    if (!confirmado) return;

    try {
      s.folio = folioBitacora;
      s.idPT = await this.api.saveTblSpooler({
        folio: s.folio,
        clave: s.clave,
        noTabla: s.noTabla,
      });

      s.iniciado = true;
      s.rollos = [{ noRollo: "", kg: 0, ml: 0 }];

      this._sincronizarClaveActual();
      this._render();
    } catch (error) {}
  }

  // Debounce para noRollo
  debounceNoRollo(idx, value) {
    clearTimeout(this.debounceTimers[idx]);
    this.debounceTimers[idx] = setTimeout(
      () => this._buscarKgPorRollo(idx, value),
      500,
    );
  }

  // Busqueda de Kg por rollo

  async _buscarKgPorRollo(idx, noRollo) {
    const s = this.s;
    if (!noRollo.trim()) {
      s.rollos[idx].kg = 0;
      this._actualizarKgEnFila(idx, "");
      this._recalcularBajada();
      return;
    }

    // this._setKgCargando(idx, true);

    try {
      const resultado = await this.api.getRolloPorNumero(noRollo.trim());

      if (resultado) {
        s.rollos[idx].kg = resultado.kg;
        this._actualizarKgEnFila(idx, resultado.kg);
        this._recalcularBajada();
      } else {
        s.rollos[idx].kg = 0;
        this._actualizarKgEnFila(idx, "");
        this._toast(`No se encontró el rollo ${noRollo}`, "warning");
      }
    } catch (error) {
      console.log(error);
      this._toast(`Error al buscar el rollo ${noRollo}`, "danger");
    } finally {
      // this._setKgCargando(idx, false);
    }
  }

  // Actualiza el input KG de una fila sin re-renderizar toda la tabla
  _actualizarKgEnFila(idx, kg) {
    const fila = this._getFila(idx);
    if (!fila) return;

    const inputKg = fila.querySelector('input[data-field="kg"]');
    if (inputKg) inputKg.value = kg;

    this._renderTotalRollos();
  }

  // Helper para obtener una fila del tbody por índice
  _getFila(idx) {
    const tbody = document.getElementById("tblRollos");
    return tbody.querySelector(`tr[data-rollo-idx="${idx}"]`);
  }

  // Actualiza solo la fila total sin re-renderizar las filas de datos
  _renderTotalRollos() {
    const tbody = document.getElementById("tblRollos");
    const filaTotal = tbody.querySelector("tr.fila-total");
    if (!filaTotal) return;

    const { totalKg, totalMl } = this._getTotalesRollos();
    filaTotal.cells[1].textContent = totalKg.toFixed(3);
    filaTotal.cells[2].textContent = totalMl.toFixed(3);

    this._setValue("inpKgTotal", totalKg.toFixed(3));
    this._setValue("inpMlTotal", totalMl.toFixed(3));
  }

  // Sincroniza valores del DOM al estado antes de re-renderizar
  _sincronizarDomAlEstado() {
    const s = this.s;
    const tbody = document.getElementById("tblRollos");
    const filas = tbody.querySelectorAll("tr[data-rollo-idx]");

    filas.forEach((fila) => {
      const idx = parseInt(fila.dataset.rolloIdx);
      if (isNaN(idx) || !s.rollos[idx]) return;

      const inpNoRollo = fila.querySelector('input[data-field="noRollo"]');
      const inpMl = fila.querySelector('input[data-field="ml"]');

      if (inpNoRollo) s.rollos[idx].noRollo = inpNoRollo.value;
      if (inpMl) s.rollos[idx].ml = Number(inpMl.value) || 0;
    });
  }

  // Agregar Rollo
  async agregarRollo() {
    if (!this.s.iniciado) {
      this._toast("Primero debes iniciar la presentación", "warning");
      return;
    }

    const { isConfirmed } = await Swal.fire({
      title: "¿Agregar nuevo rollo?",
      text: "¿Estás seguro de agregar un nuevo rollo?",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí, agregar",
      cancelButtonText: "Cancelar",
    });
    if (!isConfirmed) return;

    this._sincronizarDomAlEstado();
    this.s.rollos.push({ noRollo: "", kg: 0, ml: 0 });
    this._renderRollos();

    this._toast("Se ha agregado el nuevo rollo", "success");
  }

  // Actualiza ML
  updateMl(idx, value) {
    this.s.rollos[idx].ml = Number(value) || 0;
    this._renderTotalRollos();
    this._recalcularBajada();
  }

  // Actualiza KG
  updateKg(idx, value) {
    this.s.rollos[idx].kg = Number(value) || 0;
    this._renderTotalRollos();
    this._recalcularBajada();
  }

  // Calculos en tiempo real
  _getTotalesRollos() {
    const rollos = this.s.rollos;
    const totalKg = rollos.reduce((a, r) => a + Number(r.kg || 0), 0);
    const totalMl = rollos.reduce((a, r) => a + Number(r.ml || 0), 0);
    return { totalKg, totalMl };
  }

  recalcular() {
    this._recalcularBajada();
  }

  _recalcularBajada() {
    const s = this.s;
    if (!s.claveData) return;

    const { totalKg, totalMl } = this._getTotalesRollos();
    const bobinas = Number(document.getElementById("bobinas").value) || 0;
    const { PesoBase, Ancho } = s.claveData;

    const mlBajada = totalMl * bobinas;
    const mm2Bajada = (mlBajada * Ancho) / 1000000;
    const kgBajada = mm2Bajada * PesoBase;
    const kgMerma = totalKg - kgBajada;

    this._setValue("inpMlTotal", totalMl.toFixed(3));
    this._setValue("inpMlBajada", mlBajada.toFixed(3));
    this._setValue("inpMm2Bajada", mm2Bajada.toFixed(4));
    this._setValue("inpKgTotal", kgBajada.toFixed(3));
    this._setValue("kgmermaSpooler1", kgMerma.toFixed(3));
    this._setValue("anchoSpooler1", Ancho);
    this._setValue("pesobaseSpooler1", PesoBase);
  }

  // Guardar datos en BD
  async guardar() {
    const s = this.s;

    if (!s.iniciado) {
      this._toast("Primero debes iniciar la presentación", "warning");
    }

    const noBajada = document.getElementById("bajada").value.trim();
    const bobinas = document.getElementById("bobinas").value.trim();
    const comentarios = document.getElementById("comentarios").value.trim();
    this._sincronizarDomAlEstado();

    if (!noBajada || !bobinas) {
      this._toast("Debes ingresar el número de bajada y bobinas", "warning");
      return;
    }

    const { isConfirmed } = await Swal.fire({
      title: "¿Confirmar guardado?",
      text: `¿Confirmas guardar la Bajada ${noBajada} con ${bobinas} bobinas?`,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí, guardar",
      cancelButtonText: "Cancelar",
    });
    if (!isConfirmed) return;

    const { totalKg, totalMl } = this._getTotalesRollos();
    const { PesoBase, Ancho } = s.claveData || {};

    const mlBajada = totalMl * Number(bobinas);
    const mm2Bajada = (mlBajada * Ancho) / 1000000;
    const kgBajada = mm2Bajada * PesoBase;
    const kgMerma = totalKg - kgBajada;

    try {
      await this.api.saveRollos({
        idPT: s.idPT,
        rollo: s.rollos.map((r) => ({
          NoRollo: r.noRollo,
          accKG: r.kg,
          accMl: r.ml,
        })),
      });

      await this.api.saveBajada({
        idPT: s.idPT,
        noBajada,
        bobinas,
        kgBajada,
        mlBajada,
        mm2Bajada,
        kgMerma,
        comentarios,
      });

      // Agregar al historial local
      s.historial.push({
        noBajada,
        bobinas,
        kgTotal: totalKg,
        mlBajada,
        mm2Bajada,
        kgBajada,
        kgMerma,
      });

      // Reset tabla 1 con fila vacía nueva
      s.rollos = [{ noRollo: "", kg: 0, ml: 0 }];

      // Limpiar formulario
      document.getElementById("bajada").value = "";
      document.getElementById("bobinas").value = "";
      document.getElementById("comentarios").value = "";

      this._render();

      Swal.fire({
        title: "¡Éxito!",
        text: "Bajada guardada con éxito",
        icon: "success",
        timer: 2000,
        showConfirmButton: false,
      });
    } catch (error) {
      console.error("Error al guardar:", error);
      this._toast("Error al guardar", "danger");
    }
  }

  // Renderización
  _render() {
    this._renderRollos();
    this._renderHistorial();
    this._recalcularBajada();
  }

  _renderRollos() {
    const tbody = document.getElementById("tblRollos");
    tbody.innerHTML = "";

    const rollos = this.s.rollos;
    let totalKg = 0;
    let totalML = 0;

    rollos.forEach((rollo, idx) => {
      totalKg += Number(rollo.kg || 0);
      totalML += Number(rollo.ml || 0);

      tbody.insertAdjacentHTML(
        "beforeend",
        `
        <tr data-rollo-idx="${idx}">
          <td>
            <input class="form-control form-control-sm"
            data-field="noRollo"
            value="${rollo.noRollo || ""}"
            oninput="window._bitacora.debounceNoRollo(${idx}, this.value)" >
          </td>
          <td>
            <input type="number" class="form-control form-control-sm bg-light"
            data-field="kg"
            value="${rollo.kg || ""}"
            placeholder="0.00"
            oninput="window._bitacora.updateKg(${idx}, this.value)"
          </td>
          <td>
            <input type="number" class="form-control form-control-sm"
            data-field="ml"
            value="${rollo.ml || ""}"
            oninput="window._bitacora.updateMl(${idx}, this.value)"
          </td>
        </tr>
        `,
      );
    });

    tbody.insertAdjacentHTML(
      "beforeend",
      `
      <tr class="table-secondary fw-bold fila-total">
        <td>TOTAL</td>
        <td>${totalKg.toFixed(2)}</td>
        <td>${totalML.toFixed(2)}</td>
      </tr>
    `,
    );
  }

  _truncarDecimales(val, decimales) {
    const factor = Math.pow(10, decimales);
    return (Math.trunc(val * factor) / factor).toFixed(decimales);
  }

 _renderHistorial() {
    const tbody = document.getElementById("tblHistorial");
    tbody.innerHTML = "";

    if (!this.s.historial) return;

    let totalBobinas = 0;
    let totalKgTotal = 0;
    let totalMlBajada = 0;
    let totalMm2 = 0;
    let totalKgMerma = 0;

    this.s.historial.forEach((item) => {
      console.log(item);
      totalBobinas += Number(item.bobinas || 0);
      totalMlBajada += Number(item.mlBajada || 0);
      totalMm2 += parseFloat(Number(item.mm2Bajada || 0).toFixed(3));

      // Kg: redondeo normal a 0 decimales (mismo criterio que el PDF).
      // Se pasa primero por toFixed(4) para blindar contra errores de
      // representación binaria (ej. 60.129999999996) antes de redondear.
      const kgTotalRedondeado = item.kgTotal
        ? Math.round(Number(Number(item.kgTotal).toFixed(4)))
        : 0;

      totalKgTotal += kgTotalRedondeado;
      totalKgMerma += Number(item.kgMerma || 0);

      tbody.insertAdjacentHTML(
        "beforeend",
        `
      <tr>
        <td>${item.noBajada || ""}</td>
        <td>${item.bobinas || ""}</td>
        <td>${item.kgTotal ? kgTotalRedondeado : ""}</td>
        <td>${item.mlBajada ? Number(Number(item.mlBajada).toFixed(4)).toFixed(3) : ""}</td>
        <td>${item.mm2Bajada ? Number(Number(item.mm2Bajada).toFixed(4)).toFixed(3) : ""}</td>
        <td>${item.kgMerma ? Number(Number(item.kgMerma).toFixed(4)).toFixed(3) : ""}</td>
      </tr>
    `,
      );
    });

    // Fila totalizadora
    tbody.insertAdjacentHTML(
      "beforeend",
      `
    <tr class="table-secondary fw-bold">
      <td>TOTAL</td>
      <td>${totalBobinas}</td>
      <td>${totalKgTotal}</td>
      <td>${Number(totalMlBajada.toFixed(4)).toFixed(3)}</td>
      <td>${Number(totalMm2.toFixed(4)).toFixed(3)}</td>
      <td>${Number(totalKgMerma.toFixed(4)).toFixed(3)}</td>
    </tr>
  `,
    );
  }

  // ----------------------------------------------------------
  // UTILIDADES
  // ----------------------------------------------------------
  _setValue(id, val) {
    const el = document.getElementById(id);
    if (el) el.value = val;
  }

  _toast(msg, tipo = "info") {
    const container = document.getElementById("toastContainer");
    const id = "toast_" + Date.now();
    container.insertAdjacentHTML(
      "beforeend",
      `
      <div id="${id}" class="toast align-items-center text-bg-${tipo} border-0 show" role="alert">
        <div class="d-flex">
          <div class="toast-body">${msg}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto"
            onclick="document.getElementById('${id}').remove()"></button>
        </div>
      </div>
    `,
    );
    setTimeout(() => document.getElementById(id)?.remove(), 3500);
  }

  _exponerAlDOM() {
    window._bitacora = {
      setPresentacion: (n) => this.setPresentacion(n),
      cambiarClave: (v) => this.cambiarClave(v),
      play: () => this.play(),
      agregarRollo: () => this.agregarRollo(),
      debounceNoRollo: (i, v) => this.debounceNoRollo(i, v),
      updateMl: (i, v) => this.updateMl(i, v),
      updateKg: (i, v) => this.updateKg(i, v),
      guardar: () => this.guardar(),
      recalcular: () => this.recalcular(),
    };
  }

  // Fin sección Spooler

  TurnoVale() {
    let fecha = new Date();
    let horas = fecha.getHours();
    horas = (horas < 10 ? "0" : "") + horas;
    if (horas >= 7 && horas <= 14) document.getElementById("turnoen").value = 1;
    else if (horas >= 15 && horas <= 21)
      document.getElementById("turnoen").value = 2;
    else if (horas >= 22 || horas <= 6)
      document.getElementById("turnoen").value = 3;
  }

  async actualizarHorasTurno(folio, horas) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("horas", horas);

    const dataPromise = await fetch("./php/bitacora.php?actualizarHoras", {
      method: "POST",
      body: data,
    });
    dataPromise.status === 500 &&
      console.log("Hay un error en la base de datos");
  }

  async guardaRechazosTurno(folio, rechazos) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("rechazos", rechazos);
    const dataPromise = await fetch("./php/bitacora.php?guardarRechazos", {
      method: "POST",
      body: data,
    });
    dataPromise.status === 500 &&
      console.log("Hay un error en la base de datos");
  }

  async guardarKgRechazados(folio, rechazos) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("rechazos", rechazos);
    const dataPromise = await fetch("./php/bitacora.php?guardarRechazoskgs", {
      method: "POST",
      body: data,
    });
    dataPromise.status === 500 &&
      console.log("Hay un error en la base de datos");
  }
}



let folio = 0;
Bitacorastart = new Bitacorastart();


Bitacorastart.abrirturno().then((ses) => {
  intervaloSesion = setInterval(() => {
    Bitacorastart.TurnoVale();
    Bitacorastart.checkSession();
  }, 1000);
  folio = document.getElementById("folio").value;
  Calidad.tblCalidadsd(folio, "tblcalidadsd");
  Asistencias.tblasistencias(folio);
  Corrugados.tblcorrugados(folio);
  reporteObj.inicia(folio);
  TiemposObj.tblctrltiempos();
  TiemposObj.tblInfoParos(folio);4
 

if(ses === 67){
  Bitacorastart.mainHook(folio);
    show("paginaHook");
    show("rechazosHook");
    hide("pagina1telas");
    hide("paginaAreas");
    hide("rechazosAreas");
    hide("rechazosTelas");
    hide("paginaSpooler");
    hide("golpesMaquinaBitacora");
    hide("paginaWR");
} else if (ses === 85) {
    Bitacorastart.maintelas(folio);
    show("pagina1telas");
    show("rechazosTelas");
    hide("paginaAreas");
    hide("rechazosAreas");
    hide("paginaHook");
    hide("golpesMaquinaBitacora");
    hide("paginaSpooler");
    hide("paginaWR");
    hide("rechazosHook");
} else if(ses === 87 || ses === 88 || ses === 89) {
    Bitacorastart.init();
    hide("paginaAreas");
    hide("rechazosAreas");
    hide("golpesMaquinaBitacora");
    hide("pagina1telas");
    hide("rechazosTelas");
    show("paginaSpooler");
    hide("paginaWR");
    hide("paginaHook");
    hide("rechazosHook");
} else if(ses === 136) {
    Bitacorastart.main(folio);
    hide("paginaAreas");
    hide("rechazosAreas");
    hide("golpesMaquinaBitacora");
    hide("pagina1telas");
    hide("rechazosTelas");
    hide("paginaSpooler");    
    show("paginaWR");
    hide("paginaHook");
    hide("rechazosHook");
} else {
    Bitacorastart.main(folio);
    show("paginaAreas");
    show("rechazosAreas");
    show("golpesMaquinaBitacora");
    hide("pagina1telas");
    hide("rechazosTelas");
    hide("paginaSpooler");
    hide("paginaWR");
    hide("paginaHook");
    hide("rechazosHook");
}


  Presentaciones.tblGolpes(folio);
  ComentariosObj.tblcomentarios(folio);
  BitTiemposObj.tblParos(folio);

  Bitacorastart.actualizarHorasTurno(folio, horasTurno);

  intervaloParos = setInterval(() => {
    BitTiemposObj.tblParos(folio);
  }, 15000);

  BitInspeccionObj.tblInspeccion(folio, "tblinspeccions");
});
document.getElementById("btnreporte").addEventListener("click", (e) => {
  e.preventDefault();
  reporteObj.inicia(folio);
});


function show(id) {
    document.getElementById(id).style.display = "block";
    document.getElementById(id).hidden = false;
}

function hide(id) {
    document.getElementById(id).style.display = "none";
    document.getElementById(id).hidden = true;
}


// Regresar al turno anterior con credenciales
// document.getElementById("turnoanterior").addEventListener("click", (e) => {
//   e.preventDefault();
//   Swal.fire({
//     title: "¿Estás seguro?",
//     text: "Cuidado, volveras a otro turno, deberas dar click en turno actual para volver!",
//     icon: "warning",
//     showCancelButton: true,
//     confirmButtonColor: "#3085d6",
//     cancelButtonColor: "#d33",
//     confirmButtonText: "Si, Entendido!",
//   }).then((result) => {
//     if (result.isConfirmed) {
//       clearInterval(intervaloSesion);
//       clearInterval(intervaloParos);

//       const fecha = document.getElementById("fechaturnocambio").value;
//       const turnocambio = document.getElementById("turnocambio").value;
//       const usuario = document.getElementById("usuarioturnocambio").value;
//       const password = document.getElementById("passwordturnocambio").value;
//       Bitacorastart.turnoanterior(fecha, turnocambio, usuario, password).then(
//         (folio) => {
//           if (!folio) return;

//           folio = document.getElementById("folio").value;
//           Calidad.tblCalidadsd(folio, "tblcalidadsd");
//           Asistencias.tblasistencias(folio);
//           Corrugados.tblcorrugados(folio);
//           TiemposObj.tblctrltiempos(folio);
//           TiemposObj.tblInfoParos(folio);
//           reporteObj.inicia(folio);
//           Bitacorastart.main(folio);
//           Bitacorastart.maintelas(folio);
//           ComentariosObj.tblcomentarios(folio);
//           Presentaciones.tblGolpes(folio);
//           const modalElement = document.getElementById("ModalTurno");
//           const modalInstance = bootstrap.Modal.getInstance(modalElement);
//           modalInstance.hide();
//           document.getElementById("msjturno").innerHTML =
//             " No estas en el turno actual";
//           document.getElementById("fechaturnocambio").value = "";
//           document.getElementById("turnocambio").value = "";
//           document.getElementById("usuarioturnocambio").value = "";
//           document.getElementById("passwordturnocambio").value = "";
//         }
//       );
//     }
//   });
// });

// Para volver al turno anterior sin credenciales
document.getElementById("turnoanterior").addEventListener("click", (e) => {
  e.preventDefault();
  Swal.fire({
    title: "¿Estás seguro?",
    text: "Cuidado, volveras a otro turno, deberas dar click en turno actual para volver!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, Entendido!",
  }).then((result) => {
    if (result.isConfirmed) {
      clearInterval(intervaloSesion);
      clearInterval(intervaloParos);

      const fecha = document.getElementById("fechaturnocambio").value;
      const turnocambio = document.getElementById("turnocambio").value;
      Bitacorastart.turnoanterior(fecha, turnocambio).then((folio) => {
        if (!folio) return;

        folio = document.getElementById("folio").value;
        Calidad.tblCalidadsd(folio, "tblcalidadsd");
        Asistencias.tblasistencias(folio);
        Corrugados.tblcorrugados(folio);
        TiemposObj.tblctrltiempos(folio);
        TiemposObj.tblInfoParos(folio);
        reporteObj.inicia(folio);
        Bitacorastart.main(folio);
        Bitacorastart.init();
        Bitacorastart.maintelas(folio)
        Bitacorastart.mainHook(folio);
        ComentariosObj.tblcomentarios(folio);
        Presentaciones.tblGolpes(folio);
        const modalElement = document.getElementById("ModalTurno");
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        modalInstance.hide();
        document.getElementById("msjturno").innerHTML =
          " No estas en el turno actual";
        document.getElementById("fechaturnocambio").value = "";
        document.getElementById("turnocambio").value = "";
      });
    }
  });
});

document.getElementById("cerrarturno").addEventListener("click", (e) => {
  e.preventDefault();
  Swal.fire({
    title: "¿Estás seguro?",
    text: "¿Quieres ir al turno actual? ",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, Estoy seguro!",
  }).then((result) => {
    if (result.isConfirmed) {
      Bitacorastart.abrirturno().then(() => {
        location.reload();
        folio != document.getElementById("folio").value
          ? location.reload()
          : Swal.fire({
              title: "Lo siento!",
              text: "Ya estas en el turno actual",
              icon: "warning",
            });
      });
    }
  });
});

// Asistencias
document.getElementById("asistenciasModalid").addEventListener("click", () => {
  Asistencias.limpiar();
});
document
  .getElementById("noempasis")
  .addEventListener("keyup", function (event) {
    event.preventDefault();
    let noemp = document.getElementById("noempasis").value;
    Tools.getDataEmpleado(
      noemp,
      "nombreasis",
      "departamentoasis",
      "puestoasis"
    );
  });

document
  .getElementById("guardarasistencias")
  .addEventListener("click", async function (event) {
    event.preventDefault();
    let folio = document.getElementById("folio").value;
    let noemp = document.getElementById("noempasis").value;
    let nombre = document.getElementById("nombreasis").value;
    let idregconsultado = document.getElementById("idregconsultado").value;
    if (folio == "") {
      alert("Debe haber un folio seleccionado");
      return false;
    } else if (noemp == "" || nombre == "") {
      alert("Debe llenar el campo de numero de empleado");
      return false;
    }
    Asistencias.saveAsistencia(idregconsultado, folio, noemp).then(() => {
      Asistencias.tblasistencias(folio);
    });
  });

window.consultarAsistencia = (param) => Asistencias.consultarAsistencia(param);

// Calidad
document.getElementById("CalidadModalid").addEventListener("click", () => {
  Calidad.limpiar();
});
document
  .getElementById("guardacalidad")
  .addEventListener("click", function (e) {
    e.preventDefault();
    const idcalidad = document.getElementById("idcalidad").value;
    const inspeccionados = document.getElementById("inspeccionados").value;
    const sd = document.getElementById("sd").value;
    const ql = document.getElementById("ql").value;
    const sdobservaciones = document.getElementById("sdobservaciones").value;
    const folio = document.getElementById("folio").value;
    Calidad.savecalidad(
      idcalidad,
      folio,
      inspeccionados,
      sd,
      ql,
      sdobservaciones
    ).then(() => {
      Calidad.tblCalidadsd(folio, "tblcalidadsd");
    });
  });
window.consultarCalidad = (e) => Calidad.consultarCalidadxID(e);

// Corrugados

Tools.llnarslc("CatalogosBitacora", "GetClavesValesE", "claveproducto", 0);
document.getElementById("CorrugadosModalid").addEventListener("click", () => {
  Corrugados.limpiar();
});
document
  .getElementById("guardacorrugados")
  .addEventListener("click", function (event) {
    event.preventDefault();
    let folio = document.getElementById("folio").value;
    let crecibidas = document.getElementById("crecibidas").value;
    let calmacen = document.getElementById("calmacen").value;
    let cproducidas = document.getElementById("cproducidas").value;
    let centregadas = document.getElementById("centregadas").value;
    let claveproducto = document.getElementById("claveproducto").value;
    let idregconsultado = document.getElementById("idregconsultado").value;
    if (folio == "") {
      alert("Debe haber un folio seleccionado");
      return false;
    } else if (
      crecibidas == "" ||
      calmacen == "" ||
      cproducidas == "" ||
      centregadas == ""
    ) {
      alert("Debe llenar todos los campo");
      return false;
    }
    Corrugados.savecorrugados(
      idregconsultado,
      folio,
      crecibidas,
      calmacen,
      cproducidas,
      centregadas,
      claveproducto
    ).then(() => {
      Corrugados.tblcorrugados(folio);
    });
  });

window.consultarCorrugado = (e) => Corrugados.consultarCorrugado(e);

document.getElementById("excelRep").addEventListener("click", function (e) {
  e.preventDefault();
  Herramientas.exportartablaexcel("excelrep");
});

// Control de tiempos viejo

Tools.llnarslc("CatalogosBitacora", "GetSeccionesTiempos", "seccion", 0);
document.getElementById("seccion").addEventListener("change", function () {
  let seccion = document.getElementById("seccion").value;
  TiemposObj.seccionChg(seccion);
});
document.getElementById("horafinal").addEventListener("blur", function () {
  let horai = document.getElementById("horainicio").value;
  let horaf = document.getElementById("horafinal").value;
  var fecha1 = new Date("2000-01-01T" + horai + ":00Z");
  var fecha2 = new Date("2000-01-01T" + horaf + ":00Z");
  var diferencia = fecha2.getTime() - fecha1.getTime();
  var minutos = Math.floor(diferencia / 1000 / 60);
  document.getElementById("diftiempo").innerHTML = minutos + " minutos";
});
document
  .getElementById("guardarctrltiempos")
  .addEventListener("click", function (event) {
    event.preventDefault();
    let horainicio = document.getElementById("horainicio").value;
    let horafinal = document.getElementById("horafinal").value;
    let operacion = document.getElementById("operacion").value;
    let electrico = document.getElementById("electrico").value;
    let mecanico = document.getElementById("mecanico").value;
    let materias = document.getElementById("materias").value;
    let grado = document.getElementById("grado").value;
    let prev = document.getElementById("prev").value;
    let servicios = document.getElementById("servicios").value;
    let subtotal = document.getElementById("subtotal").value;
    let seccion = document.getElementById("seccion").value;
    let modulo = document.getElementById("modulo").value;
    let motivo = document.getElementById("motivo").value;
    let correccion = document.getElementById("correccion").value;
    let folio = document.getElementById("folio").value;
    let idregconsultado = document.getElementById("idregconsultado").value;
    if (folio == "") {
      alert("Debe haber un folio seleccionado");
      return false;
    } else if (
      horainicio == "" ||
      horafinal == "" ||
      operacion == "" ||
      electrico == "" ||
      mecanico == "" ||
      materias == "" ||
      grado == "" ||
      prev == "" ||
      servicios == "" ||
      seccion == "" ||
      modulo == "" ||
      motivo == "" ||
      correccion == ""
    ) {
      alert("Debe llenar todos los campos");
      return false;
    }
    const form = new FormData();
    form.append("horainicio", horainicio);
    form.append("horafinal", horafinal);
    form.append("operacion", operacion);
    form.append("electrico", electrico);
    form.append("mecanico", mecanico);
    form.append("materias", materias);
    form.append("grado", grado);
    form.append("prev", prev);
    form.append("servicios", servicios);
    form.append("subtotal", subtotal);
    form.append("seccion", seccion);
    form.append("modulo", modulo);
    form.append("motivo", motivo);
    form.append("correccion", correccion);
    form.append("id", idregconsultado);
    form.append("folio", folio);
    if (idregconsultado != "") {
      (async () => {
        const respuestaraw = await fetch(
          "./php/bitacora.php?editarctrltiempos",
          {
            method: "POST",
            body: form,
          }
        );
        const respuesta = await respuestaraw.json();
        TiemposObj.tblctrltiempos();
        // TiemposObj.tblInfoParos(folio);
        document.getElementById("formctrltiempos").reset();
        document.getElementById("idregconsultado").value = "";
        document.getElementById("editando").innerHTML = "";
      })();
      alert("Información actualizada");
      return false;
    }
    (async () => {
      const respuestaraw = await fetch(
        "./php/bitacora.php?guardarctrltiempos",
        {
          method: "POST",
          body: form,
        }
      );
      const respuesta = await respuestaraw.json();
      TiemposObj.tblctrltiempos();
      // TiemposObj.tblInfoParos(folio);
      document.getElementById("formctrltiempos").reset();
    })();
  });

window.consultarCtrlTiempos = (param) => TiemposObj.consultarCtrlTiempos(param);
// Tiempos sanitisacion

// SANITIZACION NUEVO
const exampleModal = document.getElementById("modalsanitizacion");
exampleModal.addEventListener("show.bs.modal", function (event) {
  // TiemposObj.limpiar();
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = exampleModal.querySelector(".modal-title");
  const modalBodyInput = exampleModal.querySelector(".modal-body input");
  modalTitle.textContent = "Sanitización con folio " + recipient;
  modalBodyInput.value = recipient;
  TiemposObj.infoSanitizacion(recipient);
});

document
  .getElementById("noempsanitizacionNew")
  .addEventListener("keyup", (e) => {
    e.preventDefault();
    Tools.getDataEmpleado(e.target.value, "nombresanitizacionNew", "", "");
  });

document
  .getElementById("addempsanitizacionNew")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const noemp = document.getElementById("noempsanitizacionNew").value;
    const nombre = document.getElementById("nombresanitizacionNew").value;
    if (nombre === "") {
      alert("No coincide el número de empleado con ningún nombre");
      return false;
    }
    const nuevaFila = document.createElement("tr");
    const celdanoemp = document.createElement("td");
    const celdanombre = document.createElement("td");
    celdanoemp.textContent = noemp;
    celdanombre.textContent = nombre;
    nuevaFila.appendChild(celdanoemp);
    nuevaFila.appendChild(celdanombre);
    nuevaFila.addEventListener("dblclick", function () {
      this.remove();
    });
    document.querySelector("#tblempsanNew tbody").appendChild(nuevaFila);
    document.getElementById("noempsanitizacionNew").value = "";
    document.getElementById("nombresanitizacionNew").value = "";
  });
// document.getElementById("saveSanitizacion").addEventListener("click", (e) => {
//   e.preventDefault();
//   const motivo = document.getElementById("motivosanitizacion").value;
//   const tiempo = document.getElementById("tiemposanitizacion").value;
//   const usuario = document.getElementById("usuariosanitizacion").value;
//   const password = document.getElementById("passwordsanitizacion").value;
//   const folio = document.getElementById("recipient-name").value;
//   TiemposObj.saveSanitizacion(
//     folio,
//     motivo,
//     tiempo,
//     usuario,
//     password,
//     "tblempsan"
//   );
// });

// Seccion Presetntaciones

// FIN SANITIZACION NUEVO

// SANITIZACION VIEJO

const exampleModalViejo = document.getElementById("modalsanitizacionOld");
exampleModalViejo.addEventListener("show.bs.modal", function (event) {
  // TiemposObj.limpiar();
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = exampleModalViejo.querySelector(".modal-title");
  const modalBodyInput = exampleModalViejo.querySelector(".modal-body input");
  modalTitle.textContent = "Sanitización con folio " + recipient;
  modalBodyInput.value = recipient;
  TiemposObj.infoSanitizacion(recipient);
});

document
  .getElementById("noempsanitizacionOld")
  .addEventListener("keyup", (e) => {
    e.preventDefault();
    Tools.getDataEmpleado(e.target.value, "nombresanitizacionOld", "", "");
  });

document
  .getElementById("addempsanitizacionOld")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const noemp = document.getElementById("noempsanitizacionOld").value;
    const nombre = document.getElementById("nombresanitizacionOld").value;
    if (nombre === "") {
      alert("No coincide el número de empleado con ningún nombre");
      return false;
    }
    const nuevaFila = document.createElement("tr");
    const celdanoemp = document.createElement("td");
    const celdanombre = document.createElement("td");
    celdanoemp.textContent = noemp;
    celdanombre.textContent = nombre;
    nuevaFila.appendChild(celdanoemp);
    nuevaFila.appendChild(celdanombre);
    nuevaFila.addEventListener("dblclick", function () {
      this.remove();
    });
    document.querySelector("#tblempsanOld tbody").appendChild(nuevaFila);
    document.getElementById("noempsanitizacionOld").value = "";
    document.getElementById("nombresanitizacionOld").value = "";
  });
document.getElementById("saveSanitizacion").addEventListener("click", (e) => {
  e.preventDefault();
  const motivo = document.getElementById("motivosanitizacionOld").value;
  const tiempo = document.getElementById("tiemposanitizacionOld").value;
  const folio = document.getElementById("recipient-name").value;
  TiemposObj.saveSanitizacion(folio, motivo, tiempo, "tblempsanOld");
});

// FIN SANITIZACION VIEJO

window.getCellValue = (e) => {
  Presentaciones.getCellValue(e);
};
window.getCellValueGolpes = (e) => {
  Presentaciones.getCellValueGolpes(e);
};

document.getElementById("savePresentacion1").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  const presentacion = document.getElementById("presentacion1").value;
  const turnoen = document.getElementById("turnoenctext").textContent;
  if (presentacion == "") {
    swal.fire("UPS", "Selecciona una clave", "warning");
    return false;
  }
  document.getElementById("presentacion1").disabled = true;
  document.getElementById("savePresentacion1").disabled = true;
  Presentaciones.savePresentacion(folio, presentacion, turnoen, 1).then(() => {
    Presentaciones.tblPresentacionSub(folio, 1, "tblpresentacionsub1");
    Presentaciones.tblGolpes(folio);
  });
});
document.getElementById("savePresentacion2").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  const presentacion = document.getElementById("presentacion2").value;
  const turnoen = document.getElementById("turnoenctext").textContent;
  if (presentacion == "") {
    swal.fire("UPS", "Selecciona una clave", "warning");
    return false;
  }
  document.getElementById("presentacion2").disabled = true;
  document.getElementById("savePresentacion2").disabled = true;
  Presentaciones.savePresentacion(folio, presentacion, turnoen, 2).then(() => {
    Presentaciones.tblPresentacionSub(folio, 2, "tblpresentacionsub2");
    Presentaciones.tblGolpes(folio);
  });
});
document.getElementById("savePresentacion3").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  const presentacion = document.getElementById("presentacion3").value;
  const turnoen = document.getElementById("turnoenctext").textContent;
  if (presentacion == "") {
    swal.fire("UPS", "Selecciona una clave", "warning");
    return false;
  }
  document.getElementById("presentacion3").disabled = true;
  document.getElementById("savePresentacion3").disabled = true;
  Presentaciones.savePresentacion(folio, presentacion, turnoen, 3).then(() => {
    Presentaciones.tblPresentacionSub(folio, 3, "tblpresentacionsub3");
    Presentaciones.tblGolpes(folio);
  });
});
document.getElementById("savePresentacion4").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  const presentacion = document.getElementById("presentacion4").value;
  const turnoen = document.getElementById("turnoenctext").textContent;
  if (presentacion == "") {
    swal.fire("UPS", "Selecciona una clave", "warning");
    return false;
  }
  document.getElementById("presentacion4").disabled = true;
  document.getElementById("savePresentacion4").disabled = true;
  Presentaciones.savePresentacion(folio, presentacion, turnoen, 4).then(() => {
    Presentaciones.tblPresentacionSub(folio, 4, "tblpresentacionsub4");
    Presentaciones.tblGolpes(folio);
  });
});
document.getElementById("resetPresentacion1").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  Presentaciones.DeletePresentacion(folio, 1).then(() => {
    Presentaciones.tblPresentacionSub(folio, 1, "tblpresentacionsub1");
  });
});
document.getElementById("resetPresentacion2").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  Presentaciones.DeletePresentacion(folio, 2).then(() => {
    Presentaciones.tblPresentacionSub(folio, 2, "tblpresentacionsub2");
  });
});
document.getElementById("resetPresentacion3").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  Presentaciones.DeletePresentacion(folio, 3).then(() => {
    Presentaciones.tblPresentacionSub(folio, 3, "tblpresentacionsub3");
  });
});
document.getElementById("resetPresentacion4").addEventListener("click", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  Presentaciones.DeletePresentacion(folio, 4).then(() => {
    Presentaciones.tblPresentacionSub(folio, 4, "tblpresentacionsub4");
  });
});

// Seccion presentaciones Telas No Tejidas

window.getCellValueTelas = (e) => {
  Presentaciones.getCellValueTelas(e);
};
document
  .getElementById("savePresentacion1telas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const presentacion = document.getElementById("presentacion1telas").value;
    const turnoen = document.getElementById("turnoenctext").textContent;
    if (presentacion == "") {
      swal.fire("UPS", "Selecciona una clave", "warning");
      return false;
    }
    document.getElementById("presentacion1telas").disabled = true;
    document.getElementById("savePresentacion1telas").disabled = true;
    Presentaciones.savePresentaciontelas(folio, presentacion, turnoen, 1).then(
      () => {
        Presentaciones.tblPresentacionSubtelas(
          folio,
          1,
          "tblpresentacionsub1telas"
        );
      }
    );
  });
document
  .getElementById("savePresentacion2telas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const presentacion = document.getElementById("presentacion2telas").value;
    const turnoen = document.getElementById("turnoenctext").textContent;
    if (presentacion == "") {
      swal.fire("UPS", "Selecciona una clave", "warning");
      return false;
    }
    document.getElementById("presentacion2telas").disabled = true;
    document.getElementById("savePresentacion2telas").disabled = true;
    Presentaciones.savePresentaciontelas(folio, presentacion, turnoen, 2).then(
      () => {
        Presentaciones.tblPresentacionSubtelas(
          folio,
          2,
          "tblpresentacionsub2telas"
        );
      }
    );
  });
document
  .getElementById("savePresentacion3telas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const presentacion = document.getElementById("presentacion3telas").value;
    const turnoen = document.getElementById("turnoenctext").textContent;
    if (presentacion == "") {
      swal.fire("UPS", "Selecciona una clave", "warning");
      return false;
    }
    document.getElementById("presentacion3telas").disabled = true;
    document.getElementById("savePresentacion3telas").disabled = true;
    Presentaciones.savePresentaciontelas(folio, presentacion, turnoen, 3).then(
      () => {
        Presentaciones.tblPresentacionSubtelas(
          folio,
          3,
          "tblpresentacionsub3telas"
        );
      }
    );
  });

document
  .getElementById("resetPresentacion1telas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    Presentaciones.DeletePresentacionTelas(folio, 1).then(() => {
      Presentaciones.tblPresentacionSubtelas(
        folio,
        1,
        "tblpresentacionsub1telas"
      );
    });
  });
document
  .getElementById("resetPresentacion2telas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    Presentaciones.DeletePresentacionTelas(folio, 2).then(() => {
      Presentaciones.tblPresentacionSubtelas(
        folio,
        2,
        "tblpresentacionsub2telas"
      );
    });
  });
document
  .getElementById("resetPresentacion3telas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    Presentaciones.DeletePresentacionTelas(folio, 3).then(() => {
      Presentaciones.tblPresentacionSubtelas(
        folio,
        3,
        "tblpresentacionsub3telas"
      );
    });
  });

// Seccion HookMesh
// document
//   .getElementById("savePresentacion1Hook")
//   .addEventListener("click", (e) => {
//     e.preventDefault();
//     const folio = document.getElementById("folio").value;
//     const presentacion = document.getElementById("presentacion1Hook").value;
//     const turnoen = document.getElementById("turnoenctext").textContent;
//     if (presentacion == "") {
//       swal.fire("UPS", "Selecciona una clave", "warning");
//       return false;
//     }
//     document.getElementById("presentacion1Hook").disabled = true;
//     document.getElementById("savePresentacion1Hook").disabled = true;
//     Presentaciones.savePresentacionHook(folio, presentacion, turnoen, 1).then(
//       () => {
//         Presentaciones.tblPresentacionSubHook(
//           folio,
//           1,
//           "tblpresentacionsub1Hook"
//         );
//         Presentaciones.tblGolpes(folio);
//       }
//     );
//   });

  // document
  // .getElementById("savePresentacion2Hook")
  // .addEventListener("click", (e) => {
  //   e.preventDefault();
  //   const folio = document.getElementById("folio").value;
  //   const presentacion = document.getElementById("presentacion2Hook").value;
  //   const turnoen = document.getElementById("turnoenctext").textContent;
  //   if (presentacion == "") {
  //     swal.fire("UPS", "Selecciona una clave", "warning");
  //     return false;
  //   }
  //   document.getElementById("presentacion2Hook").disabled = true;
  //   document.getElementById("savePresentacion2Hook").disabled = true;
  //   Presentaciones.savePresentacionHook(folio, presentacion, turnoen, 2).then(
  //     () => {
  //       Presentaciones.tblPresentacionSubHook(
  //         folio,
  //         2,
  //         "tblpresentacionsub2Hook"
  //       );
  //       Presentaciones.tblGolpes(folio);
  //     }
  //   );
  // });
  // document
  // .getElementById("savePresentacion3Hook")
  // .addEventListener("click", (e) => {
  //   e.preventDefault();
  //   const folio = document.getElementById("folio").value;
  //   const presentacion = document.getElementById("presentacion3Hook").value;
  //   const turnoen = document.getElementById("turnoenctext").textContent;
  //   if (presentacion == "") {
  //     swal.fire("UPS", "Selecciona una clave", "warning");
  //     return false;
  //   }
  //   document.getElementById("presentacion3Hook").disabled = true;
  //   document.getElementById("savePresentacion3Hook").disabled = true;
  //   Presentaciones.savePresentacionHook(folio, presentacion, turnoen, 3).then(
  //     () => {
  //       Presentaciones.tblPresentacionSubHook(
  //         folio,
  //         3,
  //         "tblpresentacionsub3Hook"
  //       );
  //       Presentaciones.tblGolpes(folio);
  //     }
  //   );
  // });
  document
    .getElementById("resetPresentacion1Hook")
    .addEventListener("click", (e) => {
      e.preventDefault();
      const folio = document.getElementById("folio").value;
      Presentaciones.DeletePresentacionHook(folio, 1).then(() => {
        Presentaciones.tblPresentacionSubHook(
          folio,
          1,
          "tblpresentacionsub1Hook",
        );
      });
    });
    document
    .getElementById("resetPresentacion2Hook")
    .addEventListener("click", (e) => {
      e.preventDefault();
      const folio = document.getElementById("folio").value;
      Presentaciones.DeletePresentacionHook(folio, 2).then(() => {
        Presentaciones.tblPresentacionSubHook(
          folio,
          2,
          "tblpresentacionsub2Hook",
        );
      });
    });
    document
    .getElementById("resetPresentacion3Hook")
    .addEventListener("click", (e) => {
      e.preventDefault();
      const folio = document.getElementById("folio").value;
      Presentaciones.DeletePresentacionHook(folio, 3).then(() => {
        Presentaciones.tblPresentacionSubHook(
          folio,
          3,
          "tblpresentacionsub3Hook",
        );
      });
    });

// // Seccion Spooler
// document.getElementById("savePresentacion1Spooler").addEventListener("click", (e) => {
//   e.preventDefault();
//   const folio = document.getElementById("folio").value;
//   const presentacion = document.getElementById("presentacion1Spooler").value;
//   const turnoen = document.getElementById("turnoenctext").textContent;
//   if(presentacion == "") {
//     swal.fire("Cuidado", "Debes seleccionar una clave", "warning");
//     return false;
//   }

//   document.getElementById("presentacion1Spooler").disabled = true;
//   document.getElementById("savePresentacion1Spooler").disabled = true;
//   SpoolerObj.savePresentacionSpooler(folio, presentacion, turnoen, 1).then(() => {
//   SpoolerObj.renderTabla1("tblpresentacionsub1Spooler");
//   });
// });

// Comentarios

document
  .getElementById("guardarcomentarios")
  .addEventListener("click", function (event) {
    event.preventDefault();
    let folio = document.getElementById("folio").value;
    let seguridad = document.getElementById("seguridad").value;
    let calidad = document.getElementById("calidadcom").value;
    let oyl = document.getElementById("oyl").value;
    let pendientes = document.getElementById("pendientes").value;
    let otros = document.getElementById("otros").value;
    let idregconsultado = document.getElementById("idregconsultado").value;
    if (folio == "") {
      alert("Debe haber un folio seleccionado");
      return false;
    } else if (
      seguridad == "" ||
      calidad == "" ||
      oyl == "" ||
      pendientes == ""
    ) {
      alert("Todos los campos son obligatorios");
      return false;
    }
    const form = new FormData();
    form.append("folio", folio);
    form.append("seguridad", seguridad);
    form.append("calidad", calidad);
    form.append("oyl", oyl);
    form.append("pendientes", pendientes);
    form.append("otros", otros);
    form.append("id", idregconsultado);
    if (idregconsultado != "") {
      (async () => {
        const respuestaraw = await fetch(
          "./php/bitacora.php?editarcomentarios",
          {
            method: "POST",
            body: form,
          }
        );
        const respuesta = await respuestaraw.json();

        ComentariosObj.tblcomentarios(folio);
        document.getElementById("formcomentarios").reset();
        document.getElementById("idregconsultado").value = "";
        document.getElementById("editando").innerHTML = "";
      })();
      alert("Información actualizada");
      return false;
    }
    (async () => {
      const respuestaraw = await fetch(
        "./php/bitacora.php?guardarcomentarios",
        {
          method: "POST",
          body: form,
        }
      );
      const respuesta = await respuestaraw.json();
      ComentariosObj.tblcomentarios(folio);
      document.getElementById("formcomentarios").reset();
    })();
  });

window.consultarComentarios = function (param) {
  ComentariosObj.consultarComentarios(param);
};

// Tiempos automatico

Tools.llnarslc(
  "CatalogosBitacora",
  "GetSeccionesTiempos",
  "tiemposSecciones",
  0
);
document
  .getElementById("tiemposSecciones")
  .addEventListener("change", function (e) {
    e.preventDefault();
    Tools.llnarslc(
      "CatalogosBitacora",
      "GetModulosTiempos&seccion=" + e.target.value,
      "TiemposModulo",
      0
    );
  });
document
  .getElementById("TiemposModulo")
  .addEventListener("change", function (e) {
    e.preventDefault();
    // const seccion = document.getElementById('tiemposSecciones').value;
    // Tools.llnarslc('CatalogosBitacora', 'GetFallasParos&seccion=' + seccion + '&modulo=' + e.target.value, 'TiemposFalla', 0);
    document.getElementById("TiemposFalla").innerHTML =
      '<option value="209">Sin llenar</option>';
  });
const modalTiempos = document.getElementById("modalTiempos");
modalTiempos.addEventListener("show.bs.modal", function (event) {
  const button = event.relatedTarget;
  const recipient = button.getAttribute("data-bs-whatever");
  const modalTitle = modalTiempos.querySelector(".modal-title");

  BitTiemposObj.dataParosAutomaticos(recipient).then((element) => {
    modalTitle.textContent = "Paro con folio: " + recipient;
    document.getElementById("TiemposParoFolio").value = recipient;

    // Valores por defecto
    const seccion = element[0].NoSeccion ?? 0;
    const modulo = element[0].NoModulo ?? 0;
    const falla = element[0].NoFalla ?? 0;
    const motivo = element[0].Motivo ?? "Sin informacion";
    const correccion = element[0].Correccion ?? "Sin informacion";

    document.getElementById("tiemposSecciones").value = seccion;

    Tools.llnarslc(
      "CatalogosBitacora",
      "GetModulosTiempos&seccion=" + seccion,
      "TiemposModulo",
      0
    ).then(() => {
      document.getElementById("TiemposModulo").value = modulo;
      // Tools.llnarslc('CatalogosBitacora', 'GetTiemposFallas&seccion=' + element[0].seccion + '&modulo=' + element[0].modulo, 'TiemposFalla', 0).then(() => {
      //     document.getElementById('TiemposFalla').value = element[0].falla;
      // })

      // Si falla es null, dejamos opción por defecto
      document.getElementById("TiemposFalla").innerHTML =
        `<option value="${falla}">${
          falla === 0 ? "Sin llenar" : falla
        }</option>`;
    });

    document.getElementById("TiemposCortes").value = element[0].Cortes;
    document.getElementById("TiemposRechazos").value = element[0].Rechazos;
    document.getElementById("Tiempostiempoparo").value = element[0].TiempoParo;
    document.getElementById("fechaParo").value = element[0].Fecha;
    document.getElementById("horaParo").value = element[0].HoraParo;
    document.getElementById("Tiemposmotivos").value = motivo;
    document.getElementById("Tiemposcorreccion").value = correccion;
    document.getElementById("motivosanitizacionNew").value = element[0].motivo;
    document.getElementById("tiemposanitizacionNew").value = element[0].tiempo;
    console.groupEnd(element);
    BitTiemposObj.tblEmpleadosSanitizacion(recipient);
    // let body = "";
    // element.forEach((emp) => {
    //   body += `<tr>
    //     <td>${emp.NoEmp ?? ""}</td>
    //     <td>${emp.EmpleadoNombre ?? ""}</td>
    //   </tr>`;
    // });
    // document.getElementById("tblEmpSanitizacionNew").innerHTML = body;
  });
});
const mymodalTiempos = new bootstrap.Modal(
  document.getElementById("modalTiempos")
);
document
  .getElementById("UpdatedataParo")
  .addEventListener("click", function (e) {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const folioParos = document.getElementById("TiemposParoFolio").value;
    const seccion = document.getElementById("tiemposSecciones").value;
    const modulo = document.getElementById("TiemposModulo").value;
    const motivo = document.getElementById("Tiemposmotivos").value;
    const correccion = document.getElementById("Tiemposcorreccion").value;
    const motivosanitizacion = document.getElementById(
      "motivosanitizacionNew"
    ).value;
    const tiemposanitizacion = document.getElementById(
      "tiemposanitizacionNew"
    ).value;
    BitTiemposObj.updateDataParo(
      folioParos,
      seccion,
      modulo,
      motivo,
      correccion,
      motivosanitizacion,
      tiemposanitizacion,
      "tblempsanNew"
    ).then((res) => {
      if (res === false) return false;
      mymodalTiempos.hide();
      BitTiemposObj.tblParos(folio);
    });
  });

// Inspecciones

Tools.llnarslc("CatalogosBitacora", "GetTipoInspeccion", "inspecciontipo", 0);
Tools.llnarslc("CatalogosBitacora", "GetDescSecpreusos", "seccionpreusos", 0);

document.getElementById("noempinsp").addEventListener("keyup", (e) => {
  Tools.getDataEmpleado(e.target.value, "nombreinsp", "", "");
});
document.getElementById("inspecciontipo").addEventListener("change", (e) => {
  if (e.target.value == "") {
    document.getElementById("inpecciondesc").innerHTML = "";
    document.getElementById("archivopreussos").innerHTML = "";
  } else {
    Tools.llenarCheckbox(
      "CatalogosBitacora",
      "GetDescInspeccion&id=" + e.target.value,
      "inpecciondesc"
    );
    document.getElementById("archivopreussos").innerHTML =
      `<embed src="preusos/${e.target.value}.jpg" type="application/pdf" width="100%" height="500px" />`;
  }
});

document.getElementById("saveinsp").addEventListener("click", function (event) {
  event.preventDefault();
  const datos = {
    noempinsp: document.getElementById("noempinsp").value,
    nombreinsp: document.getElementById("nombreinsp").value,
    inspecciontipo: document.getElementById("inspecciontipo").value,
    seccionpreusos: document.getElementById("seccionpreusos").value,
    inpeccionfecha: document.getElementById("inpeccionfecha").value,
    inpeccioncomentarios: document.getElementById("inpeccioncomentarios").value,
    folio: document.getElementById("folio").value,
    inpecciondesc: [],
  };
  const radiosSeleccionados = document.querySelectorAll(
    '#inpecciondesc input[type="radio"]:checked'
  );
  radiosSeleccionados.forEach((radio) => {
    datos.inpecciondesc.push({
      id: radio.name.replace("opcion_", ""),
      valor: radio.value,
    });
  });
  if (BitInspeccionObj.validarSeleccionCompleta("inpecciondesc")) {
    BitInspeccionObj.saveInpeccion(datos).then(() => {
      BitInspeccionObj.tblInspeccion(datos.folio, "tblinspeccions");
      document.getElementById("formibnsp").reset();
      document.getElementById("inpecciondesc").innerHTML = "";
      document.getElementById("archivopreussos").innerHTML = "";
    });
  }
});
document.getElementById("resetinspecciones").addEventListener("click", () => {
  document.getElementById("inpecciondesc").innerHTML = "";
  document.getElementById("archivopreussos").innerHTML = "";
});

document.getElementById("btnPlanProducc").addEventListener("click", (e) => {
  e.preventDefault();
  PlanProduccObj.visualizarPlanProducc("listaPlanProduccion");
});

document.getElementById("btnVolverPlan").addEventListener("click", (e) => {
  e.preventDefault();
  PlanProduccObj.visualizarPlanProducc("listaPlanProduccion");
});

document.getElementById("btnBuscarPlan").addEventListener("click", (e) => {
  e.preventDefault();
  const fecha = document.getElementById("fechaPlan").value;
  Swal.fire({
    title: "¿Estás seguro de continuar?",
    text: "Vas a ver información de otro día.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, guardar",
  }).then((result) => {
    if (result.isConfirmed) {
      PlanProduccObj.visualizarPlanProduccFecha("listaPlanProduccion", fecha);
    }
  });
});

const modalNuevoParo = new bootstrap.Modal(
  document.getElementById("modalNuevoParo")
);

document.getElementById("crearNuevoParo").addEventListener("click", (e) => {
  e.preventDefault();
  Swal.fire({
    title: "¿Estás seguro?",
    text: "Cuidado, volveras a otro turno, deberas dar click en turno actual para volver!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, Entendido!",
  }).then((result) => {
    if (result.isConfirmed) {
      const folio = document.getElementById("folio").value;
      const seccion = document.getElementById("seccionNuevoParo").value;
      const modulo = document.getElementById("moduloNuevoParo").value;
      const cortes = document.getElementById("cortesNuevoParo").value;
      const rechazos = document.getElementById("rechazosNuevoParo").value;
      const tiempoparo = document.getElementById("tiempoParoNuevoParo").value;
      const hora = document.getElementById("horaNuevoParo").value;
      const motivo = document.getElementById("motivosNuevoParo").value;
      const correccion = document.getElementById("correccionNuevoParo").value;
      const usuario = document.getElementById("usuarioNuevoParo").value;
      const password = document.getElementById("passwordNuevoParo").value;

      BitTiemposObj.crearNuevoParo(
        folio,
        seccion,
        modulo,
        cortes,
        rechazos,
        tiempoparo,
        hora,
        motivo,
        correccion,
        usuario,
        password
      ).then((res) => {
        if (res === false) return false;

        document.getElementById("seccionNuevoParo").value = "";
        document.getElementById("moduloNuevoParo").value = "";
        document.getElementById("cortesNuevoParo").value = "";
        document.getElementById("rechazosNuevoParo").value = "";
        document.getElementById("tiempoParoNuevoParo").value = "";
        document.getElementById("horaNuevoParo").value = "";
        document.getElementById("motivosNuevoParo").value = "";
        document.getElementById("correccionNuevoParo").value = "";
        document.getElementById("usuarioNuevoParo").value = "";
        document.getElementById("passwordNuevoParo").value = "";

        modalNuevoParo.hide();
        BitTiemposObj.tblParos(folio);
      });
    }
  });
});

window.eliminarParo = (idParo) => {
  const folio = document.getElementById("folio").value;
  Swal.fire({
    title: "¿Estás seguro?",
    text: "Una vez eliminado, no podrás recuperar este registro.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, eliminar",
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Ingrese sus credenciales",
        html:
          '<input id="usuarioEliminarParo" type="password" class="swal2-input" placeholder="Usuario">' +
          '<input id="passwordEliminarParo" type="password" class="swal2-input" placeholder="Contraseña">',
        showCancelButton: true,
        confirmButtonText: "Eliminar",
        preConfirm: () => {
          const usuario = document.getElementById("usuarioEliminarParo").value;
          const password = document.getElementById(
            "passwordEliminarParo"
          ).value;
          if (!usuario || !password) {
            Swal.showValidationMessage("Debe ingresar usuario y contraseña");
            return false;
          }
          return { usuario, password };
        },
      }).then((result2) => {
        if (result2.isConfirmed) {
          BitTiemposObj.eliminarParo(
            idParo,
            result2.value.usuario,
            result2.value.password
          ).then((res) => {
            if (res === false) return false;
            BitTiemposObj.tblParos(folio);
          });
        }
      });
    }
  });
};

// Obtener el turno y las horas trabajadas del mismo

function obtenerTurnoYHoras(turno, horasTrabajadas) {
  const horasMaximas = horasPorTurno[turno] || 0;
  const horasInput = document.getElementById("horaNuevoParo");

  if (horasTrabajadas !== null) {
    horasInput.value = horasTrabajadas;
    horasInput.max = horasMaximas;
  } else {
    horasInput.value = horasMaximas;
    horasInput.max = horasMaximas;
  }

  return horasInput.value;
}

document.getElementById("horaNuevoParo").addEventListener("blur", (e) => {
  e.preventDefault();
  const folio = document.getElementById("folio").value;
  const horasTurnoActu = document.getElementById("horaNuevoParo").value;
  Bitacorastart.actualizarHorasTurno(folio, horasTurnoActu);
});

document
  .getElementById("guardarHorasTrabajadas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const horasTurnoActu = document.getElementById("horaNuevoParo").value;
    Bitacorastart.actualizarHorasTurno(folio, horasTurnoActu).then(() => {
      Swal.fire({
        icon: "success",
        title: "Exito",
        text: "Se han guardado las horas trabajadas correctamente.",
        timer: 2000,
        showConfirmButton: false,
      });
    });
  });

document
  .getElementById("guardarRechazosMaquina")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const rechazosTurnoActual = document.getElementById("rechazosTurno").value;
    Bitacorastart.guardaRechazosTurno(folio, rechazosTurnoActual).then(() => {
      Swal.fire({
        icon: "success",
        title: "Exito",
        text: "Se han guardado los rechazos correctamente.",
        timer: 2000,
        showConfirmButton: false,
      });
    });
  });

  document
  .getElementById("guardarRechazosTelas")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const kgsRechazados = document.getElementById("rechazoskgs").value;
    Bitacorastart.guardarKgRechazados(folio, kgsRechazados).then(() => {
      Swal.fire({
        icon: "success",
        title: "Exito",
        text: "Se han guardado los rechazos correctamente.",
        timer: 2000,
        showConfirmButton: false,
      });
    });
  });

  document
  .getElementById("guardarRechazosHook")
  .addEventListener("click", (e) => {
    e.preventDefault();
    const folio = document.getElementById("folio").value;
    const golpesHook = document.getElementById("golpesHook").value;
    Bitacorastart.guardarKgRechazados(folio, golpesHook).then(() => {
      Swal.fire({
        icon: "success",
        title: "Exito",
        text: "Se han guardado los golpes correctamente.",
        timer: 2000,
        showConfirmButton: false,
      });
    });
  });



// NUEVAS FUNCIONES SPOOLER

// document
//   .querySelectorAll('#navPresentaciones .nav-link')
//   .forEach(btn => {
//     btn.addEventListener("click", (e) => {
//       e.preventDefault();
//       cambiarPresentacion(Number(e.currentTarget.dataset.id));
//     });
//   });



// document.addEventListener("DOMContentLoaded", () => {
//   cambiarPresentacion(1);
// });

