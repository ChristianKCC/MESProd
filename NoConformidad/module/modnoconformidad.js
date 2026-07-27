export class NoConfromidad{
    async tblReporteNoConformidad(fechai,fechaf,departamento,maquina){
        const data = new FormData();
        data.append('fechai',fechai);
        data.append('fechaf',fechaf);
        data.append('departamento',departamento);
        data.append('maquina',maquina);
        const respuestaraw = await fetch("../NoConformidad/php/noconformidad.php?tblReporteNoConformidad",{
          method: 'POST',
          body: data
        });
        const respuesta = await respuestaraw.json();
        let body = "";
        respuesta.forEach((element) => {
          body += `<tr><td>${element.id}</td><td>${element.fecha}</td><td>${element.departamento}</td><td>${element.maquina}</td>
          <td>${element.sellador}</td><td>${element.operador}</td><td>${element.turno}</td><td>${element.producto}</td>
          <td>${element.hora}</td><td>${element.defecto}</td><td>${element.totalprod}</td><td>${element.prodrecuperado}</td>
          <td>${element.lider}</td><td>NULL</td><td>${element.codempdefecto}</td><td>${element.codterdefecto}</td><td>${element.descripcion}</td>
          <td>${element.accionescorrectivas}</td><td>${element.Componente}</td>
          <td><a href='php/crearpdf.php?id=${element.id}' target='_blank' class='btn btn-sm btn-danger'><i class="fas fa-file-pdf"></i></a></td>
          <td><a href='#' data-bs-toggle="modal" data-bs-target="#modalrepnocof" data-bs-whatever="${element.id}" class='btn btn-sm btn-warning'><i class="fas fa-edit"></i></a></td></tr>`;
        });
        document.getElementById("tblReporteIMC").innerHTML = body;
    }
    async saveUpdateNoConf(folio,departamento,defecto,calidad){
      const data = new FormData();
      data.append('folio',folio);
      data.append('departamento',departamento);
      data.append('defecto',defecto);
      data.append('calidad',calidad);
      const respuestaraw = await fetch("../NoConformidad/php/noconformidad.php?saveUpdateNoConf",{
        method: 'POST',
        body: data
      });
      respuestaraw.status === 200 && swal.fire('LISTO!!!','Información actualizada con exito','success');
      respuestaraw.status === 500 && swal.fire('ERROR!!!','Hay un problema en la base de datos','error');
    }
    async dataNoconf(id){
      const data = new FormData();
      data.append("id", id);
      const respuestaraw = await fetch(
        "../bitacora/php/bitacora.php?getAllDataConf",
        {
          method: "POST",
          body: data,
        }
      );
      const respuesta = await respuestaraw.json();
      return respuesta;
    }
}
