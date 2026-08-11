import { Toolsjs } from '../../Tools/Tools.js'
import { Rill } from '../modules/Bitacora.js';

const Tools = new Toolsjs();
const RillObj = new Rill();
Tools.llnarslc('CatalogosBitacora', 'GetFoliosValesRil', 'foliovalesril', 0);
Tools.llnarslc('CatalogosBitacora', 'GetClavesValesE', 'claveril', 0);
document.getElementById('claverilupdate').addEventListener('click', function (event) {
    event.preventDefault();
    Tools.llnarslc('CatalogosBitacora', 'GetFoliosValesRil', 'foliovalesril', 0);
})

document.getElementById('noempril').addEventListener('keyup', function (event) {
    Tools.getDataEmpleado(event.target.value, 'empleadoril', '', 'puestoempril');
})
document.getElementById('claveril').addEventListener('change', (event) => {
    event.preventDefault();
    const valorseleccionado = event.target.value;
    Tools.llnarslc('CatalogosBitacora', 'GetClasexClave&clave=' + valorseleccionado, 'claseril', 0);
});
document.getElementById('claseril').addEventListener('change', (event) => {
    event.preventDefault();
    const valorseleccionado = event.target.value;
    const clave = document.getElementById('claveril').value;
    Tools.llnarslc('CatalogosBitacora', 'GetMaterialesxclase&clase=' + valorseleccionado + '&clave='+ clave, 'materialril', 0);
});

document.getElementById('guardarril').addEventListener('click', function (event) {
    event.preventDefault();
    const claverill = document.getElementById('claveril').value;
    const claseril = document.getElementById('claseril').value;
    const materialril = document.getElementById('materialril').value;
    const materialpruebaril = document.getElementById('materialpruebaril').value;
    const noempril = document.getElementById('noempril').value;
    const loteril = document.getElementById('loteril').value;
    const foliovalesril = document.getElementById('foliovalesril').value;
    const foliovalemanual = document.getElementById('foliovalemanual').value;
    const horaril = document.getElementById('horaril').value;
    if (claverill == '' ||
        claseril == '' ||
        (materialril == '' && materialpruebaril == '') ||
        noempril == '' ||
        loteril == '' ||
        (foliovalesril == '' && foliovalemanual == '') ||
        horaril == '') {
        swal.fire('Ups!', 'Todos los campos son obligatorios', 'warning');
        return false;
    }
    RillObj.saveRill(claverill, claseril, materialril, noempril, loteril, foliovalesril, horaril, materialpruebaril, foliovalemanual).then(() => {
        document.getElementById('loteril').value = '';
        document.getElementById('claseril').value = '';
        document.getElementById('materialril').value = '';
        document.getElementById('materialpruebaril').value = '';
        document.getElementById('loteril').value = '';
        document.getElementById('foliovalesril').value = '';
        document.getElementById('foliovalemanual').value = '';
        document.getElementById('horaril').value = '';
        RillObj.tblRill().then(body => {
            document.getElementById('rilltbl').innerHTML = body;
        });
    });
})
RillObj.tblRill().then(body => {
    document.getElementById('rilltbl').innerHTML = body;
});

(async () => {
    const resultContainer = document.getElementById('qr-reader-results');
    function onScanSuccess(decodedText, decodedResult) {
        document.querySelector('#resultqr').innerText = decodedText;
        document.getElementById('resultqr').innerText = "Codigo detectado: " + decodedText;
        document.getElementById('loteril').value = decodedText;
        html5QrcodeScanner.clear();
    }
    function onScanFailure(error) {
        // si falla el escaneo
    }
    function onCameraError(error) {
        alert('Error al acceder a la cámara: ' + error.message);
    }
    let videoDevices = await Html5Qrcode.getCameras();
    if (videoDevices && videoDevices.length) {
        let rearCamera = videoDevices.find(device => device.label.toLowerCase().includes('back'));
        let cameraId = rearCamera ? rearCamera.id : videoDevices[0].id;
        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            {
                fps: 10,
                qrbox: 250,
                formatsToSupport: [
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.CODE_39,
                    Html5QrcodeSupportedFormats.QR_CODE
                ],
                cameraId: cameraId
            });
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    } else {
        alert('No se encontraron cámaras.');
    }
})();