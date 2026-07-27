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
        "error"
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
      }
    );
    respuestaraw.status === 200 &&
      swal.fire("Listo", "La acción se completo correctamente", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error"
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
          "error"
        );
  }

  async savePresentacionaditionalTelas(id, rollos, mml, acum) {
    const data = new FormData();
    data.append("id", id);
    data.append("rollos", rollos);
    data.append("mml", mml);
    data.append("acum", acum);
    const respuestaraw = await fetch(
      "php/presentacion.php?updatedatatblTelas",
      {
        method: "POST",
        body: data,
      }
    );
    respuestaraw.ok
      ? null
      : swal.fire(
          "ERROR",
          "Hay un problema al actualizar la información",
          "error"
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
      }
    );
    respuestaraw.status === 200 &&
      swal.fire("Listo", "La acción se completo correctamente", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error"
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
      }
    );
    respuestaraw.status === 200 &&
      swal.fire("Listo", "La acción se completo correctamente", "success");
    respuestaraw.status === 500 &&
      swal.fire(
        "ERROR",
        "Hay problema al guardar en la base de datos",
        "error"
      );
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
      }
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
      }
    );
    const respuesta = await respuestaraw.json();
    let sumareal = 0;
    let golpes = 0;
    let cajasxpanal = 0;
    let std = 0;
    respuesta.forEach((newelement) => {
      sumareal = sumareal + newelement.real;
      cajasxpanal = sumareal * newelement.panalxcaja;
      std = (sumareal * newelement.factor).toFixed(2);
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
        std
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
      }
    );
    const respuesta = await respuestaraw.json();
    let acumulado = 0;
    let sumarollos = 0;
    let mmlmop = 0;
    respuesta.forEach((newelement) => {
      acumulado = acumulado + newelement.mml;
      sumarollos = sumarollos + newelement.rollos;
      mmlmop = newelement.panalxcaja;
      body += `<tr data-id="${newelement.id}"><td>${newelement.hora}</td>
            <td contenteditable="true" class="editable" onblur="getCellValueTelas(this)">${newelement.rollos}</td>
            <td contenteditable="true" class="editable" onblur="getCellValueTelas(this)">${newelement.mml}</td>
            <td>${acumulado}</td></tr>`;
      presentacion = newelement.presentacion;
      this.savePresentacionaditionalTelas(
        newelement.id,
        newelement.rollos,
        newelement.mml,
        acumulado
      );
    });
    document.getElementById("presentacion" + notbl + "telas").value =
      presentacion;
    if (body != "") {
      document.getElementById("presentacion" + notbl + "telas").disabled = true;
      document.getElementById(
        "savePresentacion" + notbl + "telas"
      ).disabled = true;
      body += `<tr><td class="fw-bold">Total</td><td>${sumarollos}</td><td></td><td>${acumulado}</td></tr>`;
      let acumant = document.getElementById("mmltotal").value;
      let rollosant = document.getElementById("rollostotal").value;
      let mop = rollosant * mmlmop;
      let mermamml = mop / acumant - 1;
      acumant = +acumant + +acumulado;
      rollosant = +rollosant + +sumarollos;
      document.getElementById("mmltotal").value = acumant;
      document.getElementById("rollostotal").value = rollosant;
      document.getElementById("mmltotalMop").value = mop;
      document.getElementById("mermammltotal").value = mermamml.toFixed(2);
    } else {
      document.getElementById(
        "presentacion" + notbl + "telas"
      ).disabled = false;
      document.getElementById(
        "savePresentacion" + notbl + "telas"
      ).disabled = false;
    }
    document.getElementById(domtbl).innerHTML = body;
    this.permitirSoloNumeros(domtbl);
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
    console.log(rowId);
    const cells = row.querySelectorAll("td");
    const editableValue = cell.innerText;
    const table = cell.closest("table");

    this.savePresentacionaditional(
      rowId,
      editableValue,
      cells[2].innerText,
      cells[3].innerText,
      cells[4].innerText
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
    const editableValue = cell.innerText;
    const cellIndex = Array.from(row.children).indexOf(cell);
    let valor1 = cells[1].innerText;
    let valor2 = cells[2].innerText;
    if (cellIndex === 1) {
      valor1 = editableValue;
    } else if (cellIndex === 2) {
      valor2 = editableValue;
    }
    const table = cell.closest("table");
    this.savePresentacionaditionalTelas(
      rowId,
      valor1,
      valor2,
      cells[3].innerText
    ).then(() => {
      table.id == "tablapresentacion1telas" &&
        this.tblPresentacionSubtelas(folio, 1, "tblpresentacionsub1telas");
      table.id == "tablapresentacion2telas" &&
        this.tblPresentacionSubtelas(folio, 2, "tblpresentacionsub2telas");
      table.id == "tablapresentacion3telas" &&
        this.tblPresentacionSubtelas(folio, 3, "tblpresentacionsub3telas");
    });
  }

  async getCellValueGolpesDos(rows) {
    for (const row of rows) {
      const id = row.getAttribute("data-id");
      const golpes = row.cells[0]?.innerText.trim();
      const merma = row.cells[1]?.innerText.trim();

      await this.saveGolpes(id, golpes, merma);
    }
  }

  async getCellValueGolpes(cell) {
    console.log(cell);
    const row = cell.parentNode;
    console.log(row);
    const rowId = row.getAttribute("data-id");
    console.log(rowId);
    const cells = row.querySelectorAll("td");
    console.log(cells);
    let editableValue = cell.innerText;
    console.log(editableValue);
    this.sumartodo();
    await this.saveGolpes(rowId, editableValue, cells[1].innerText);
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
      rows4.length
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
}
