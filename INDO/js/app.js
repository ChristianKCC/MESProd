// ===== Agregar =====
document.getElementById('btnAgregar')?.addEventListener('click', async () => {
  const form = document.getElementById('formAgregar');
  const tarea = form.querySelector('[name="campos[D]"]');
  if (tarea && tarea.value.trim() === '') {
    Swal.fire('Falta información', 'La "Tarea del puesto" es obligatoria.', 'warning');
    tarea.focus(); return;
  }
  const conf = await Swal.fire({
    title: '¿Agregar registro?', text: 'Se añadirá al final del Excel.',
    icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, agregar', cancelButtonText: 'Cancelar'
  });
  if (!conf.isConfirmed) return;

  Swal.fire({ title: 'Guardando...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });
  try {
    const resp = await fetch('../INDO/php/agregar.php', { method: 'POST', body: new FormData(form) });
    const data = await resp.json();
    if (data.ok) {
      await Swal.fire('Archivo actualizado', `Registro #${data.num} agregado.`, 'success');
      form.reset();
    } else {
      Swal.fire('Error', data.msg || 'No se pudo agregar.', 'error');
    }
  } catch (e) {
    Swal.fire('Error de red', String(e), 'error');
  }
});

// ===== Modal eliminar =====
const modalEl = document.getElementById('modalEliminar');
let registros = [];

modalEl?.addEventListener('shown.bs.modal', cargarRegistros);
document.getElementById('buscarNum')?.addEventListener('input', pintarRegistros);

async function cargarRegistros() {
  const tbody = document.getElementById('tbodyRegistros');
  tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Cargando...</td></tr>';
  try {
    const r = await fetch('../INDO/php/buscar.php?listar=1');
    registros = await r.json();
    pintarRegistros();
  } catch (e) {
    tbody.innerHTML = `<tr><td colspan="5" class="text-danger">Error: ${e}</td></tr>`;
  }
}

// ========= Recuperacion de datos del excel en el modal de eliminar ==============
function pintarRegistros() {
  const q = (document.getElementById('buscarNum').value || '').trim().toLowerCase();
  const tbody = document.getElementById('tbodyRegistros');
  const fil = registros.filter(x => q === '' || String(x.num).includes(q) || (x.folio || '').toLowerCase().includes(q));
  if (!fil.length) { tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Sin resultados</td></tr>'; return; }
  tbody.innerHTML = fil.map(x => `
    <tr>
      <td class="fw-bold">${x.num}</td>
      <td>${esc(x.folio)}</td>
      <td class="small">${esc(x.tarea)}</td>
      <td><span class="badge bg-primary">${esc(x.tipo)}</span></td>      
      <td class="text-center"><button class="btn btn-sm btn-outline-danger" onclick='eliminar(${x.num}, ${JSON.stringify(x.folio)}, ${JSON.stringify(x.tarea)})'><i class="fa-solid fa-delete-left"></i> </button></td>
    </tr>`).join('');
}

// =============== Eliminacion de datos ===============================
window.eliminar = async function(num, folio, tarea) {
  const conf = await Swal.fire({
    title: `¿Eliminar el registro #${num}?`,
    html: `<div class="text-start small"><b>Folio:</b> ${esc(folio)}<br><b>Tarea:</b> ${esc(tarea)}</div>`,
    icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545',
    confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
  });
  if (!conf.isConfirmed) return;

  Swal.fire({ title: 'Eliminando...', didOpen: () => Swal.showLoading(), allowOutsideClick: false });
  try {
    const fd = new FormData(); fd.append('num', num);
    const r = await fetch('../INDO/php/eliminar.php', { method: 'POST', body: fd });
    const data = await r.json();
    if (data.ok) {
      await Swal.fire('Archivo actualizado', `Registro #${num} eliminado.`, 'success');
      cargarRegistros();
    } else {
      Swal.fire('Error', data.msg || 'No se pudo eliminar.', 'error');
    }
  } catch (e) { Swal.fire('Error de red', String(e), 'error'); }
};


function esc(s) { return (s == null ? '' : String(s)).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c])); }