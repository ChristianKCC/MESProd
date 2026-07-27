document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("formEditVac");
    if (!form) return;

    // Referencias a los inputs de arriba
    const inputVacaciones = document.querySelector('input[name="dias_solicitados"]');
    const inputReposicionFestivo = document.querySelector('input[name="dias_reposicion"]');
    const inputDescanso = document.querySelector('input[name="dias_descanso"]');
    const inputTotal = document.querySelector('input[name="total_dias"]');

    // Mapeo de colores
    const colores = {
        V: "rgb(198,224,180)",
        D: "rgb(255,255,153)",
        F: "rgb(255,153,153)",
        R: "rgb(180,198,231)"
    };

    // Función para recalcular y pintar
    function recalcularContadores() {
        let vacaciones = 0;
        let reposicionFestivo = 0;
        let descanso = 0;

        document.querySelectorAll(".calendario select").forEach(sel => {
            const valor = sel.value;

            // Contadores
            if (valor === "V") vacaciones++;
            if (valor === "F" || valor === "R") reposicionFestivo++;
            if (valor === "D") descanso++;

            // Colorear dinámicamente la celda
            const td = sel.closest("td");
            if (valor && colores[valor]) {
                td.style.backgroundColor = colores[valor];
            } else {
                td.style.backgroundColor = "";
            }
        });

        // Actualizar inputs
        if (inputVacaciones) inputVacaciones.value = vacaciones;
        if (inputReposicionFestivo) inputReposicionFestivo.value = reposicionFestivo;
        if (inputDescanso) inputDescanso.value = descanso;
        if (inputTotal) inputTotal.value = vacaciones + reposicionFestivo + descanso;
    }

    // Enganchar evento change a todos los selects
    document.querySelectorAll(".calendario select").forEach(sel => {
        sel.addEventListener("change", recalcularContadores);
    });

    // Ejecutar una vez al cargar para inicializar
    recalcularContadores();

    // Submit del formulario
    form.addEventListener("submit", async function(e) {
        e.preventDefault();

        const accion = e.submitter ? e.submitter.value : "actualizar";
        const data = new FormData(form);

        if (accion === "actualizar") {
            try {
                const resp = await fetch("./php/index.php?actualizarSolicitudVacaciones", {
                    method: "POST",
                    body: data
                });

                const text = await resp.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (err) {
                    console.error("Respuesta no es JSON:");
                    Swal.fire("Error", "El servidor devolvió un error inesperado", "error");
                    return;
                }

                if (result.success) {
                    Swal.fire("Actualizado", "La solicitud se actualizó correctamente", "success")
                    .then(() => {
                        Swal.fire("Listo", "Abriendo PDF...", "success")
                        .then(() => {
                            const folio = data.get("folio");
                            window.open("./pdf/GenPDF.php?folio=" + btoa(folio), "_blank");
                        });
                    });
                } else {
                    Swal.fire("Error", "No se pudo actualizar en BD", "error");
                }
            } catch (err) {
                Swal.fire("Error", "Ocurrió un error al comunicarse con el servidor", "error");
            }
        }
    });
});

// Funcion para actualizar tabla desde editarInfo con ventanas padre e hija
function regresar() {    
    if (window.opener && !window.opener.closed) {        
        if (typeof window.opener.refrescarTablaVacaciones === "function") {
            window.opener.refrescarTablaVacaciones();
        }
    }    
    window.close();
}