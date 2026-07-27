import { ReporteCalidad } from "../module/calidad.js";
const CalidadObj = new ReporteCalidad();

CalidadObj.llnarslcMaquinas("maquina")

const maquinaEl = document.getElementById("maquina");
const inspeccionadosEl = document.getElementById("inspeccionados");
const sdEl = document.getElementById("sd");
const qlEl = document.getElementById("ql");
const sdobservacionesEl = document.getElementById("sdobservaciones");

const btnGuardarReporte = document.getElementById("btnGuardarReporte");

btnGuardarReporte.addEventListener("click", (e) => {
    e.preventDefault();

    const maquina = maquinaEl.value;
    const inspeccionados = inspeccionadosEl.value;
    const sd = sdEl.value;
    const ql = qlEl.value;
    const sdobservaciones = sdobservacionesEl.value;

    CalidadObj.guardarReporteCalidad(
        maquina,
        inspeccionados,
        sd,
        ql,
        sdobservaciones
    ).then((response) => {
        if (response.success) {
            Swal.fire("Éxito", "Reporte guardado exitosamente", "success");

            // ✅ Aquí SÍ funcionan porque siguen siendo elementos DOM
            maquinaEl.value = '';
            inspeccionadosEl.value = 0;
            sdEl.value = 0;
            qlEl.value = 0;
            sdobservacionesEl.value = "";
            mostrarReporteCalidad();
        } else {
            Swal.fire("Error", "Error al guardar el reporte", "error");
        }
    });
});

function mostrarReporteCalidad() {
    CalidadObj.obtenerReporteCalidad().then((data) => {
        const tbody = document.getElementById("tableBody");
        tbody.innerHTML = ""; // Limpiar el cuerpo de la tabla antes de llenarla
        data.forEach((registro) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${registro.Fecha}</td>
                <td>${registro.NombreMaquina}</td>
                <td>${registro.Inspeccionadas}</td>
                <td>${registro.SD}</td>
                <td>${registro.QL}</td>
                <td>${registro.Observaciones}</td>
            `;
            tbody.appendChild(row);
        });

    });
}

// Llamamos a la función para mostrar el reporte al cargar la página
mostrarReporteCalidad();