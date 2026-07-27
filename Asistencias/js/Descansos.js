import { Toolsjs } from "../../Tools/Tools.js";
import { Descansos } from "../modules/PersonalEmp.js";
const Tools = new Toolsjs();
const descansosObj = new Descansos();

document.getElementById('savedescansos').addEventListener('click', function () {
    const fechadescansos = document.getElementById('fechadescansos').value;
    const fileInput = document.getElementById("archivo");
    descansosObj.uploadfile(fileInput, fechadescansos);
})

document.getElementById('buscar').addEventListener('click', function (e) {
    e.preventDefault();
    const fechai = document.getElementById('fechai').value;
    const fechaf = document.getElementById('fechaf').value;
    const noemp = document.getElementById('noemp').value;
    descansosObj.tblDescansos(fechai, fechaf,noemp);
})
window.deleteDesc = function (param) {
    descansosObj.deleteDesc(param).then(() => {
        const fechai = document.getElementById('fechai').value;
        const fechaf = document.getElementById('fechaf').value;
        const noemp = document.getElementById('noemp').value;
        descansosObj.tblDescansos(fechai, fechaf,noemp);
    });
}