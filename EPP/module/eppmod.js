export class EPPMod {
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
    respuestaraw.ok ? swal.fire('Listo!!!', 'Se guardó el registro de EPP para ' + noemp, 'success') :
      swal.fire('Error!!!', 'No se puede guardar el registro ', 'error');
    document.getElementById('formepp').reset();
  }
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
  limpiar() {
    document.getElementById('formepp').reset();
  }
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

}

