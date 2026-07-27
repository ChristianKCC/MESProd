document.addEventListener("DOMContentLoaded", () => {
    const inputNominas = document.getElementById("archivoNominas");
    const textoNominas = document.getElementById("texto-upload-nominas");
    const formNominas = document.getElementById("formNominas");

    inputNominas.addEventListener("change", () => {
        textoNominas.textContent = inputNominas.files.length > 0
            ? "Archivo seleccionado: " + inputNominas.files[0].name
            : "Haz click para seleccionar un archivo";
    });

    formNominas.addEventListener("submit", (e) => {
        if (inputNominas.files.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Archivo requerido',
                text: 'Debes seleccionar un archivo de BD Nóminas antes de subirlo.',
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

    // Tutorial con Driver.js
    const driver = window.driver.js.driver;
    const steps = [
        {
            element: ".tittlecont",
            popover: {
                title: "Actualización BD Nóminas",
                description: "Desde aquí podrás actualizar el archivo de nóminas en formato CSV.",
                side: "bottom"
            }
        },
        {
            element: ".alert.alert-info",
            popover: {
                title: "Instrucciones",
                description: "Recuerda que el archivo debe estar en formato '.csv'.",
                side: "bottom"
            }
        },
        {
            element: ".card.mb-4",
            popover: {
                title: "Estado del archivo",
                description: "Aquí verás el estado actual del archivo cargado, número de registros y fecha de actualización.",
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
            element: ".zonaArchivoNominas",
            popover: {
                title: "Archivo BD Nóminas",
                description: "Haz clic aquí para seleccionar el archivo de nóminas en formato CSV.",
                side: "top"
            }
        },
        {
            element: "form#formNominas button[type='submit']",
            popover: {
                title: "Subir archivo",
                description: "Presiona este botón para subir el archivo de nóminas.",
                side: "top"
            }
        },
        {
            element: "#btnAyuda",
            popover: {
                title: "Botón de ayuda",
                description: "Presiona este botón para repetir el tutorial.",
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

    const tutorialKey = "tutorial_actualizacionNominas";
    if (!localStorage.getItem(tutorialKey)) {
        driverObj.drive();
        localStorage.setItem(tutorialKey, "true");
    }

    const btnAyuda = document.getElementById("btnAyuda");
    if (btnAyuda) {
        btnAyuda.addEventListener("click", () => driverObj.drive());
    }
});
