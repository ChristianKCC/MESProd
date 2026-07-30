export class EPPMod {
  
  // Obtencion de EPP egun el tipo
  async getListEquipo(tipoEquipo) {
    const respuestaraw = await fetch('../EPP/php/epp.php?' + tipoEquipo);
    const respuesta = await respuestaraw.json();
    let list = '';
    respuesta.forEach(element => {
      list += `<tr>
            <td>${element.nombre}</td>
            <td> <input class="form-check-input" type="radio" name="${element.id}" id="bas${element.id}" value="1">
            <label class="form-check-label" for="bas${element.id}">
              Si
            </label></td>
            <td><input class="form-check-input" type="radio" name="${element.id}" id="basno${element.id}" value="2" checked>
            <label class="form-check-label" for="basno${element.id}">
              No
            </label></td>`;
      if (element.tipo === 1 || (element.tipo === 3 && element.id === 13)) {
        list += `<td><input class="form-check-input" type="radio" name="${element.id}" id="basno${element.id}" value="3">
                <label class="form-check-label" for="basno${element.id}">
                  N/A
                </label></td>`;
      }
      list += `</tr>`;
    });
    return list;
  }

  // Funcion de guardado de EPP 
  async saveEpp(noemp, checkbox, comentario, noempres = '') {
    const data = new FormData();
    data.append('noemp', noemp);
    data.append('noempres', noempres);
    data.append('comentario', comentario);
    data.append('checkbox', JSON.stringify(checkbox));
    const respuestaraw = await fetch('../EPP/php/epp.php?saveEPP', {
      method: 'POST',
      body: data
    });
    respuestaraw.ok ? swal.fire('Listo', 'Se guardo el registro de EPP para ' + noemp, 'success') :
      swal.fire('Error', 'No se puede guaradar el regitro ', 'error');
    document.getElementById('formepp').reset();
  }

  // Funcion de llenado de tabla principal de EPP
  async tblEPP(session = 1) {
    const respuestaraw = await fetch('../EPP/php/epp.php?tblEPPEnc&session=' + session);
    const respuesta = await respuestaraw.json();
    let list = '';
    respuesta.forEach(element => {
      list += `<tr><td>${element.id}</td><td>${element.noemp}</td><td>${element.nombre}</td><td>${element.departamento}</td><td>${element.comentario}</td>
        <td><a href='#' class='btn btn-sm btn-info' data-bs-toggle='modal' data-bs-target='#exampleModal' data-bs-whatever='${element.id}'><i class='fas fa-box-open'></i></a></td></tr>`;
    });
    return list;
  }

  // Funcion de llenado de datos para modal
  async tblEPPSubEnc(folio) {
    const respuestaraw = await fetch('../EPP/php/epp.php?tblEPPSubEnc&folio=' + folio);
    const respuesta = await respuestaraw.json();
    let list = '';
    respuesta.forEach(element => {
      list += `<tr><td>${element.noemp}</td><td>${element.nombre}</td><td>${element.departamento}</td>
        <td>${element.equipo}</td> <td>${element.valor}</td></tr>`;
    });
    return list;
  }

  // Funcion de limpiado de form
  limpiar() {
    document.getElementById('formepp').reset();
  }

  // Funcion de reporte de EPP
  async tblEPPReporte(fechai, fechaf, noemp, observador, departamento) {
    const data = new FormData();
    data.append('fechai', fechai);
    data.append('fechaf', fechaf);
    data.append('noemp', noemp);
    data.append('departamento', departamento);
    data.append('observador', observador);
    const respuestaraw = await fetch('../EPP/php/epp.php?tblEPPReporte', {
      method: 'POST',
      body: data
    });
    const respuesta = await respuestaraw.json();
    let list = '';
    respuesta.forEach(element => {
      list += `<tr><td>${element.id}</td><td>${element.noemp}</td><td>${element.nombre}</td><td>${element.departamento}</td><td>${element.comentario}</td>
        <td><a href='#' class='btn btn-sm btn-info' data-bs-toggle='modal' data-bs-target='#exampleModal' data-bs-whatever='${element.id}'><i class='fas fa-box-open'></i></a></td></tr>`;
    });
    return list;
  }

  // Trae el EPP desde el Excel según departamento y puesto
  async getEPPExcel(departamento, puesto) {
    const params = new URLSearchParams({ departamento, puesto });
    const respuestaraw = await fetch('../EPP/php/epp.php?getEPPExcel&' + params.toString());
    return await respuestaraw.json();
  }

  // Renderiza la lista del modal de solicitud
  renderEPPExcel(lista) {
    if (!Array.isArray(lista) || lista.length === 0) {
      return `<tr><td colspan="4" class="text-center text-muted">
                No se encontró EPP para este departamento y puesto.
              </td></tr>`;
    }
    let html = '';
    lista.forEach((element, i) => {
      html += `<tr>
        <td>${element.categoria}</td>
        <td>${element.equipo}</td>
        <td><input class="form-check-input" type="radio" name="excel_${i}" value="1"> Sí</td>
        <td><input class="form-check-input" type="radio" name="excel_${i}" value="2" checked> No</td>
        <td><input type="number" class="form-control form-control-sm cantidad-input" value="1" min="1" style="width:75px"></td>
      </tr>`;
    });
    return html;
  }

  // Trae las herramientas desde el Excel según departamento y puesto
  async getToolExcel(departamento, puesto) {
    const params = new URLSearchParams({ departamento, puesto });
    const respuestaraw = await fetch('../EPP/php/epp.php?getToolExcel&' + params.toString());
    return await respuestaraw.json();
  }

  // Renderiza la lista del modal de solicitud
  renderToolExcel(lista) {
    if (!Array.isArray(lista) || lista.length === 0) {
      return `<tr><td colspan="4" class="text-center text-muted">
                No se encontraron herramientas para este departamento y puesto.
              </td></tr>`;
    }
    let html = '';
    lista.forEach((element, i) => {
      html += `<tr>
        <td>${element.categoria}</td>
        <td>${element.equipo}</td>
        <td><input class="form-check-input" type="radio" name="excel_${i}" value="1"> Sí</td>
        <td><input class="form-check-input" type="radio" name="excel_${i}" value="2" checked> No</td>
        <td><input type="number" class="form-control form-control-sm cantidad -input" value="1" min="1" style="width:75px"></td>
      </tr>`;
    });
    return html;
  }

  // Envio del payload a vale.php por POST para siguiente apertura del PDF en pestaña nueva
  generarValePDF(payload) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../EPP/php/vale.php';
    form.target = '_blank';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'payload';
    input.value = JSON.stringify(payload);

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
  }

  // ------------------------------------------------------------------------------------------
  // Metodos de solicitud de EPP
  // ------------------------------------------------------------------------------------------
  async misSolicitudes() {
    const r = await fetch('../EPP/php/epp.php?misSolicitudes'); return await r.json();
  }
  async esAlmacen() {
    const r = await fetch('../EPP/php/epp.php?esAlmacen'); return await r.json();
  }
  async pendientesPorEmp(ibm) {
    const r = await fetch('../EPP/php/epp.php?pendientesPorEmp&ibm=' + encodeURIComponent(ibm));
    return await r.json();
  }

  async entregarVale(folio, clave) {
    const d = new FormData(); d.append('folio', folio); d.append('clave', clave);
    const r = await fetch('../EPP/php/epp.php?entregarVale', { method: 'POST', body: d });
    return await r.json();
  }

  async recibirVale(folio, clave) {
    const d = new FormData(); d.append('folio', folio); d.append('clave', clave);
    const r = await fetch('../EPP/php/epp.php?recibirVale', { method: 'POST', body: d });
    return await r.json();
  }

  async rechazarVale(folio) {
    const d = new FormData(); d.append('folio', folio);
    const r = await fetch('../EPP/php/epp.php?rechazarVale', { method: 'POST', body: d });
    return await r.json();
  }
  descargarVale(folio) { window.open('../EPP/php/vale.php?folio=' + folio, '_blank'); }

  renderMisSolicitudes(lista) {
    if (!Array.isArray(lista) || lista.length === 0)
      return `<tr><td colspan="5" class="text-center text-muted">Sin solicitudes.</td></tr>`;
    const estados = { 0: 'Pendiente', 1: 'Entregado', 2: 'Rechazado' };
    let html = '';
    lista.forEach(el => {
      html += `<tr>
        <td>${el.folio}</td>
        <td>${el.tipo === 'epp' ? 'EPP' : 'Herramientas'}</td>
        <td>${el.motivo || ''}</td>
        <td>${estados[el.estado] || '-'}</td>
        <td style="width:1%; white-space:nowrap;">
          <button type="button" class="btn btn-sm btn-danger btn-pdf-mis" data-folio="${el.folio}">
            <i class="fas fa-file-pdf"></i> Ver Solicitud PDF
          </button>
        </td>
      </tr>`;
    });
    return html;
  }

  renderEntregas(lista) {
    if (!Array.isArray(lista) || lista.length === 0)
      return `<tr><td colspan="5" class="text-center text-muted">Sin solicitudes pendientes.</td></tr>`;
    let html = '';
    lista.forEach(el => {
      html += `<tr>
        <td>${el.folio}</td>
        <td>${el.tipo === 'epp' ? 'EPP' : 'Herramientas'}</td>
        <td>${el.motivo || ''}</td>
        <td>${el.fecha || ''}</td>
        <td style="width:1%; white-space:nowrap;">
          <button type="button" class="btn btn-sm btn-primary btn-pdf" data-folio="${el.folio}">
              <i class="fas fa-file-pdf"></i> Ver Solicitud PDF
          </button>
          <button type="button" class="btn btn-sm btn-success btn-entregar" data-folio="${el.folio}">
              <i class="fas fa-check"></i> Entregar
          </button>
          <button type="button" class="btn btn-sm btn-danger btn-rechazar" data-folio="${el.folio}">
              <i class="fas fa-times"></i> Rechazar
          </button>
        </td>
      </tr>`;
    });
    return html;
  }
}