(async()=>{
    const respuestaraw = await fetch("../IMC/php/imc.php?tblMisIMC");
      const respuesta = await respuestaraw.json();
      console.log(respuesta);
      let body = "";
      respuesta.forEach((element) => {
        body += `<tr><td>${element.imc}</td><td>${element.creado}</td><td>${element.emisor}</td><td>${element.departamento}</td><td>${element.area}</td>
        <td>${element.deteccion}</td><td>${element.riesgo}</td><td>${element.tipo}</td><td>${element.responsable}</td>
        <td>${element.fechacompromiso}</td><td>${element.estatus}</td><td>${element.descripcion}</td><td>${element.sugerencias}</td></tr>`;
      });
      document.getElementById("tblReporteIMC").innerHTML = body;
})();