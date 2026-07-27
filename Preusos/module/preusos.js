export class Preusos {
  async reportePreusos(fechai, fechaf,departamento,maquina) {
    const data = new FormData();
    data.append("fechai", fechai);
    data.append("fechaf", fechaf);
    data.append("departamento", departamento);
    data.append("maquina", maquina);
    const datapromise = await fetch("php/preusos.php?reportePreusos", {
      method: "POST",
      body: data,
    });
    const dataresprom = await datapromise.json();
    return dataresprom;
  }
  async tblPreusos(data, dom) {
    data = await data;
    const grupos = this.agruparPorId(data);
    let body = "";

    Object.keys(grupos).forEach((id) => {
      const primerElemento = grupos[id][0];
      body += `
      <tr>
        <td>${primerElemento.id}</td>
        <td>${primerElemento.tipoinspeccion}</td>
        <td>${primerElemento.seccionpre}</td>
        <td>${primerElemento.noemp}</td>
        <td>${primerElemento.Nombre}</td>
        <td>${primerElemento.Turno}</td>
        <td>${primerElemento.NombreMaquina}</td>
        <td>${primerElemento.comentarios}</td>
        <td>${primerElemento.fechasave}</td>
        <td><button class='btn btn-sm btn-info' onclick="verDetalles(${id})">Ver detalles</button></td>
      </tr>
    `;
    });

    dom.innerHTML = body;
  }
  agruparPorId(data) {
    const grupos = {};
    data.forEach((element) => {
      if (!grupos[element.id]) {
        grupos[element.id] = [];
      }
      grupos[element.id].push(element);
    });
    return grupos;
  }

  verDetalles(id) {
  const detalles = window.datosAgrupados[id];
  if (!detalles) return;

  const filas = document.querySelectorAll("table tr");
  let filaOriginal = null;

  filas.forEach(fila => {
    if (fila.children[0] && fila.children[0].textContent == id) {
      filaOriginal = fila;
    }
  });

  if (!filaOriginal) return;

  const siguienteFila = filaOriginal.nextElementSibling;
  if (siguienteFila && siguienteFila.classList.contains("fila-detalles")) {
    siguienteFila.remove(); 
    return;
  }

  const contenido = `
    <table style="width:50%; border-collapse: collapse;" class='table table-bordered'>
      <thead class='table-dark'>
        <tr>
          <th>Descripcion</th>
          <th>Respuesta</th>
        </tr>
      </thead>
      <tbody>
        ${detalles.map(d => `
          <tr>
            <td>${d.descInpeccion}</td>
            <td>${d.itemcheck == 0 ? 'NA' : d.itemcheck==1 ? 'SI' : 'NO'}</td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  `;

  const filaDetalles = document.createElement("tr");
  filaDetalles.classList.add("fila-detalles");
  filaDetalles.innerHTML = `
    <td colspan="11">
      <strong>Detalles para ID ${id}:</strong>
      ${contenido}
    </td>
  `;

  filaOriginal.parentNode.insertBefore(filaDetalles, filaOriginal.nextSibling);
}

}
