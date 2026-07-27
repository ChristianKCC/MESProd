// Validacion ligera: exigir al menos un filtro antes de enviar
document.getElementById('formFiltros').addEventListener('submit', function (e) {
    const ibm   = document.getElementById('ibm').value.trim();
    const tipo  = document.getElementById('tipoEmpleado').value;
    const depto = document.getElementById('departamento').value;
    const estado = document.getElementById('tipoAprobado').value;
    const fi    = this.fechaInicio.value;
    const ff    = this.fechaFin.value;

    if (!ibm && !tipo && !depto && !fi && !ff && !estado) {
        e.preventDefault();
        alert('Especifica al menos un filtro para generar el reporte.');
        return;
    }
    if ((fi && !ff) || (!fi && ff)) {
        e.preventDefault();
        alert('Si usas rango de fechas, completa tanto la fecha de inicio como la de fin.');
    }
});