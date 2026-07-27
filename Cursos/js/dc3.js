import { Toolsjs } from "../../Tools/Tools.js";
const herramientas = new Toolsjs();

class Cursos {
    gnrardcr(idcapasitacion) {
        alert(idcapasitacion);
    }
    reportedc3() {
        herramientas.llnarslc('CatalogoCursos', 'slccursostipo', 'cursostipo', 0);
        document
            .getElementById("cursostipo")
            .addEventListener("change", function () {
                let cursostipo = document.getElementById("cursostipo").value;
                herramientas.llnarslc('CatalogoCursos','slccursosxtipo&tipo=' + cursostipo, 'cursos',0);
            });

        document
            .getElementById("consultar")
            .addEventListener("click", function (event) {
                event.preventDefault();
                let formrptdc3 = document.getElementById("formrptdc3");
                const formdata = new FormData(formrptdc3);
                formdata.append("data", formrptdc3);
                (async () => {
                    const respuestaraw = await fetch("./php/dc3.php?tblrptdc3", {
                        method: "POST",
                        body: formdata,
                    });
                    const respuesta = await respuestaraw.json();
                    let body = "";
                    respuesta.forEach((elemento) => {
                        body += `<tr><td>${elemento.idcurso}</td><td>${elemento.nombrecurso}</td>
            <td>${elemento.fechainicial}</td><td>${elemento.fechafinal}</td>
            <td><a class='btn btn-sm btn-danger' href='pdf/crearpdf.php?idcap=${elemento.idcurso}' target="_blank">DC3</a></td></tr>`;
                    });
                    document.getElementById("tblrptdc3").innerHTML = body;
                })();
            });
    }
}
const cursos = new Cursos();
cursos.reportedc3();