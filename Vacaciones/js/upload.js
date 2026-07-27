document.addEventListener("DOMContentLoaded", () => {
    // Vacaciones empleados
    const inputVac = document.getElementById("archivo");
    const textoVac = document.getElementById("texto-upload");
    const formVac = document.getElementById("form");

    inputVac.addEventListener("change", () => {
        textoVac.textContent = inputVac.files.length > 0
            ? "Archivo seleccionado: " + inputVac.files[0].name
            : "Haz click para seleccionar un archivo";
    });

    formVac.addEventListener("submit", (e) => {
        if (inputVac.files.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Archivo requerido',
                text: 'Debes seleccionar un archivo de vacaciones antes de subirlo.',
                confirmButtonText: 'Entendido'
            });
        }
    });

    // Relación de sindicalizados con supervisores
    const inputSind = document.getElementById("archivoSind");
    const textoSind = document.getElementById("texto-upload-sind");
    const formSind = document.getElementById("formSind");

    inputSind.addEventListener("change", () => {
        textoSind.textContent = inputSind.files.length > 0
            ? "Archivo seleccionado: " + inputSind.files[0].name
            : "Haz click para seleccionar un archivo";
    });

    formSind.addEventListener("submit", (e) => {
        if (inputSind.files.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Archivo requerido',
                text: 'Debes seleccionar un archivo de sindicalizados antes de subirlo.',
                confirmButtonText: 'Entendido'
            });
        }
    });

    // Validacion de modificacion de archivo si esta abierto
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get("fileopen") === "1") {
        Swal.fire({
            icon: 'error',
            title: 'Archivo en uso',
            text: 'El archivo está abierto en otra aplicación, buscalo y ciérralo antes de subir uno nuevo.',
            confirmButtonText: 'Entendido'
        });
    }

    // -------------------------------------------------------------------------------------------------------------------------
    const driver = window.driver.js.driver;

    const steps = [
        {
            element: ".tittlecont",
            popover: {
                title: "Actualización de archivo",
                description: "Desde aquí podrás actualizar los archivos de vacaciones en formato CSV.",
                side: "bottom"
            }
        },
        {
            element: ".alert.alert-info",
            popover: {
                title: "Instrucciones",
                description: "Recuerda que los archivos deben estar en formato '.csv'.",
                side: "bottom"
            }
        },
        {
            element: "h4.fw-bold",
            popover: {
                title: "Módulo de vacaciones",
                description: "Aquí se muestra la última actualización de los archivos de vacaciones y relacionales.",
                side: "bottom"
            }
        },
        {
            element: ".card.mb-4",
            popover: {
                title: "Estado del archivo",
                description: "En esta tarjeta verás el estado actual de los archivos cargados, número de registros y fecha de actualización.",
                side: "top"
            }
        },
        {
            element: ".alert.alert-warning",
            popover: {
                title: "Nota importante",
                description: "El sistema no acepta archivos '.xlsx'. Debes convertirlos a '.csv' antes de subirlos.",
                side: "top",
                popoverClass: "popover-importante"
            }
        },
        {
            element: ".zonaArchivoUno",
            popover: { 
                title: "Archivo de vacaciones", 
                description: "Haz clic aquí para seleccionar el archivo de vacaciones de empleados en formato CSV.", 
                side: "top" 
            }
        },        
        {
            element: "form#form button[type='submit']",
            popover: {
                title: "Subir archivo",
                description: "Presiona este botón para subir el archivo de vacaciones.",
                side: "top"
            }
        },
        {
            element: ".zonaArchivoDos",
            popover: {
                title: "Archivo de sindicalizados",
                description: "Haz clic aquí para seleccionar el archivo de relación de sindicalizados con supervisores.",
                side: "top"
            }
        },    
        {
            element: "form#formSind button[type='submit']",
            popover: {
                title: "Subir archivo sindicalizados",
                description: "Presiona este botón para subir el archivo de sindicalizados.",
                side: "top"
            }
        },
        {
            element: "#btnAyuda",
            popover: {
                title: "Volver a ver el tutorial",
                description: "Si necesitas repasar cómo usar esta pantalla, presiona este botón para repetir el tutorial.",
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
    const tutorialKey = "tutorial_actualizacionVacaciones";
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
