import { Toolsjs } from "../../Tools/Tools.js";

const Tools = new Toolsjs();
Tools.llnarslc('CatalogoPersonal', 'GetSlcMaquinas', 'maquina', 0);
async function tblTrazabilidad() {
	const maquina = document.getElementById('maquina').value;
	const fechai = document.getElementById('fechai').value;
	const fechaf = document.getElementById('fechaf').value;
	const data = new FormData();
	data.append('maquina', maquina);
	data.append('fechai', fechai);
	data.append('fechaf', fechaf);
	const respuestaraw = await fetch('php/tbltrazabilidadcompleto.php', {
		method: 'POST',
		body: data
	});
	const respuesta = await respuestaraw.text();
	document.getElementById('tbltrazabilidadcompleto').innerHTML = respuesta;
}
document.getElementById('filtro').addEventListener('click', function (e) {
	e.preventDefault();
	tblTrazabilidad();
});
document.getElementById('exceltbltraz').addEventListener('click', function (e) {
	e.preventDefault();
	Tools.exportartablaexcel('tbltrazabilidadcompleto');

})
