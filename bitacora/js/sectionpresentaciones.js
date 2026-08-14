export class BitPresentaciones {
  async savePresentacion(folio, presentacion, turnoen, notbl) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("presentacion", presentacion);
    data.append("turnoen", turnoen);
    data.append("notbl", notbl);
    const respuestaraw = await fetch("php/presentacion.php?savePresentacion", {
      method: "POST",
      body: data,
    });
    respuestaraw.status === 200 &&
      swal.fire("Listo", "La acción se completo correctamente", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error",
      );
  }

  async savePresentaciontelas(folio, presentacion, turnoen, notbl) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("presentacion", presentacion);
    data.append("turnoen", turnoen);
    data.append("notbl", notbl);
    const respuestaraw = await fetch(
      "php/presentacion.php?savePresentaciontelas",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.status === 200 &&
      swal.fire("Listo", "La acción se completo correctamente", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error",
      );
  }

  async savePresentacionSpooler(folio, presentacion, turnoen, notbl) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("presentacion", presentacion);
    data.append("turnoen", turnoen);
    data.append("notbl", notbl);
    const respuestaraw = await fetch(
      "php/presentacion.php?savePresentacionSpooler",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.status === 200 &&
      swal.fire("Listo", "La acción se completo correctamente", "success");
    this.recargarTblPresentacionSubSpooler();
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error",
      );
  }

  async saveGolpes(id, golpes, merma) {
    const data = new FormData();
    data.append("id", id);
    data.append("golpes", golpes);
    data.append("merma", merma);
    const respuestaraw = await fetch("php/presentacion.php?saveGolpes", {
      method: "POST",
      body: data,
    });
    respuestaraw.status === 500 && console.log("error");
  }
  async savePresentacionaditional(id, real, cajaxp, aum, std) {
    const data = new FormData();
    data.append("id", id);
    data.append("real", real);
    data.append("cajaxp", cajaxp);
    data.append("aum", aum);
    data.append("std", std);
    const respuestaraw = await fetch("php/presentacion.php?updatedatatbl", {
      method: "POST",
      body: data,
    });
    respuestaraw.ok
      ? null
      : swal.fire(
          "ERROR",
          "Hay un problema al actualizar la información",
          "error",
        );
  }

  async savePresentacionAdicionalSpooler(id, norollo, kg, acckg) {
    const data = new FormData();
    data.append("id", id);
    data.append("norollo", norollo);
    data.append("kg", kg);
    data.append("acckg", acckg);
    const respuestaraw = await fetch(
      "php/presentacion.php?updateTablaSpooler",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.ok
      ? null
      : swal.fire(
          "ERROR",
          "Hay un problema al actualizar la información",
          "error",
        );
  }

  async updateMetrosLinealesRollo(id, metrosLineales, accml) {
    const data = new FormData();
    data.append("id", id);
    data.append("ml", metrosLineales);
    data.append("accml", accml);
    const respuestaraw = await fetch(
      "php/presentacion.php?updateMetrosLinealesRollo",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.ok
      ? null
      : swal.fire(
          "ERROR",
          "Hay un problema al actualizar la información",
          "error",
        );
  }

  async saveBajadasBobinas(
    id,
    NoBajada,
    bobinas,
    kgtbajada,
    mlbajada,
    mcbajada,
    kgmbajada,
  ) {
    const data = new FormData();
    data.append("id", id);
    data.append("NoBajada", NoBajada);
    data.append("bobinas", bobinas);
    data.append("kgtbajada", kgtbajada);
    data.append("mlbajada", mlbajada);
    data.append("mcbajada", mcbajada);
    data.append("kgmbajada", kgmbajada);
    const respuesta = await fetch("php/presentacion.php?updateBajadaBobinas", {
      method: "POST",
      body: data,
    });

    if (respuesta.ok) {
      await this.recargarTblPresentacionSubSpooler();
    } else {
      swal.fire(
        "ERROR",
        "Hay un problema al actualizar la información",
        "error",
      );
    }
  }

  async savePresentacionaditionalTelas(
    id,
    ml,
    accmml,
    mm2,
    pesoTotal,
    acumm2,
    acumpt,
  ) {
    const data = new FormData();
    data.append("id", id);
    data.append("ml", ml);
    data.append("accmml", accmml);
    data.append("mm2", mm2);
    data.append("pesoTotal", pesoTotal);
    data.append("acumm2", acumm2);
    data.append("acumpt", acumpt);
    const respuestaraw = await fetch(
      "php/presentacion.php?updatedatatblTelas",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.ok
      ? null
      : swal.fire(
          "ERROR",
          "Hay un problema al actualizar la información",
          "error",
        );
  }

  permitirSoloNumeros(idTabla) {
    var tabla = document.getElementById(idTabla);
    var celdas = tabla.querySelectorAll('td[contenteditable="true"]');

    celdas.forEach(function (celda) {
      celda.addEventListener("keypress", function (event) {
        var charCode = event.which ? event.which : event.keyCode;
        if ((charCode < 48 || charCode > 57) && charCode !== 46) {
          event.preventDefault();
        }
        if (charCode === 13) {
          event.preventDefault();
          celda.blur();
        }
      });
    });
  }
  async DeletePresentacion(folio, notbl) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("notbl", notbl);
    const respuestaraw = await fetch(
      "php/presentacion.php?DeletePresentacion",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.status === 200 &&
      swal.fire("Listo", "La acción se completo correctamente", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error",
      );
  }
  async DeletePresentacionTelas(folio, notbl) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("notbl", notbl);
    const respuestaraw = await fetch(
      "php/presentacion.php?DeletePresentacionTelas",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.status === 200 &&
      swal.fire("Listo", "La acción se completo correctamente", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error",
      );
  }

  agregarBajada(idPT) {
    Swal.fire({
      title: "Agregar nueva bajada",
      text: "¿Deseas crear una nueva bajada?",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí, agregar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#d33",
    }).then((result) => {
      if (result.isConfirmed) {
        this.saveDataTelas(idPT);
      }
    });
  }

  agregarRollo(idRollo) {
    Swal.fire({
      title: "Rollo Maestro",
      text: "¿Deseas agregar un nuevo rollo?",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Sí, agregar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#d33",
    }).then((result) => {
      if (result.isConfirmed) {
        this.saveDataSpooler(idRollo);
      }
    });
  }

  async saveDataTelas(id) {
    const data = new FormData();
    data.append("id", id);

    const respuestaraw = await fetch(
      "php/presentacion.php?InsertarDataTelas2",
      {
        method: "POST",
        body: data,
      },
    );

    if (respuestaraw.status === 200) {
      swal.fire("Listo", "La acción se completó correctamente", "success");
      this.recargarTblPresentacionSubTelas();
    }

    if (respuestaraw.status === 500) {
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error",
      );
    }
  }

  async saveDataSpooler(id) {
    const data = new FormData();
    data.append("id", id);
    const respuestaraw = await fetch(
      "php/presentacion.php?InsertarDataSpooler2",
      {
        method: "POST",
        body: data,
      },
    );

    if (respuestaraw.status === 200) {
      swal.fire("Listo", "La acción se completó correctamente", "success");
      this.recargarTblPresentacionSubSpooler();
    }

    if (respuestaraw.status === 500) {
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error",
      );
    }
  }

  async recargarTblPresentacionSubTelas() {
    const folio = document.getElementById("folio")?.value;

    if (!folio) {
      return;
    }

    const tablas = [
      [1, "tblpresentacionsub1telas"],
      [2, "tblpresentacionsub2telas"],
      [3, "tblpresentacionsub3telas"],
    ];

    for (const [notbl, domtbl] of tablas) {
      if (document.getElementById(domtbl)) {
        await this.tblPresentacionSubtelas(folio, notbl, domtbl);
      }
    }
  }

  async recargarTblPresentacionSubSpooler() {
    const folio = document.getElementById("folio")?.value;

    if (!folio) {
      return;
    }

    const tablas = [
      [1, "tblpresentacionsub1Spooler", "tblClaveSubSpooler1"],
      [2, "tblpresentacionsub2Spooler", "tblClaveSubSpooler2"],
      [3, "tblpresentacionsub3Spooler", "tblClaveSubSpooler3"],
    ];

    for (const [notbl, domtbl, domtbl2] of tablas) {
      if (document.getElementById(domtbl)) {
        await this.tblPresentacionSubSpooler(folio, notbl, domtbl, domtbl2);
      }
    }
  }

  async tblGolpes(folio) {
    const data = new FormData();
    let body = "";
    data.append("folio", folio);
    const respuestaraw = await fetch(
      "php/presentacion.php?tblPresentacionGolpes",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    respuesta.forEach((respuesta) => {
      body += `
      <tr data-id="${respuesta.id}">
          <td contenteditable="true" class="editable" onblur="getCellValueGolpes(this)">${respuesta.golpes}</td>
          <td>${respuesta.merma}</td>
      </tr>`;
    });
    document.getElementById("tblgolpesmermatotal").innerHTML = body;
    this.permitirSoloNumeros("sumTable");
  }

  // Funciones de HookMesh
  async cargarPresentacionesAutomatico(folio) {
    const data = new FormData();
    data.append("folio", folio);

    try {
      // Paso 1: Crear/verificar Hook_Enc para todas las claves
      const respuestaraw = await fetch("api/hook.php?cargarPresentacionesAutomatico", {
        method: "POST",
        body: data,
      });

      if (!respuestaraw.ok) {
        console.error("Error en cargarPresentacionesAutomatico:", respuestaraw.status);
        return;
      }

      const respuesta = await respuestaraw.json();
      console.log(respuesta);

      if (!respuesta.presentaciones) {
        console.log("No hay presentaciones para cargar, limpiando tablas...");
        
        // ✅ LIMPIAR TODAS LAS 3 TABLAS - CON VALIDACIÓN
        for (let notbl = 1; notbl <= 3; notbl++) {
          const selectId = "presentacion" + notbl + "Hook";
          const btnId = "savePresentacion" + notbl + "Hook";
          const domTable = `tblpresentacionsub${notbl}Hook`;
          
          // Validar y limpiar selector
          const select = document.getElementById(selectId);
          if (select) {
            select.value = "";
            select.disabled = false;
          }
          
          // Validar y habilitar botón
          const btn = document.getElementById(btnId);
          if (btn) {
            btn.disabled = false;
          }
          
          // Validar y limpiar tabla
          const table = document.getElementById(domTable);
          if (table) {
            table.innerHTML = `
              <tr>
                <td colspan="5" class="text-center text-muted">No hay etiquetas</td>
              </tr>`;
          }
        }
        return;
      }

      // NUEVO: Obtener qué NoTabla tienen datos
      const noTablaConDatos = [];

      // Paso 2: Por cada presentación creada, cargar sus datos
      // presentaciones es un objeto: { "3422046": {NoTabla: 1, accion: "creado"}, ... }

      for (const [clave, info] of Object.entries(respuesta.presentaciones)) {
    const noTabla = info.NoTabla;
    const idHE = info.idHE;  // ← OBTENER

    if (noTabla && noTabla >= 1 && noTabla <= 3) {
        const domTable = `tblpresentacionsub${noTabla}Hook`;
        await this.tblPresentacionSubHook(folio, noTabla, domTable, clave);
        
        // ← GUARDAR ETIQUETAS EN HISTÓRICO
        if (idHE) {
            await this.guardarEtiquetasHook(folio, clave, idHE);
        }
        
        noTablaConDatos.push(noTabla);
        console.log(`Tabla ${noTabla} cargada con clave ${clave}`);
    }
}

      // NUEVO: Limpiar las tablas que NO tienen datos en este turno
      for (let notbl = 1; notbl <= 3; notbl++) {
        if (!noTablaConDatos.includes(notbl)) {
          const domTable = `tblpresentacionsub${notbl}Hook`;
          
          // Limpiar selector de clave
          document.getElementById("presentacion" + notbl + "Hook").value = "";
          document.getElementById("presentacion" + notbl + "Hook").disabled = false;
          
          // Limpiar tabla
          document.getElementById(domTable).innerHTML = `
            <tr>
              <td colspan="5" class="text-center text-muted">No hay etiquetas</td>
            </tr>`;
          
        }
      }

    } catch (error) {
      console.error("Error en cargarPresentacionesAutomatico:", error);
    }
  }

  async guardarEtiquetasHook(folio, clave, idEncabezadoHook) {
  const data = new FormData();
  data.append("folio", folio);
  data.append("clave", clave);
  data.append("idEncabezadoHook", idEncabezadoHook);
 
  try {
    const respuestaraw = await fetch("php/presentacion.php?guardarEtiquetasHook", {
      method: "POST",
      body: data,
    });
 
    if (!respuestaraw.ok) {
      console.error("Error al guardar etiquetas:", respuestaraw.status);
      return;
    }
 
    const respuesta = await respuestaraw.json();
    console.log(`Etiquetas guardadas: ${respuesta.guardadas}, Duplicadas: ${respuesta.duplicadas}`);
 
  } catch (error) {
    console.error("Error en guardarEtiquetasHook:", error);
  }
}

  async DeletePresentacionHook(folio, notbl) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("notbl", notbl);
    const respuestaraw = await fetch(
      "php/presentacion.php?DeletePresentacionHook",
      {
        method: "POST",
        body: data,
      },
    );
    respuestaraw.status === 200 &&
      swal.fire("Listo", "La acción se completo correctamente", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error",
      );
  }

  async recargarTblPresentacionSubHook() {
    const folio = document.getElementById("folio")?.value;

    if (!folio) return;

    const tablas = [
      [1, "tblpresentacionsub1Hook"],
      [2, "tblpresentacionsub2Hook"],
      [3, "tblpresentacionsub3Hook"],
    ];

    for (const [notbl, domtbl] of tablas) {
      if (document.getElementById(domtbl)) {
        await this.tblPresentacionSubHook(folio, notbl, domtbl);
      }
    }
  }

  // FUNCION A REVISAR
  async tblPresentacionSub(folio, notbl, domtbl) {
    const data = new FormData();
    let body = "";
    let presentacion = "";
    data.append("folio", folio);
    data.append("notbl", notbl);
    const respuestaraw = await fetch(
      "php/presentacion.php?tblPresentacionSub",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    let sumareal = 0;
    let golpes = 0;
    let cajasxpanal = 0;
    let std = 0;
    respuesta.forEach((newelement) => {
      sumareal = sumareal + newelement.real;
      cajasxpanal = sumareal * newelement.panalxcaja;
      std = (sumareal * newelement.factor).toFixed(3);
      console.log("Cajas por panel:", cajasxpanal, "Suma real:", sumareal, "STD:", std);
      body += `
      <tr data-id="${newelement.id}">
        <td>${newelement.hora}</td>
        <td contenteditable="true" class="editable" onblur="getCellValue(this)">${newelement.real}</td>
        <td>${cajasxpanal}</td>
        <td>${sumareal}</td>
        <td>${std}</td>
      </tr>
      `;

      presentacion = newelement.presentacion;
      this.savePresentacionaditional(
        newelement.id,
        newelement.real,
        cajasxpanal,
        sumareal,
        std,
      );
    });
    document.getElementById("presentacion" + notbl).value = presentacion;
    if (body != "") {
      document.getElementById("presentacion" + notbl).disabled = true;
      document.getElementById("savePresentacion" + notbl).disabled = true;
      body += `<tr><td class="fw-bold">Total</td><td>${sumareal}</td><td></td><td>${golpes}</td></tr>`;
    } else {
      document.getElementById("presentacion" + notbl).disabled = false;
      document.getElementById("savePresentacion" + notbl).disabled = false;
    }
    document.getElementById(domtbl).innerHTML = body;
    this.sumartodo();
    this.permitirSoloNumeros(domtbl);

    // Acceder al tbody de la tabla sumTable
    const sumTableBody = document.querySelector("#sumTable tbody");
    const rows = sumTableBody.querySelectorAll("tr");
    this.getCellValueGolpesDos(rows);
  }

  // Tabla de TABBI
  async tblPresentacionSubtelas(folio, notbl, domtbl) {
    const data = new FormData();
    let body = "";
    let presentacion = "";
    data.append("folio", folio);
    data.append("notbl", notbl);
    const respuestaraw = await fetch(
      "php/presentacion.php?tblPresentacionSubTelas",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    let acumuladoMM2 = 0;
    let accmml = 0;
    let mm2 = 0;
    let peso = 0;
    let acummuladoPeso = 0;
    let idPT = 0;
    respuesta.forEach((newelement) => {
      mm2 =
        Math.round(((newelement.ML * newelement.panalxcaja) / 1000) * 1000) /
        1000;
      accmml = Math.round((accmml + newelement.ML) * 1000) / 1000;
      acumuladoMM2 = Math.round((acumuladoMM2 + mm2) * 1000) / 1000;
      peso = Math.round((peso + newelement.PesoTotal) * 1000) / 1000;
      acummuladoPeso =
        Math.round((acummuladoPeso + newelement.PesoTotal) * 1000) / 1000;
      idPT = newelement.idpresentacionenc;
      body += `
            <tr data-id="${newelement.NoBajada}">
              <td>${newelement.NoBajada}</td>
              <td contenteditable="true" class="editable" onblur="getCellValueTelas(this)" onkeydown="handleKeyDownTelas(event, this)">${newelement.ML}</td>
              <td hidden>${accmml}</td>
              <td>${mm2}</td>
              <td contenteditable="true" class="editable" onblur="getCellValueTelas(this)" onkeydown="handleKeyDownTelas(event, this)">${newelement.PesoTotal}</td>
              <td>${acumuladoMM2}</td>
              <td>${acummuladoPeso}</td>  
              
            </tr>`;
      presentacion = newelement.Clave;
      this.savePresentacionaditionalTelas(
        newelement.id,
        newelement.ML,
        accmml,
        mm2,
        newelement.PesoTotal,
        acumuladoMM2,
        acummuladoPeso,
      );
    });
    document.getElementById("presentacion" + notbl + "telas").value =
      presentacion;
    if (body != "") {
      document.getElementById("presentacion" + notbl + "telas").disabled = true;
      document.getElementById("savePresentacion" + notbl + "telas").disabled =
        true;
      body += `<tr>
                <td class="fw-bold">Total</td>
                <td>${accmml}</td>
                <td></td>
                <td></td>
                <td>${acumuladoMM2}</td>
                <td>${acummuladoPeso}</td>
              </tr>`;
      body += `<tr>
                <td colspan="6" class="text-center">
                  <button class="btn btn-sm bg-target" onclick="agregarBajada(${idPT})" this.disabled=true;>
                    <i class="fas fa-plus"></i> Agregar Bajada
                  </button>
                </td>
              </tr>`;
    } else {
      document.getElementById("presentacion" + notbl + "telas").disabled =
        false;
      document.getElementById("savePresentacion" + notbl + "telas").disabled =
        false;
    }
    document.getElementById(domtbl).innerHTML = body;
    this.sumarTodoTelas();
    this.permitirSoloNumeros(domtbl);
  }

  // Tabla de Spooler
  async tblPresentacionSubSpooler(folio, notbl, domtbl, domtbl2) {
    const data = new FormData();
    let body = "";
    let body2 = "";
    let presentacion = "";
    data.append("folio", folio);
    data.append("notbl", notbl);
    // Obtener datos desde el servidor
    const respuestaraw = await fetch(
      "php/presentacion.php?tblPresentacionSubSpooler",
      {
        method: "POST",
        body: data,
      },
    );
    const respuesta = await respuestaraw.json();
    // Procesar los datos recibidos
    let acckg = 0;
    let accml = 0;
    let idRollo = 0;
    let idBajada = 0;
    respuesta.forEach((newelement) => {
      acckg = Math.round((acckg + newelement.KGTotalRollo) * 1000) / 1000;
      accml = Math.round((accml + newelement.MetrosLineales) * 1000) / 1000;
      idRollo = newelement.idPT;
      idBajada = newelement.idSD;
      body += `
          <tr data-id="${newelement.idSU}">
            <td contenteditable="true" class="editable" onblur="getCellValueRollos(this)" onkeydown="handleKeyDownTelas(event, this)">${newelement.NoRollo || 0}</td>
            <td>${newelement.KGTotalRollo || 0}</td>
            <td hidden>${acckg}</td>
            <td contenteditable="true" class="editable" onblur="getCellValueMetrosLineales(this)" onkeydown="handleKeyDownTelas(event, this)">${newelement.MetrosLineales || 0}</td>
            <td hidden>${accml}</td>
          </tr>
      `;
      presentacion = newelement.Clave;
      this.savePresentacionAdicionalSpooler(
        newelement.idSU,
        newelement.NoRollo,
        newelement.KGTotalRollo,
        acckg,
      );
      this.updateMetrosLinealesRollo(
        newelement.idSU,
        newelement.MetrosLineales,
        accml,
      );
    });

    const { mlBajada, mm2Bajada, ptcb, kgmb } = calcularBajada(
      respuesta,
      acckg,
      accml,
    );

    if (body !== "") {
      document.getElementById("presentacion" + notbl + "Spooler").value =
        presentacion;
      document.getElementById("presentacion" + notbl + "Spooler").disabled =
        true;
      document.getElementById("savePresentacion" + notbl + "Spooler").disabled =
        true;
      body += `<tr>
                <td class="fw-bold">Total</td>
                <td>${acckg}</td>
                <td>${accml}</td>
              </tr>`;
      body += `<tr>
                <td colspan="6" class="text-center">
                  <button class="btn btn-sm bg-target" onclick="agregarRollo(${idRollo})" this.disabled=true;>
                    <i class="fas fa-plus"></i> Agregar Rollo
                  </button>
                </td>
              </tr>`;
      body2 += `
              <tr data-id="${idBajada}">
                <td contenteditable="true" class="editable"
                    onblur="getCellValueBobinas(this)"
                    onkeydown="handleKeyDownTelas(event, this)">
                  ${respuesta[0].NoBajada || 0}
                </td>
                <td contenteditable="true" class="editable"
                    onblur="getCellValueBobinas(this)"
                    onkeydown="handleKeyDownTelas(event, this)">
                  ${respuesta[0].bobinas || 0}
                </td>
                <td>${ptcb.toFixed(3)}</td>
                <td>${mlBajada}</td>
                <td>${mm2Bajada.toFixed(3)}</td>
                <td>${respuesta[0].ancho}</td>
                <td>${kgmb.toFixed(3)}</td>
                <td>${respuesta[0].pesoBase}</td>
              </tr>`;
      body2 += `<tr>
          <td>Hola</td>
        </tr>`;
    }

    document.getElementById(domtbl).innerHTML = body;
    document.getElementById(domtbl2).innerHTML = body2;
  }

  checkInfinity(value) {
    if (value === Infinity || value === -Infinity || Number.isNaN(value)) {
      return 0;
    }
    return Number(value).toFixed(2);
  }

  getCellValue(cell) {
    const folio = document.getElementById("folio").value;
    const row = cell.parentNode;
    const rowId = row.getAttribute("data-id");
    const cells = row.querySelectorAll("td");
    const editableValue = cell.innerText;
    const table = cell.closest("table");
    console.log(cell);

    this.savePresentacionaditional(
      rowId,
      editableValue,
      cells[2].innerText,
      cells[3].innerText,
      cells[4].innerText,
    ).then(() => {
      table.id == "tablapresentacion1" &&
        this.tblPresentacionSub(folio, 1, "tblpresentacionsub1");
      table.id == "tablapresentacion2" &&
        this.tblPresentacionSub(folio, 2, "tblpresentacionsub2");
      table.id == "tablapresentacion3" &&
        this.tblPresentacionSub(folio, 3, "tblpresentacionsub3");
      table.id == "tablapresentacion4" &&
        this.tblPresentacionSub(folio, 4, "tblpresentacionsub4");
    });
  }

  getCellValueTelas(cell) {
    const folio = document.getElementById("folio").value;
    const row = cell.parentNode;
    const rowId = row.getAttribute("data-id");
    const cells = row.querySelectorAll("td");

    let ML = parseFloat(cells[1].innerText) || 0;
    let peso = parseFloat(cells[4].innerText) || 0;

    this.savePresentacionaditionalTelas(
      rowId,
      ML,
      cells[2].innerText,
      cells[3].innerText,
      peso,
      cells[5].innerText,
      cells[6].innerText,
    ).then(() => {
      const table = cell.closest("table");

      table.id === "tablapresentacion1telas" &&
        this.tblPresentacionSubtelas(folio, 1, "tblpresentacionsub1telas");

      table.id === "tablapresentacion2telas" &&
        this.tblPresentacionSubtelas(folio, 2, "tblpresentacionsub2telas");

      table.id === "tablapresentacion3telas" &&
        this.tblPresentacionSubtelas(folio, 3, "tblpresentacionsub3telas");
    });
  }

  async getCellValueRollos(cell) {
    const folio = document.getElementById("folio").value;
    // Obtener el valor ingresado (número de rollo)
    const rolloNumber = cell.innerText.trim();
    // Validar que no esté vacío
    if (!rolloNumber) return;
    // Obtener la fila (tr) a la que pertenece esta celda
    const row = cell.closest("tr");
    // Obtener el ID del registro (data-id de la fila)
    const recordId = row.getAttribute("data-id");
    const cells = row.querySelectorAll("td");
    // Crear FormData para la petición
    const data = new FormData();
    data.append("rollo", rolloNumber);
    try {
      // Realizar la petición al servidor para buscar el rollo
      const response = await fetch("php/presentacion.php?buscarRollo", {
        method: "POST",
        body: data,
      });
      const result = await response.json();
      if (result.success && result.data) {
        // Actualizar la celda de KG (segunda celda de la fila, índice 1)
        const kgCell = row.cells[1]; // La celda de KG está en el índice 1
        kgCell.innerText = result.data[0].PesoTotal;
        this.savePresentacionAdicionalSpooler(
          recordId,
          rolloNumber,
          result.data[0].PesoTotal,
          cells[2].innerText, // Acumulado KG
        ).then(() => {
          const table = cell.closest("table");
          table.id === "tablapresentacion1Spooler" &&
            this.tblPresentacionSubSpooler(
              folio,
              1,
              "tblpresentacionsub1Spooler",
              "tblClaveSubSpooler1",
            );
          table.id === "tablapresentacion2Spooler" &&
            this.tblPresentacionSubSpooler(
              folio,
              2,
              "tblpresentacionsub2Spooler",
              "tblClaveSubSpooler2",
            );
          table.id === "tablapresentacion3Spooler" &&
            this.tblPresentacionSubSpooler(
              folio,
              3,
              "tblpresentacionsub3Spooler",
              "tblClaveSubSpooler3",
            );
        });
      } else {
        // Opcional: Mostrar mensaje al usuario
        // alert(`El rollo ${rolloNumber} no existe en la base de datos`);
      }
    } catch (error) {
      console.error("Error al buscar el rollo:", error);
    }
  }

  getCellValueMetrosLineales(cell) {
    const folio = document.getElementById("folio").value;
    const metrosLineales = cell.innerText.trim();
    const row = cell.closest("tr");
    const rolloId = row.getAttribute("data-id");
    const cells = row.querySelectorAll("td");
    this.updateMetrosLinealesRollo(
      rolloId,
      metrosLineales,
      cells[4].innerText,
    ).then(() => {
      const table = cell.closest("table");
      table.id === "tablapresentacion1Spooler" &&
        this.tblPresentacionSubSpooler(
          folio,
          1,
          "tblpresentacionsub1Spooler",
          "tblClaveSubSpooler1",
        );
      table.id === "tablapresentacion2Spooler" &&
        this.tblPresentacionSubSpooler(
          folio,
          2,
          "tblpresentacionsub2Spooler",
          "tblClaveSubSpooler2",
        );
      table.id === "tablapresentacion3Spooler" &&
        this.tblPresentacionSubSpooler(
          folio,
          3,
          "tblpresentacionsub3Spooler",
          "tblClaveSubSpooler3",
        );
    });
  }

  getCellValueBobinas(cell) {
    const folio = document.getElementById("folio").value;
    const row = cell.closest("tr");
    const rolloId = row.getAttribute("data-id");
    const cells = row.querySelectorAll("td");
    const cellIndex = Array.from(row.children).indexOf(cell);
    let NoBajada =
      cellIndex === 0 ? cell.innerText.trim() : cells[0].innerText.trim();
    let bobinas =
      cellIndex === 1 ? cell.innerText.trim() : cells[1].innerText.trim();
    const table = cell.closest("table");
    this.saveBajadasBobinas(
      rolloId,
      NoBajada,
      bobinas,
      cells[2].innerText.trim(),
      cells[3].innerText.trim(),
      cells[4].innerText.trim(),
      cells[6].innerText.trim(),
    ).then(() => {
      table.id === "tablapresentacion1Spooler" &&
        this.tblPresentacionSubSpooler(
          folio,
          1,
          "tblpresentacionsub1Spooler",
          "tblClaveSubSpooler1",
        );
      table.id === "tablapresentacion2Spooler" &&
        this.tblPresentacionSubSpooler(
          folio,
          2,
          "tblpresentacionsub2Spooler",
          "tblClaveSubSpooler2",
        );
      table.id === "tablapresentacion3Spooler" &&
        this.tblPresentacionSubSpooler(
          folio,
          3,
          "tblpresentacionsub3Spooler",
          "tblClaveSubSpooler3",
        );
    });
  }

  async getCellValueGolpesDos(rows) {
    for (const row of rows) {
      const id = row.getAttribute("data-id");
      const golpes = row.cells[0]?.innerText.trim();
      const merma = row.cells[1]?.innerText.trim();

      // await this.saveGolpes(id, golpes, merma);
    }
  }

  async getCellValueGolpes(cell) {
    const row = cell.parentNode;
    const rowId = row.getAttribute("data-id");
    const cells = row.querySelectorAll("td");
    let editableValue = cell.innerText;
    this.sumartodo();
    await this.saveGolpes(rowId, editableValue, cells[1].innerText);
  }

  // Tabla de HookMesh
  async tblPresentacionSubHook(folio, notbl, domtbl, claveOpcional = null) {
    const data = new FormData();
    data.append("folio", folio);
    data.append("notbl", notbl);

    if (claveOpcional) {
        data.append("clave", claveOpcional);
    }

    const respuestaraw = await fetch("api/hook.php?obtenerEtiquetasHook", {
        method: "POST",
        body: data,
    });

    if (!respuestaraw.ok) {
        console.error("Error en obtenerEtiquetasHook:", respuestaraw.status);
        return;
    }

    const respuesta = await respuestaraw.json();

    if (!Array.isArray(respuesta)) {
        console.error("Respuesta no es un array:", respuesta);
        return;
    }

    let body = "";
    let totalML = 0;
    let clave = "";

   respuesta.forEach((row) => {
    console.log(row);

    totalML += row.MetrosLineales;
    clave = row.Clave;

    const mmc = (row.MetrosLineales * row.factor) / 1000;
    const accML = row.AccML ?? null;
    const accMC = row.AccMC ?? null;

    body += `
        <tr>
            <td>${row.NumeroRollo}</td>
            <td>${row.MetrosLineales}</td>
            <td>${mmc.toFixed(3)}</td>
            <td>${accML !== null ? accML.toFixed(3) : '—'}</td>
            <td>${accMC !== null ? accMC.toFixed(3) : '—'}</td>
        </tr>`;
});

    document.getElementById("presentacion" + notbl + "Hook").value = clave;

    if (body !== "") {
        document.getElementById("presentacion" + notbl + "Hook").disabled = true;

        // AccML y AccMC del total vienen del último row (ya es el acumulado final)
        const ultimoRow = respuesta[respuesta.length - 1];

        body += `
            <tr class="row-total">
                <td colspan="1">TOTAL</td>
                <td>${totalML}</td>
                <td></td>
                <td>${ultimoRow.AccML.toFixed(3)}</td>
                <td>${ultimoRow.AccMC.toFixed(3)}</td>
            </tr>`;
    } else {
        document.getElementById("presentacion" + notbl + "Hook").disabled = false;
        body = `
            <tr>
                <td colspan="5" class="text-center text-muted">No hay etiquetas</td>
            </tr>`;
    }

    document.getElementById(domtbl).innerHTML = body;
}

  // -------------------------------------------------------
  // MERMA HOOK
  // Carga todos los rollos < 1900 ML del folio (de las 3 claves)
  // y renderiza la tabla de merma con checkboxes.
  // -------------------------------------------------------
  async cargarTablaMermaHook(folio, silencioso = false) {
    const tbody = document.getElementById("tblMermaHookBody");
    const resumen = document.getElementById("mermaHookResumen");
    if (!tbody) return;

    if (!folio) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Sin folio activo</td></tr>`;
      return;
    }

    // Solo mostrar spinner en la carga inicial, no en el refresh automático
    if (!silencioso) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center"><span class="spinner-border spinner-border-sm"></span> Cargando...</td></tr>`;
    }

    const data = new FormData();
    data.append("folio", folio);

    try {
      const res = await fetch("php/presentacion.php?obtenerRollosMermaHook", {
        method: "POST",
        body: data,
      });

      if (!res.ok) {
        if (!silencioso)
          tbody.innerHTML = `<tr><td colspan="5" class="text-danger text-center">Error al cargar rollos</td></tr>`;
        return;
      }

      const rollos = await res.json();

      if (!Array.isArray(rollos) || rollos.length === 0) {
        // En refresh silencioso: solo actualizar si la tabla estaba vacía antes
        if (!silencioso || tbody.querySelector(".chk-merma") === null) {
          tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No hay rollos con ML &lt; 1900 en este turno</td></tr>`;
          if (resumen) resumen.textContent = "";
        }
        return;
      }

      // En refresh silencioso: comparar rollos actuales vs nuevos por número de rollo
      // Si no hay cambio en la cantidad, no re-renderizar para no perder los checks del usuario
      if (silencioso) {
        const rollosActuales = Array.from(tbody.querySelectorAll("tr[data-rollo]"))
          .map((tr) => parseInt(tr.dataset.rollo));
        const rollosNuevos = rollos.map((r) => r.NumeroRollo);
        const mismos =
          rollosActuales.length === rollosNuevos.length &&
          rollosNuevos.every((n) => rollosActuales.includes(n));
        if (mismos) return; // Sin cambios — no tocar el DOM
      }

      // Preservar estado de checks antes de re-renderizar
      const checksAnteriores = {};
      tbody.querySelectorAll(".chk-merma").forEach((chk) => {
        checksAnteriores[chk.dataset.rollo] = chk.checked;
      });

      let html = "";
      rollos.forEach((r) => {
        const bloqueado = r.esMerma === 1;

        // Si ya es merma confirmada → bloqueado y siempre checked
        // Si no → respetar el check previo del usuario en pantalla
        const checkedPrev = bloqueado
          ? true
          : checksAnteriores.hasOwnProperty(r.NumeroRollo)
            ? checksAnteriores[r.NumeroRollo]
            : false;

        const checked   = checkedPrev ? "checked" : "";
        const disabled  = bloqueado ? "disabled" : "";
        const rowClass  = bloqueado
          ? "table-danger"         // rojo = merma confirmada, no editable
          : checkedPrev
            ? "table-warning"      // amarillo = seleccionado pero aún no guardado
            : "";

        const badgeMerma = bloqueado
          ? `<span class="badge bg-danger ms-1" title="Ya guardado como merma"><i class="fas fa-lock"></i></span>`
          : "";

        html += `
          <tr class="${rowClass}" data-rollo="${r.NumeroRollo}" data-clave="${r.Clave}" data-ml="${r.MetrosLineales}">
            <td>
              <input type="checkbox" class="form-check-input chk-merma" ${checked} ${disabled}
                     data-rollo="${r.NumeroRollo}" data-clave="${r.Clave}" data-ml="${r.MetrosLineales}"
                     data-bloqueado="${bloqueado ? '1' : '0'}">
            </td>
            <td>${r.NumeroRollo} ${badgeMerma}</td>
            <td>${r.MetrosLineales}</td>
            <td>${r.mmc}</td>
            <td><span class="badge ${r.Clave ? 'bg-primary' : 'bg-secondary'}">${r.Clave ?? "-"}</span></td>
          </tr>`;
      });

      tbody.innerHTML = html;
      this._actualizarResumenMerma();

      // Actualizar resumen al marcar/desmarcar
      tbody.querySelectorAll(".chk-merma").forEach((chk) => {
        chk.addEventListener("change", () => {
          const row = chk.closest("tr");
          row.classList.toggle("table-warning", chk.checked);
          this._actualizarResumenMerma();
        });
      });

    } catch (err) {
      console.error("Error cargarTablaMermaHook:", err);
      if (!silencioso)
        tbody.innerHTML = `<tr><td colspan="5" class="text-danger text-center">Error inesperado</td></tr>`;
    }
  }

  _actualizarResumenMerma() {
    const resumen = document.getElementById("mermaHookResumen");
    if (!resumen) return;

    const todos    = document.querySelectorAll(".chk-merma:checked");
    // Separar bloqueados (ya en BD) de pendientes (selección actual del usuario)
    const bloqueados = Array.from(todos).filter((c) => c.dataset.bloqueado === "1");
    const pendientes = Array.from(todos).filter((c) => c.dataset.bloqueado === "0");

    const mlPendiente = pendientes.reduce((acc, c) => acc + parseFloat(c.dataset.ml || 0), 0);

    let texto = "";
    if (bloqueados.length > 0)
      texto += `${bloqueados.length} en merma · `;
    if (pendientes.length > 0)
      texto += `${pendientes.length} seleccionado(s) — ${mlPendiente.toFixed(1)} ML`;
    if (!texto)
      texto = "Sin selección";

    resumen.textContent = texto;
  }

  async guardarMermaHook(folio) {
    const checks = document.querySelectorAll("#tblMermaHookBody .chk-merma");
    if (checks.length === 0) return;

    // Solo procesar rollos NO bloqueados (los bloqueados ya están en BD)
    const rollos = Array.from(checks)
      .filter((chk) => chk.dataset.bloqueado !== "1")
      .map((chk) => ({
        NumeroRollo   : parseInt(chk.dataset.rollo),
        Clave         : chk.dataset.clave,
        MetrosLineales: parseFloat(chk.dataset.ml),
        esMerma       : chk.checked ? 1 : 0,
      }));

    // Si no hay nada editable que procesar, no hacer nada
    if (rollos.length === 0) {
      Swal.fire({
        title: "Sin cambios",
        text : "Todos los rollos ya están guardados como merma.",
        icon : "info",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }

    const seleccionados = rollos.filter((r) => r.esMerma === 1).length;
    const regresados    = rollos.filter((r) => r.esMerma === 0).length;

    // No tiene sentido guardar si no hay ningún rollo marcado para merma
    if (seleccionados === 0) {
      Swal.fire({
        title: "Sin selección",
        text : "Selecciona al menos un rollo para enviar a merma.",
        icon : "info",
        timer: 2000,
        showConfirmButton: false,
      });
      return;
    }

    const confirm = await Swal.fire({
      title: "¿Confirmar merma?",
      html : `Se enviarán <b>${seleccionados}</b> rollo(s) a merma.<br>
              ${regresados > 0 ? `<b>${regresados}</b> rollo(s) regresarán a sus presentaciones.` : ""}`,
      icon : "warning",
      showCancelButton  : true,
      confirmButtonText : "Sí, guardar",
      cancelButtonText  : "Cancelar",
      confirmButtonColor: "#d33",
      cancelButtonColor : "#6c757d",
    });

    if (!confirm.isConfirmed) return;

    try {
      const res = await fetch("api/hook.php?guardarMermaHook", {
        method : "POST",
        headers: { "Content-Type": "application/json" },
        body   : JSON.stringify({ folio, rollos }),
      });

      const result = await res.json();

      if (!res.ok || result.status === "error") {
        Swal.fire("Error", "No se pudo guardar la merma", "error");
        return;
      }

      Swal.fire({
        title: "Guardado",
        html : `<b>${result.guardados}</b> rollo(s) a merma<br>
                <b>${result.regresados}</b> rollo(s) regresados a presentación`,
        icon : "success",
        timer: 2500,
        showConfirmButton: false,
      });

      // Recargar tabla de merma y las 3 tablas Hook
      await this.cargarTablaMermaHook(folio);
      await this.recargarTblPresentacionSubHook();

    } catch (err) {
      console.error("Error guardarMermaHook:", err);
      Swal.fire("Error", "Error inesperado al guardar", "error");
    }
  }

  // Realiza la sumatoria de las celdas de las filas
  sumartodo() {
    let a = 0;

    // Obtiene los cuerpos de las tablas
    const table1 = document
      .getElementById("tablapresentacion1")
      ?.getElementsByTagName("tbody")[0];
    const table2 = document
      .getElementById("tablapresentacion2")
      ?.getElementsByTagName("tbody")[0];
    const table3 = document
      .getElementById("tablapresentacion3")
      ?.getElementsByTagName("tbody")[0];
    const table4 = document
      .getElementById("tablapresentacion4")
      ?.getElementsByTagName("tbody")[0];

    // Tabla de merma donde se van a poner los resultados
    const sumTable = document
      .getElementById("sumTable")
      .getElementsByTagName("tbody")[0];

    // Obtiene las filas de cada tabla
    const rows1 = table1 ? table1.getElementsByTagName("tr") : [];
    const rows2 = table2 ? table2.getElementsByTagName("tr") : [];
    const rows3 = table3 ? table3.getElementsByTagName("tr") : [];
    const rows4 = table4 ? table4.getElementsByTagName("tr") : [];
    const row1golpes = sumTable ? sumTable.getElementsByTagName("tr") : [];

    // Calcula el numero maximo de filas entre las tablas
    const rowCount = Math.max(
      rows1.length,
      rows2.length,
      rows3.length,
      rows4.length,
    );

    for (let i = 0; i < rowCount - 1; i++) {
      // Obtiene el valor de la tercera celda, si no existe se usa 0
      // como valor por defecto
      const cell1 = rows1[i]
        ? rows1[i].getElementsByTagName("td")[2]
        : { textContent: 0 };
      const cell2 = rows2[i]
        ? rows2[i].getElementsByTagName("td")[2]
        : { textContent: 0 };
      const cell3 = rows3[i]
        ? rows3[i].getElementsByTagName("td")[2]
        : { textContent: 0 };
      const cell4 = rows4[i]
        ? rows4[i].getElementsByTagName("td")[2]
        : { textContent: 0 };

      // Contiene los valores de las celdas de la primera columna
      // de la tabla de golpes
      const cellgolpes = row1golpes[i]
        ? row1golpes[i].getElementsByTagName("td")[0]
        : { textContent: 0 };

      // Convierte los valores a numeros decimales
      const value1 = parseFloat(cell1.textContent, 10);
      const value2 = parseFloat(cell2.textContent, 10);
      const value3 = parseFloat(cell3.textContent, 10);
      const value4 = parseFloat(cell4.textContent, 10);
      const valuegolpes = cellgolpes.textContent;

      // Realiza la suma de los valores
      let sum = value1 + value2 + value3 + value4;
      let sum2 = valuegolpes;
      let newRow = sumTable.rows[i];
      let newCell2 = newRow.cells[1];
      a = a + sum;
      let merma = sum2 - sum;
      merma = sum2 == 0 ? 0 : (merma / sum2) * 100;

      // En esta parte, despues de hacer todo el calculo,
      // se asigna el valor de lo que se calculo en la celda
      // correspondiente de la tabla
      newCell2.textContent = this.checkInfinity(merma.toFixed(2));
    }
  }

  sumarTodoTelas() {
    // Obtener tablas
    const table1 = document.querySelector("#tablapresentacion1telas tbody");
    const table2 = document.querySelector("#tablapresentacion2telas tbody");
    const table3 = document.querySelector("#tablapresentacion3telas tbody");

    const sumTable = document.querySelector("#sumTableTelas tbody");

    const rows1 = table1 ? table1.getElementsByTagName("tr") : [];
    const rows2 = table2 ? table2.getElementsByTagName("tr") : [];
    const rows3 = table3 ? table3.getElementsByTagName("tr") : [];

    const sumRows = sumTable ? sumTable.getElementsByTagName("tr") : [];

    const rowCount = Math.max(rows1.length, rows2.length, rows3.length);

    for (let i = 0; i < rowCount - 1; i++) {
      // Obtener celdas (mm2 = col 2, peso = col 3)
      const mm2_1 = rows1[i]?.cells[2]?.textContent || 0;
      const mm2_2 = rows2[i]?.cells[2]?.textContent || 0;
      const mm2_3 = rows3[i]?.cells[2]?.textContent || 0;

      const peso_1 = rows1[i]?.cells[3]?.textContent || 0;
      const peso_2 = rows2[i]?.cells[3]?.textContent || 0;
      const peso_3 = rows3[i]?.cells[3]?.textContent || 0;

      // Convertir a número
      const totalMM2 =
        (parseFloat(mm2_1) || 0) +
        (parseFloat(mm2_2) || 0) +
        (parseFloat(mm2_3) || 0);

      const totalPeso =
        (parseFloat(peso_1) || 0) +
        (parseFloat(peso_2) || 0) +
        (parseFloat(peso_3) || 0);

      // Fila destino
      if (!sumRows[i]) continue;

      let row = sumRows[i];

      // Ejemplo:
      // col 1 = total mm2
      // col 2 = total peso
      row.cells[1].textContent = totalMM2.toFixed(2);
      row.cells[2].textContent = totalPeso.toFixed(2);

      let base = totalPeso; // o cambia según tu lógica
      let merma = base === 0 ? 0 : ((base - totalMM2) / base) * 100;

      row.cells[3].textContent = this.checkInfinity(merma.toFixed(2));
    }
  }
}

const bitPresentaciones = new BitPresentaciones();

window.agregarBajada = (idPT) => {
  bitPresentaciones.agregarBajada(idPT);
};

window.agregarRollo = (idRollo) => {
  bitPresentaciones.agregarRollo(idRollo);
};

window.handleKeyDownTelas = function (e, cell) {
  if (e.key === "Enter") {
    e.preventDefault();
    cell.blur();
  }
};


function calcularBajada(respuesta, acckg, accml) {
  const bobinas = Number(respuesta[0].bobinas) || 0;
  const ancho = Number(respuesta[0].ancho) || 0;
  const pesoBase = Number(respuesta[0].pesoBase) || 0;

  const mlBajada = accml * bobinas;
  const mm2Bajada = (mlBajada * ancho) / 1000000;
  const ptcb = mm2Bajada * pesoBase;
  const kgmb = acckg - ptcb;

  return { mlBajada, mm2Bajada, ptcb, kgmb };
}