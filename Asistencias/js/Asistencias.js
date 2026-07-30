import { Toolsjs } from "../../Tools/Tools.js";
import { Asistencias } from "../modules/PersonalEmp.js";
const tools = new Toolsjs();
tools.llnarslc("CatalogoPersonal", "GetSlcDepsall", "departamento", 0);
tools.llnarslc("CatalogoPersonal", "GetSlcCentroCostos", "ctrocstos", 0);
const asistencias = new Asistencias();

// Consulta de empleados y carga de loader
document.getElementById("consultar").addEventListener("click", (e) => {
    e.preventDefault();

    document.getElementById("loader").style.display = "flex";
    document.getElementById("content").style.display = "none";

    asistencias.buscarAsistencias().then(() => {
        document.getElementById("loader").style.display = "none";
        document.getElementById("content").style.display = "block";
    }).catch(() => {
        document.getElementById("loader").style.display = "none";
    });
});

document.getElementById("reiniciar").addEventListener("click", (e) => {
    document.getElementById('consultaacceso').innerHTML = '';
});

// Funcion para carga de datos segun el numero de empleado
document.getElementById('empno').addEventListener('change',
    async function () {
        const empno = this.value;

        try {
            const resp = await fetch('src/Repositorios/getEmpleadoInfo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'empno=' + encodeURIComponent(empno)
            });
            const data = await resp.json();

            if (data.success) {
                // Solo asigna si existe valor, si no deja el select como está
                if (data.IdCentroCosto !== undefined && data.IdCentroCosto !== null && data.IdCentroCosto !== '') {
                    document.getElementById('ctrocstos').value = data.IdCentroCosto;
                }
                if (data.EmpleadoSindicalizado !== undefined && data.EmpleadoSindicalizado !== null && data.EmpleadoSindicalizado !== '') {
                    document.getElementById('tipemp').value = data.EmpleadoSindicalizado;
                }
                if (data.NombreDepartamento !== undefined && data.NombreDepartamento !== null && data.NombreDepartamento !== '') {
                    document.getElementById('departamento').value = data.NombreDepartamento;
                }
            } else {
                document.getElementById('ctrocstos').value = "";
                document.getElementById('tipemp').value = "";
                document.getElementById('departamento').value = "";
            }
        } catch (err) {
            console.error('Error consultando empleado');
        }
    }
);

/*
// Funcion para carga de datos segun el numero de empleado
// Version 2 con funcion separada
document.getElementById('empno').addEventListener('change', (e) =>{
    asistencias.cargarDatosbyNoEmp();
});
*/