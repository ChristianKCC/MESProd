const btnVerInformacion = document.getElementById("formVac");
if (btnVerInformacion) {
document.getElementById("formVac").addEventListener("submit", async function(e){
    e.preventDefault();

    // Obtener la accion si fue PDF o ENVIAR
    const accion = e.submitter.value;
    const data = new FormData(e.target);

    if(accion === "pdf"){
        const resp = await fetch("php/index.php?guardarSolicitudVacaciones", {
            method: "POST",
            body: data 
        });
        
        // Si la respuesta fue exitosa pasar al PDF
        // const result = await resp.json();
        // if(result.success){
        //     if(result.success){
        //         Swal.fire("Guardado", "La solicitud se registró correctamente, ¡En 10 segundos se te redigira a la consulta inicial!", "success")
        //         .then(() => {
        //             e.target.submit();
        //         });
        //     } else {
        //         Swal.fire("Error", "No se pudo guardar en BD", "error");
        //     }
        // } 
        // // Caso de que la solicitud no se almacenara
        // else {
        //     Swal.fire("Error", "No se pudo guardar en BD", "error");
        // }

        const result = await resp.json();
        if (result.error) {
            Swal.fire({
                icon: 'error',
                title: 'No se pudo registrar la solicitud',
                text: typeof result.error === 'string' 
                    ? result.error 
                    : JSON.stringify(result.error),
                confirmButtonText: 'Entendido'
            });            
            return;
        }

        if (result.success) {
            Swal.fire("Guardado", "La solicitud se registró correctamente, ¡En 10 segundos se te redigirá a la consulta inicial!", "success")
            .then(() => {
                e.target.submit();
            });
        } else {
            Swal.fire("Error", "No se pudo guardar en BD", "error");
        }

    }
     else if(accion === "enviar"){
        
        for (let [key, value] of data.entries()) {
            //console.log(key, value);
        }

        const resp = await fetch("php/index.php?enviarSolicitudVacaciones", {
            method: "POST",
            body: data   
        });
        
        const result = await resp.json();
        
        if(result.success){
            Swal.fire("Enviado", "La solicitud fue enviada correctamente", "success");
        } else {
            Swal.fire("Error", "No se pudo enviar la solicitud", "error");
        }
    }
});
}


// Funcion de carga de datos en hoja de datos
function cargarDeptoPuesto(ibm) {
    if (ibm !== '') {
        fetch('php/index.php?getDeptoPuesto', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'ibm=' + encodeURIComponent(ibm)
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                //console.warn(data.error);
            } else {
                document.getElementById('puesto').value = data.puesto;
                document.getElementById('departamento').value = data.depto;
            }
        })
        .catch(err => console.error(err));
    }
}

// Carga de funcion al cargar el DOM cuando se recuperan el ibm
document.addEventListener('DOMContentLoaded', function() {
    const tarjetaInput = document.getElementById('tarjeta');
    const ibmInicial = tarjetaInput.value.trim();
    cargarDeptoPuesto(ibmInicial);

    tarjetaInput.addEventListener('change', function() {
        cargarDeptoPuesto(this.value.trim());
    });
    
    // -------------------------------------------------------------------------------------------
    const driver = window.driver.js.driver;

    const steps = [
        {
            element: ".tittlecont",
            popover: {
                title: "Revisión de datos",
                description: "Aquí comienza el proceso de revisión de tu solicitud de vacaciones.",
                side: "bottom"
            }
        },
        {
            element: ".alert.alert-info",
            popover: {
                title: "Instrucciones",
                description: "Verifica tu información y al final guarda el PDF para enviarlo a autorización.",
                side: "bottom"
            }
        },
        {
            element: ".campos",
            popover: {
                title: "Campos principales",
                description: "Completa o revisa tus datos: nombre, puesto, fechas, días solicitados y antigüedad.",
                side: "right"
            }
        },
        {
            element: ".glosarioColores",
            popover: {
                title: "Glosario de colores",
                description: "Aquí encontraras una explicacion de lo que significa cada color segun el tipo que escojas en la tabla de abajo.",
                side: "top"
            }
        },
        {
            element: ".calendario",
            popover: {
                title: "Calendario",
                description: "Selecciona los días de vacaciones, descanso, festivo o reposición según corresponda.",
                side: "top"
            }
        },
        {
            element: ".diaSeleccionable",
            popover: {
                title: "Selección de dia",
                description: "Presiona aqui para desplegar las opciones y cambies si es necesario el tipo ya sea V | D | F | R (Usa el glosario de colores para saber a que refiere cada opción).",
                side: "top"
            }
        },        
        {
            element: ".observacionesSeccion",
            popover: {
                title: "Observaciones",
                description: "Agrega comentarios adicionales sobre tu solicitud (máx. 150 caracteres).",
                side: "top"
            }
        },
        {
            element: ".fechasReposicionfestivo",
            popover: {
                title: "Reposición/Festivo",
                description: "Anota las fechas de días por reposición o festivo en formato d/m/y.",
                side: "top"
            }
        },
        {
            element: ".saldo-row",
            popover: {
                title: "Saldo",
                description: "Aquí se muestran el saldo al periodo y los días hábiles calculados.",
                side: "top"
            }
        },        
        {
            element: ".botonRegresar",
            popover: {
                title: "Regresar a la selección",
                description: "Usa este botón para regresar a la pantalla anterior.",
                side: "top"
            }
        },
        {
            element: ".botonGuardar",
            popover: {
                title: "Guardar informacion",
                description: "Usa este botón para guardar y generar el PDF (Esto enviara tu solicitud a tu jefe inmediato).",
                side: "top"
            }
        },
        {
            element: "#btnAyuda",
            popover: {
                title: "Volver a ver el tutorial",
                description: "Si necesitas repasar cómo llenar esta pantalla, presiona este botón para repetir el tutorial.",
                side: "bottom"
            }
        }
    ];

    const driverObj = driver({
        showProgress: true,
        allowClose: false,
        disableInteraction: true,
        progressText: "Paso {{current}} de {{total}}",
        doneBtnText: "Finalizar",
        nextBtnText: "Siguiente",
        prevBtnText: "Atrás",
        steps
    });

    // Clave única para este tutorial
    const tutorialKey = "tutorial_finalizar";
    const tutorialYaVisto = localStorage.getItem(tutorialKey);

    if (!tutorialYaVisto) {
        driverObj.drive();
        localStorage.setItem(tutorialKey, "true");
    }

    // Botón de ayuda para relanzar el tutorial
    const btnAyuda = document.getElementById("btnAyuda");
    if (btnAyuda) {
        btnAyuda.addEventListener("click", () => {
            driverObj.drive();
        });
    }
});

document.querySelector('.botonGuardar').addEventListener('click', function() {
    // abrir el PDF en nueva pestaña (lo hace el form con target="_blank")
    // y redirigir la ventana actual
    setTimeout(() => {
        window.location.href = "./Consulta.php";
    }, 10000); // medio segundo de espera
});

// Carga de datos al iniciar el DOM
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a los inputs de arriba
    const inputVacaciones = document.querySelector('input[name="dias_solicitados"]');
    const inputReposicionFestivo = document.querySelector('input[name="dias_reposicion"]');
    const inputDescanso = document.querySelector('input[name="dias_descanso"]');
    const inputTotal = document.querySelector('input[name="total_dias"]');
    const solicitud_por = document.querySelector('input[name="solicitud_por"]');

    const colores = {
        V: "rgb(198,224,180)",
        D: "rgb(255,255,153)",
        F: "rgb(255,153,153)",
        R: "rgb(180,198,231)" 
    };

    // Función para recalcular
    // function recalcularContadores() {
    //     let vacaciones = 0;
    //     let reposicionFestivo = 0;
    //     let descanso = 0;

    //     // Recorrer todos los selects de la tabla
    //     document.querySelectorAll('.calendario select').forEach(sel => {
    //         const valor = sel.value;
    //         if (valor === 'V') {
    //             vacaciones++;
    //         }
    //         if (valor === 'F' || valor === 'R') {
    //             reposicionFestivo++;
    //         }
    //         if (valor === 'D') {
    //             descanso++;
    //         }
    //     });

    //     // Actualizar los inputs
    //     inputVacaciones.value = vacaciones;
    //     inputReposicionFestivo.value = reposicionFestivo;
    //     inputDescanso.value = descanso;

    //     // Calcular total
    //     inputTotal.value = vacaciones + reposicionFestivo + descanso;
    // }
    function recalcularContadores() {
        let vacaciones = 0;
        let reposicionFestivo = 0;
        let descanso = 0;

        document.querySelectorAll('.calendario select').forEach(sel => {
            const valor = sel.value;

            // Contadores
            if (valor === 'V') vacaciones++;
            if (valor === 'F' || valor === 'R') reposicionFestivo++;
            if (valor === 'D') descanso++;

            // Colorear dinámicamente la celda
            const td = sel.closest('td');
            if (valor && colores[valor]) {
                td.style.backgroundColor = colores[valor];
            } else {
                td.style.backgroundColor = ""; // blanco si no hay valor
            }
        });

        // Actualizar inputs
        inputVacaciones.value = vacaciones;
        inputReposicionFestivo.value = reposicionFestivo;
        inputDescanso.value = descanso;
        inputTotal.value = vacaciones + reposicionFestivo + descanso;
        // solicitud_por.value = vacaciones + reposicionFestivo + descanso;
    }


    // Enganchar evento change a todos los selects
    document.querySelectorAll('.calendario select').forEach(sel => {
        sel.addEventListener('change', recalcularContadores);
    });

    // Ejecutar una vez al cargar para inicializar
    recalcularContadores();
});
