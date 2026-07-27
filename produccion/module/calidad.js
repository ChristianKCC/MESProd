export class ReporteCalidad {
    async llnarslcMaquinas(dom, tipo = 0){
        const response = await fetch("../produccion/php/reporteCalidad.php?getDataMaquinas",); 
        const data = await response.json();
        let body = "";
        tipo == 0
        ? (body = `<option value = ''>Seleciona una opción</option>`)
        : (body = "");
        data.forEach((elemento) => {
            body += `<option value = "${elemento.NoMaquina}">  ${elemento.NombreMaquina}  </option>`;
        });
        document.getElementById(dom).innerHTML = body;
    }

    async guardarReporteCalidad(maquina, inspeccionados, sd, ql, sdobservaciones){
        const data = new FormData();
        data.append("maquina", maquina);
        data.append("inspeccionados", inspeccionados);
        data.append("sd", sd);
        data.append("ql", ql);
        data.append("sdobservaciones", sdobservaciones);

        const response = await fetch("../produccion/php/reporteCalidad.php?guardarReporteCalidad", {
            method: "POST",
            body: data,
        });
        return await response.json();

    }

    async obtenerReporteCalidad(){
        const response = await fetch("../produccion/php/reporteCalidad.php?obtenerReporteCalidad",);
        return await response.json();
    }
}